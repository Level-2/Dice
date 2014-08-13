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
		$this->rules[ltrim($name, '\\')] = $rule;
	}

	public function getRule($name) {
		if (isset($this->rules[ltrim($name, '\\')])) return $this->rules[ltrim($name, '\\')];
		foreach ($this->rules as $key => $rule) {
			if ($rule->instanceOf === null && $key !== '*' && is_subclass_of($name, $key) && $rule->inherit === true) return $rule;
		}
		return isset($this->rules['*']) ? $this->rules['*'] : new Rule;
	}
		
	public function create($component, array $args = [], $forceNewInstance = false) {
		if (!$forceNewInstance && isset($this->instances[$component])) return $this->instances[$component];
		if (!isset($this->cache[$component])) {
			$rule = $this->getRule($component);
			$class = new \ReflectionClass(empty($rule->instanceOf) ? $component : $rule->instanceOf);
			$constructor = $class->getConstructor();			
			$params = $constructor ? $this->getParams($constructor, $rule) : null;
			
			$this->cache[$component] = function($args, $forceNewInstance) use ($component, $rule, $class, $constructor, $params) {
				if ($rule->shared) {
                                        if ($constructor) {
                                                try {
                                                        $this->instances[$component] = $object = $class->newInstanceWithoutConstructor();
                                                        $constructor->invokeArgs($object, $params($args));
                                                } catch (\ReflectionException $r) {
                                                        $this->instances[$component] = $object = $class->newInstanceArgs($params($args));
                                                }
					} else {
						$this->instances[$component] = $object = $class->newInstanceWithoutConstructor();
					}
				}
				else $object = $params ? $class->newInstanceArgs($params($args)) : new $class->name;
				if (!empty($rule->call)) foreach ($rule->call as $call) $class->getMethod($call[0])->invokeArgs($object, call_user_func($this->getParams($class->getMethod($this->expand($call[0])), new Rule), $call[1]));
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
		$subs = empty($rule->substitutions) ? null :$rule->substitutions;
		$paramClasses = [];
		foreach ($method->getParameters() as $param) $paramClasses[] = $param->getClass() ? $param->getClass()->name : null;
		
		return function($args) use ($paramClasses, $rule, $subs) {
			$share = empty($rule->shareInstances) ? [] : array_map([$this, 'create'], $rule->shareInstances);
			if (!empty($share) || !empty($rule->constructParams)) $args = array_merge($args, $this->expand($rule->constructParams, $share), $share);
			$parameters = [];
			
			foreach ($paramClasses as $class) {
				if (!empty($args)) for ($i = 0; $i < count($args); $i++) {
					if ($class && $args[$i] instanceof $class) {
						$parameters[] = array_splice($args, $i, 1)[0];
						continue 2;
					}
				}
				if (!empty($subs) && isset($subs[$class])) $parameters[] = is_string($subs[$class]) ? $this->create($subs[$class]) : $this->expand($subs[$class]);
				else if (!empty($class)) $parameters[] = $this->create($class, $share, !empty($rule->newInstances) && in_array(strtolower($class), array_map('strtolower', $rule->newInstances)));
				else if (!empty($args)) $parameters[] = array_shift($args);
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
