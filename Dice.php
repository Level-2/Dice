<?php
/* @description     Dice - A minimal Dependency Injection Container for PHP         *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2012-2015 Tom Butler <tom@r.je> | http://r.je/dice.html         *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         2.0                                                             */
namespace Dice;
class Dice {
	private $rules = ['*' => ['shared' => false, 'constructParams' => [], 'shareInstances' => [], 'call' => [], 'inherit' => true, 'substitutions' => [], 'instanceOf' => null, 'newInstances' => []]];
	private $cache = [];
	private $instances = [];

	public function addRule($name, array $rule) {
		$this->rules[ltrim(strtolower($name), '\\')] = array_merge($this->getRule($name), $rule);
	}

	public function getRule($name) {
		if (isset($this->rules[strtolower(ltrim($name, '\\'))])) return $this->rules[strtolower(ltrim($name, '\\'))];
		foreach ($this->rules as $key => $rule) {
			if ($rule['instanceOf'] === null && $key !== '*' && is_subclass_of($name, $key) && $rule['inherit'] === true) return $rule;
		}
		return $this->rules['*'];
	}

	public function create($name, array $args = [], $forceNewInstance = false, $share = []) {
		if (!$forceNewInstance && isset($this->instances[$name])) return $this->instances[$name];
		if (empty($this->cache[$name])) $this->cache[$name] = $this->getClosure($name, $this->getRule($name));
		return $this->cache[$name]($args, $share);
	}

	private function getClosure($name, array $rule) {
		$class = new \ReflectionClass(isset($rule['instanceOf']) ? $rule['instanceOf'] : $name);
		$constructor = $class->getConstructor();
		$params = $constructor ? $this->getParams($constructor, $rule) : null;

		if ($rule['shared']) $closure = function($args, $share) use ($class, $name, $constructor, $params) {
			$this->instances[$name] = $class->newInstanceWithoutConstructor();
			if ($constructor) $constructor->invokeArgs($this->instances[$name], $params($args, $share));
			return $this->instances[$name];
		};			
		else if ($params) $closure = function($args, $share) use ($class, $params) { return new $class->name(...$params($args, $share)); };
		else $closure = function($args, $share) use ($class) { return new $class->name;	};

		return $rule['call'] ? function ($args, $share) use ($closure, $class, $rule) {
			$object = $closure($args, $share);
			foreach ($rule['call'] as $call) $object->{$call[0]}(...$this->getParams($class->getMethod($call[0]), $rule)->__invoke($this->expand($call[1])));
			return $object;
		} : $closure;
	}

	private function expand($param, array $share = []) {
		if (is_array($param) && isset($param['instance'])) {
			return is_callable($param['instance']) ? call_user_func($param['instance'], ...(isset($param['params']) ? $this->expand($param['params']) : [$this])) : $this->create($param['instance'], [], false, $share);
		}
		else if (is_array($param)) foreach ($param as &$value) $value = $this->expand($value, $share); 		
		return $param;
	}

	private function getParams(\ReflectionMethod $method, array $rule) {
		$paramInfo = [];
		foreach ($method->getParameters() as $param) {
			$class = $param->getClass() ? $param->getClass()->name : null;
			$paramInfo[] = [$class, $param->allowsNull(), array_key_exists($class, $rule['substitutions']), in_array($class, $rule['newInstances'])];
		}
		return function($args, $share = []) use ($paramInfo, $rule) {
			if ($rule['shareInstances']) $share = array_merge($share, array_map([$this, 'create'], $rule['shareInstances']));
			if ($share || $rule['constructParams']) $args = array_merge($args, $this->expand($rule['constructParams']), $share);
			$parameters = [];

			foreach ($paramInfo as list($class, $allowsNull, $sub, $new)) {
				if ($args) foreach ($args as $i => $arg) {
					if ($class && $args[$i] instanceof $class || ($args[$i] === null && $allowsNull)) {
						$parameters[] = array_splice($args, $i, 1)[0];
						continue 2;
					}
				}
				if ($class) $parameters[] = $sub ? $this->expand($rule['substitutions'][$class], $share) : $this->create($class, [], $new, $share);
				else if ($args) $parameters[] = $this->expand(array_shift($args));
			}
			return $parameters;
		};
	}
}
