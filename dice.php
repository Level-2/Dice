<?php 
/* @description 		Dice - A minimal Dependency Injection Container for PHP  
 * @author				Tom Butler tom@r.je
 * @copyright			2012-2014 Tom Butler <tom@r.je>
 * @link				http://r.je/dice.html
 * @license				http://www.opensource.org/licenses/bsd-license.php  BSD License 
 * @version				1.3.0
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
		if (!$forceNewInstance && isset($this->instances[strtolower($component)])) return $this->instances[strtolower($component)];

		$rule = $this->getRule($component);
		$class = new \ReflectionClass((!empty($rule->instanceOf)) ? $rule->instanceOf : $component);
		$constructor = $class->getConstructor();
		$params = $this->getParams($constructor, $args, $rule);
		$object = ($constructor && end(($class->getMethods()))->isInternal()) ? $class->newInstanceArgs(iterator_to_array($params)) : $class->newInstanceWithoutConstructor();

		if ($rule->shared === true) $this->instances[strtolower($component)] = $object;
		if ($constructor && !end(($class->getMethods()))->isInternal()) $constructor->invokeArgs($object, iterator_to_array($params));
		foreach ($rule->call as $call) $class->getMethod($call[0])->invokeArgs($object, iterator_to_array($this->getParams($class->getMethod($call[0]), $call[1], new Rule)));
		return $object;
	}

	private function expand($param, array $share = []) {
		if (is_array($param)) return array_map([$this, 'expand'], $param);
		else if ($param instanceof Instance) $param = $this->create($param->name, $share);
		else if (is_callable($param)) $param = $param($this);
		return $param;
	}

	private function getParams(\ReflectionMethod $method, array $args, Rule $rule) {
		$subs = array_change_key_case($rule->substitutions);
		$share = array_map([$this, 'create'], $rule->shareInstances);
		$args = array_merge($args, $this->expand($rule->constructParams, $share), $share);
	
		foreach ($method->getParameters() as $param) {
			$class = $param->getClass() ? strtolower($param->getClass()->name) : null;
			for ($i = 0; $i < count($args); $i++) {
				if ($class && $args[$i] instanceof $class) {
					yield $this->expand(array_splice($args, $i, 1)[0]);
					continue 2;
				}
			}
			if (isset($subs[$class])) yield is_string($subs[$class]) ? $this->create($subs[$class]) : $this->expand($subs[$class]);
			else if ($class) yield $this->create($param->getClass()->name, $share, in_array($class, array_map('strtolower', $rule->newInstances)));
			else if (count($args) > 0) yield $this->expand(array_shift($args));
		}
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