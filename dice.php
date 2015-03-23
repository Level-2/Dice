<?php 
/* @description		Dice - A minimal Dependency Injection Container for PHP			*  
 * @author			Tom Butler tom@r.je												*
 * @copyright		2012-2014 Tom Butler <tom@r.je> | http://r.je/dice.html			*
 * @license			http://www.opensource.org/licenses/bsd-license.php  BSD License	*
 * @version			1.3.2															*/
namespace Dice;
class Dice {
	private $rules = [];
	private $cache = [];
	private $instances = [];
	
	public function addRule($name, Rule $rule) {
		$this->rules[ltrim(strtolower($name), '\\')] = $rule;
	}

	public function getRule($name) {
		if (isset($this->rules[strtolower(ltrim($name, '\\'))])) return $this->rules[strtolower(ltrim($name, '\\'))];
		foreach ($this->rules as $key => $rule) {
			if ($rule->instanceOf === null && $key !== '*' && is_subclass_of($name, $key) && $rule->inherit === true) return $rule;
		}
		return isset($this->rules['*']) ? $this->rules['*'] : new Rule;
	}
		
	public function create($component, array $args = [], $forceNewInstance = false, $share = []) {
		if (!$forceNewInstance && isset($this->instances[$component])) return $this->instances[$component];
		if (empty($this->cache[$component])) {
			$rule = $this->getRule($component);
			$class = new \ReflectionClass($rule->instanceOf ? $rule->instanceOf : $component);
			$constructor = $class->getConstructor();			
			$params = $constructor ? $this->getParams($constructor, $rule) : null;
			
			$this->cache[$component] = function($args) use ($component, $rule, $class, $constructor, $params, $share) {
				if ($rule->shared) {
					if ($constructor) {
						try {
							$this->instances[$component] = $object = $class->newInstanceWithoutConstructor();
						$constructor->invokeArgs($object, $params($args,$share));
						} catch (\ReflectionException $r) {
							$this->instances[$component] = $object = $class->newInstanceArgs($params($args,$share));
						}
					}
					else $this->instances[$component] = $object = $class->newInstanceWithoutConstructor();
				}
				else $object = $params ? $class->newInstanceArgs($params($args,$share)) : new $class->name;				
				if ($rule->call) foreach ($rule->call as $call) $class->getMethod($call[0])->invokeArgs($object, call_user_func($this->getParams($class->getMethod($call[0]), $rule), $this->expand($call[1])));
				return $object;
			};			
		}
		return $this->cache[$component]($args);
	}
			
	private function expand($param, array $share = []) {
		if (is_array($param)) return array_map(function($p) use($share) { return $this->expand($p, $share); }, $param);
		if ($param instanceof \Closure) return call_user_func($param, $this, $share);
		if ($param instanceof Instance && is_callable($param->name)) return call_user_func($param->name, $this, $share);
		else if ($param instanceof Instance) return $this->create($param->name, $share, false, $share);
		return $param;
	}
		
	private function getParams(\ReflectionMethod $method, Rule $rule) {	
		$paramInfo = [];
		foreach ($method->getParameters() as $param) {
			$class = $param->getClass() ? $param->getClass()->name : null;
			$paramInfo[] = [$class, $param->allowsNull(), array_key_exists($class, $rule->substitutions), in_array($class, $rule->newInstances)];
		}		
		return function($args, $share = []) use ($paramInfo, $rule) {
			if ($rule->shareInstances) $share = array_merge($share, array_map([$this, 'create'], $rule->shareInstances));			
			if ($share || $rule->constructParams) $args = array_merge($args, $this->expand($rule->constructParams, $share), $share);
			$parameters = [];
			
			foreach ($paramInfo as $param) {
				list($class, $allowsNull, $sub, $new) = $param;
				if ($args) for ($i = 0; $i < count($args); $i++) {
					if ($class && $args[$i] instanceof $class || !$args[$i] && $allowsNull) {
						$parameters[] = array_splice($args, $i, 1)[0];
						continue 2;
					}
				}
				if ($sub && is_string($rule->substitutions[$class])) $parameters[] = $this->create($rule->substitutions[$class], $share, $new, $share);
				if ($class) $parameters[] = $sub ? $this->expand($rule->substitutions[$class], $share) : $this->create($class, $share, $new, $share);
				else if ($args) $parameters[] = $this->expand(array_shift($args));
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
