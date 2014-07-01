<?php 
/* @description		Dice - A minimal Dependency Injection Container for PHP				*  
 * @author			Tom Butler tom@r.je													*
 * @copyright		2012-2014 Tom Butler <tom@r.je> | http://r.je/dice.html				*
 * @license			http://www.opensource.org/licenses/bsd-license.php  BSD License 	*
 * @version			1.3.1																*/
namespace Dice;
class Dice {
	private $rules = [];
	private $cache = [];
	private $instances = [];
	
	public function addRule($name, Rule $rule) {
		$this->rules[strtolower(trim($name, '\\'))] = $rule;
	}

	public function getRule($name) {
		if (isset($this->rules[strtolower(trim($name, '\\'))])) return $this->rules[strtolower(trim($name, '\\'))];
		foreach ($this->rules as $key => $rule) {
			if ($rule->instanceOf === null && $key !== '*' && is_subclass_of($name, $key) && $rule->inherit === true) return $rule;
		}
		return isset($this->rules['*']) ? $this->rules['*'] : new Rule;
	}
	
	public function create($component, array $args = [], $forceNewInstance = false) {
		if (!$forceNewInstance && isset($this->instances[strtolower($component)])) return $this->instances[strtolower($component)];
		if (!isset($this->cache[$component])) {			
			$rule = $this->getRule($component);
			$class = new \ReflectionClass((!empty($rule->instanceOf)) ? $rule->instanceOf : $component);
			$constructor = $class->getConstructor();
			$internal = $constructor && end(($class->getMethods()))->isInternal();
			$params = $constructor ? $this->getParams($constructor, $rule) : null;
			
			$this->cache[$component] = function($args, $forceNewInstance) use ($component, $rule, $class, $constructor, $internal, $params) {
				$object = ($constructor && $internal) ? $class->newInstanceArgs($params($args)) : $class->newInstanceWithoutConstructor();					
				if ($rule->shared === true) $this->instances[strtolower($component)] = $object;
				if ($constructor && !$internal) $constructor->invokeArgs($object, $params($args));
				foreach ($rule->call as $call) 	$class->getMethod($call[0])->invokeArgs($object, call_user_func($this->getParams($class->getMethod($call[0]), new Rule), $call[1]));
				return $object;
			};
		}
		return $this->cache[$component]($args, $forceNewInstance);
	}
	
	private function expand($param, array $share = []) {
		if (is_array($param)) return array_map(function($p) use($share) { return $this->expand($p, $share); }, $param);
		if ($param instanceof Instance) return $this->create($param->name, $share);
		else if (is_callable($param)) return $param($this);
		return $param;
	}
		
	private function getParams(\ReflectionMethod $method, Rule $rule) {	
		$subs = !empty($rule->substitutions) ? array_change_key_case($rule->substitutions) : [];
		$paramClasses = array_map(function($p) { return $p->getClass() ? strtolower($p->getClass()->name) : null;}, $method->getParameters());

		return function($args) use ($paramClasses, $rule, $subs) {			
			$share = !empty($rule->shareInstances) ? array_map([$this, 'create'], $rule->shareInstances) : [];
			$args = array_merge($args, !empty($rule->constructParams) ? $this->expand($rule->constructParams, $share) : [], $share);
			$parameters = [];
			
			foreach ($paramClasses as $class) {
				for ($i = 0; $i < count($args); $i++) {
					if ($class && $args[$i] instanceof $class) {
						$parameters[] = array_splice($args, $i, 1)[0];
						continue 2;
					}
				}
				if (isset($subs[$class])) $parameters[] = is_string($subs[$class]) ? $this->create($subs[$class]) : $this->expand($subs[$class]);
				else if ($class) $parameters[] = $this->create($class, $share, in_array($class, array_map('strtolower', $rule->newInstances)));
				else if (count($args) > 0) $parameters[] = array_shift($args);
			}
			return $parameters;
		};	
	}
}

class Rule {
	public $shared = false;
	public $constructParams = [];
	public $substitutions = [];
	public $newInstances = [];
	public $instanceOf;
	public $call = [];
	public $inherit = true;
	public $shareInstances = [];
}

class Instance {
	public $name;
	public function __construct($instance) {
		$this->name = $instance;
	}
}