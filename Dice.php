<?php
/* @description     Dice - A minimal Dependency Injection Container for PHP         *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2012-2015 Tom Butler <tom@r.je> | http://r.je/dice.html         *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         2.0                                                             */
namespace Dice;
class Dice {
	private $rules = [];
	private $cache = [];
	private $instances = [];

	public function addRule($name, array $rule) {
		$this->rules[ltrim(strtolower($name), '\\')] = array_merge($this->getRule($name), $rule);
	}

	public function getRule($name) {
		$lcName = strtolower(ltrim($name, '\\'));
		if (isset($this->rules[$lcName])) return $this->rules[$lcName];
		
		foreach ($this->rules as $key => $rule) {
			if (empty($rule['instanceOf']) && $key !== '*' && is_subclass_of($name, $key) && (!array_key_exists('inherit', $rule) || $rule['inherit'] === true )) return $rule;
		}
		return isset($this->rules['*']) ? $this->rules['*'] : [];
	}

	public function create($name, array $args = [], array $share = []) {
		if (!empty($this->instances[$name])) return $this->instances[$name];
		if (empty($this->cache[$name])) $this->cache[$name] = $this->getClosure($name, $this->getRule($name));
		return $this->cache[$name]($args, $share);
	}

	private function getClosure($name, array $rule) {
		$class = new \ReflectionClass(isset($rule['instanceOf']) ? $rule['instanceOf'] : $name);
		$constructor = $class->getConstructor();
		$params = $constructor ? $this->getParams($constructor, $rule) : null;

		if (isset($rule['shared']) && $rule['shared'] === true ) $closure = function (array $args, array $share) use ($class, $name, $constructor, $params) {
			try {
				$this->instances[$name] = $this->instances[ltrim($name, '\\')] = $class->newInstanceWithoutConstructor();	
			}
			catch (\ReflectionException $e) {
				$this->instances[$name] = $this->instances[ltrim($name, '\\')] = $class->newInstanceArgs($params($args, $share));
			}
			
			if ($constructor) $constructor->invokeArgs($this->instances[$name], $params($args, $share));
			return $this->instances[$name];
		};			
		else if ($params) $closure = function (array $args, array $share) use ($class, $params) { return $class->newInstanceArgs($params($args, $share)); };

		else $closure = function () use ($class) { return new $class->name;	};

		return isset($rule['call']) ? function (array $args, array $share) use ($closure, $class, $rule) {
			$object = $closure($args, $share);
			foreach ($rule['call'] as $call) call_user_func_array([$object, $call[0]] , $this->getParams($class->getMethod($call[0]), $rule)->__invoke($this->expand($call[1])));
			return $object;
		} : $closure;
	}

	/** looks for 'instance' array keys in $param and when found returns an object based on the value see https://r.je/dice.html#example3-1 */
	private function expand($param, array $share = [], $createFromString = false) {
		if (is_array($param) && isset($param['instance'])) {
			if (is_callable($param['instance'])) return call_user_func_array($param['instance'], (isset($param['params']) ? $this->expand($param['params']) : []));
			else return $this->create($param['instance'], $share);
		}
		else if (is_array($param)) foreach ($param as &$value) $value = $this->expand($value, $share); 		
		return is_string($param) && $createFromString ? $this->create($param) : $param;
	}

	private function getParams(\ReflectionMethod $method, array $rule) {
		$paramInfo = [];
		foreach ($method->getParameters() as $param) {
			$class = $param->getClass() ? $param->getClass()->name : null;
			$paramInfo[] = [$class, $param, isset($rule['substitutions']) && array_key_exists($class, $rule['substitutions'])];
		}
		return function (array $args, array $share = []) use ($paramInfo, $rule) {
			if (isset($rule['shareInstances'])) $share = array_merge($share, array_map([$this, 'create'], $rule['shareInstances']));
			if ($share || isset($rule['constructParams'])) $args = array_merge($args, isset($rule['constructParams']) ? $this->expand($rule['constructParams'], $share) : [], $share);
			$parameters = [];

			foreach ($paramInfo as $p) {
				list($class, $param, $sub) = $p;
				if ($args) foreach ($args as $i => $arg) {
					if ($class && ($arg instanceof $class || ($arg === null && $param->allowsNull()))) {
						$parameters[] = array_splice($args, $i, 1)[0];
						continue 2;
					}
				}
				if ($class) $parameters[] = $sub ? $this->expand($rule['substitutions'][$class], $share, true) : $this->create($class, [], $share);
				else if ($args) $parameters[] = $this->expand(array_shift($args));
				else $parameters[] = $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null;
			}
			return $parameters;
		};
	}
}
