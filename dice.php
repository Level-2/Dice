<?php 
/* @description 		Dice - A minimal Dependency Injection Container for PHP  
 * @author				Tom Butler tom@r.je
 * @copyright			2012-2014 Tom Butler <tom@r.je>
 * @link				http://r.je/dice.html
 * @license				http://www.opensource.org/licenses/bsd-license.php  BSD License 
 * @version				1.1.1
 */
namespace Dice;
class Dice {
	private $rules = [];
	private $instances = [];
		
	public function addRule($name, Rule $rule) {
		$rule->substitutions = array_change_key_case($rule->substitutions);
		$this->rules[strtolower(trim($name, '\\'))] = $rule;
	}
	
	public function getRule($name) {
		if (isset($this->rules[strtolower(trim($name, '\\'))])) return $this->rules[strtolower(trim($name, '\\'))];
		foreach ($this->rules as $key => $value) {
			if ($value->instanceOf === null && $key !== '*' && is_subclass_of($name, $key) && $value->inherit === true) return $value;
		}
		return isset($this->rules['*']) ? $this->rules['*'] : new Rule;
	}
	
	public function create($component, array $args = [], $forceNewInstance = false) {
		if ($component instanceof Instance) $component = $component->name;		
		$component = trim($component, '\\');
		
		if (!isset($this->rules[strtolower($component)]) && !class_exists($component)) throw new \Exception('Class does not exist for creation: ' . $component);
		
		if (!$forceNewInstance && isset($this->instances[strtolower($component)])) return $this->instances[strtolower($component)];
		
		$rule = $this->getRule($component);
		$className = (!empty($rule->instanceOf)) ? $rule->instanceOf : $component;		
		$share = $this->expandParams($rule->shareInstances);		
		$params = $this->getMethodParams($className, '__construct', $rule->substitutions, $rule->newInstances, array_merge($share, $args, $this->expandParams($rule->constructParams)), $share);
				
		$object = (count($params) > 0) ? (new \ReflectionClass($className))->newInstanceArgs($params) : $object = new $className;
		if ($rule->shared === true) $this->instances[strtolower($component)] = $object;
		foreach ($rule->call as $call) call_user_func_array([$object, $call[0]], $this->getMethodParams($className, $call[0], [], [], array_merge($this->expandParams($call[1]), $args)));
		return $object;
	}
		
	private function expandParams(array $params = [], array $newInstances = []) {
		for ($i = 0; $i < count($params); $i++) {
			if ($params[$i] instanceof Instance) $params[$i] = $this->create($params[$i]->name, []);
			else $params[$i] = is_callable($params[$i]) ? call_user_func($params[$i], $this) : $params[$i];
		}
		return $params;
	}
	
	private function getMethodParams($className, $method, array $substitutions = [], array $newInstances = [], array $args = [], array $share = []) {
		if (!method_exists($className, $method)) return [];
		$params = (new \ReflectionMethod($className, $method))->getParameters();
		$parameters = [];		
		foreach ($params as $param) {
			$class = $param->getClass() ? $param->getClass()->name : false;	
			foreach ($args as $argName => $arg) {				
				if ($class && $arg instanceof $class) {
					$parameters[] = $arg;
					unset($args[$argName]);
					continue 2;
				}
			}
			if ($class && isset($substitutions[strtolower($class)])) $parameters[] = is_string($substitutions[strtolower($class)]) ? new Instance($substitutions[strtolower($class)]) : $substitutions[strtolower($class)];
			else if ($class) $parameters[] = $this->create($class, $share, in_array(strtolower($class), array_map('strtolower', $newInstances)));
			else if (is_array($args) && count($args) > 0) $parameters[] = array_shift($args);
			else $parameters[] = $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null;			
		}
		return $this->expandParams($parameters, $newInstances);
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