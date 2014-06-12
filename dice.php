<?php 
/* @description 		Dice - A minimal Dependency Injection Container for PHP  
 * @author				Tom Butler tom@r.je
 * @copyright			2012-2014 Tom Butler <tom@r.je>
 * @link				http://r.je/dice.html
 * @license				http://www.opensource.org/licenses/bsd-license.php  BSD License 
 * @version				1.2.1
 */
namespace Dice;
class Dice {
	private $rules = [];
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
		$component = trim(($component instanceof Instance) ? $component->name : $component, '\\');

		if (!$forceNewInstance && isset($this->instances[strtolower($component)])) return $this->instances[strtolower($component)];
		
		$rule = $this->getRule($component);
		$class = new \ReflectionClass((!empty($rule->instanceOf)) ? $rule->instanceOf : $component);
		$constructor = $class->getConstructor();
		$object = ($constructor && end(($class->getMethods()))->isInternal()) ? $class->newInstanceArgs($this->getParams($constructor, $args, $rule)) : $class->newInstanceWithoutConstructor();
		
		if ($rule->shared === true) $this->instances[strtolower($component)] = $object;
		if ($constructor && !end(($class->getMethods()))->isInternal()) $constructor->invokeArgs($object, $this->getParams($constructor, $args, $rule));
		foreach ($rule->call as $call) $class->getMethod($call[0])->invokeArgs($object, $this->getParams($class->getMethod($call[0]), $call[1], new Rule));
		return $object;
	}
	
	private function expandParams(array $params, array $share = []) {
		foreach ($params as &$param) {
			if ($param instanceof Instance) $param = $this->create($param, $share);
			else if (is_callable($param)) $param = $param($this);
		}
		return $params;
	}
		
	private function getParams(\ReflectionMethod $method, array $args, Rule $rule) {
		$subs = array_change_key_case($rule->substitutions);
		$share = array_map([$this, 'create'], $rule->shareInstances);
		$args = array_merge($args, $this->expandParams($rule->constructParams, $share), $share);
		$parameters = [];
		
		foreach ($method->getParameters() as $param) {
			$class = $param->getClass() ? strtolower($param->getClass()->name) : null;
			for ($i = 0; $i < count($args); $i++) {
				if ($class && $args[$i] instanceof $class) {
					$parameters[] = array_splice($args, $i, 1)[0];
					continue 2;
				}
			}
			if (isset($subs[$class])) $parameters[] = is_string($subs[$class]) ? new Instance($subs[$class]) : $subs[$class];
			else if ($class) $parameters[] = $this->create($param->getClass()->name, $share, in_array($class, array_map('strtolower', $rule->newInstances)));
			else if (count($args) > 0) $parameters[] = array_shift($args);
		}
		return $this->expandParams($parameters);
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