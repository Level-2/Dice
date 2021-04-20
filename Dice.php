<?php
/* @description Dice - A minimal Dependency Injection Container for PHP
 * @author Tom Butler tom@r.je
 * @copyright 2012-2020 Tom Butler <tom@r.je> | https://r.je/dice
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version 4.0 */
namespace Dice;
class Dice {
	const CONSTANT = 'Dice::CONSTANT';
	const GLOBAL = 'Dice::GLOBAL';
	const INSTANCE = 'Dice::INSTANCE';
	const CHAIN_CALL = 'Dice::CHAIN_CALL';
	const SELF = 'Dice::SELF';
	/**
	 * @var array $rules Rules which have been set using addRule()
	 */
	private $rules = [];

	/**
	 * @var array $cache A cache of closures based on class name so each class is only reflected once
	 */
	private $cache = [];

	/**
	 * @var array $instances Stores any instances marked as 'shared' so create() can return the same instance
	 */
	private $instances = [];

	/**
	 * Add a rule $rule to the class $name
	 * @param string $name The name of the class to add the rule for
	 * @param array $rule The container can be fully configured using rules provided by associative arrays. See {@link https://r.je/dice.html#example3} for a description of the rules.
	 */
	public function addRule(string $name, array $rule): self {
		$dice = clone $this;
		$this->addRuleTo($dice, $name, $rule);
		return $dice;
	 }

	/**
	* Add rules as array. Useful for JSON loading $dice->addRules(json_decode(file_get_contents('foo.json'));
	* @param array Rules in a single array [name => $rule] format
	*/
	public function addRules($rules): self {
		if (is_string($rules)) $rules = json_decode(file_get_contents($rules), true);
		$dice = clone $this;
		foreach ($rules as $name => $rule) $this->addRuleTo($dice,$name, $rule);
		return $dice;
	}

	private function addRuleTo(Dice $dice, string $name, array $rule) {
        if (isset($rule['instanceOf']) && (!array_key_exists('inherit', $rule) || $rule['inherit'] === true ))
            $rule = array_replace_recursive($dice->getRule($rule['instanceOf']), $rule);
        //Allow substitutions rules to be defined with a leading a slash
        if (isset($rule['substitutions'])) foreach($rule['substitutions'] as $key => $value) $rule['substitutions'][ltrim($key,  '\\')] = $value;
        //Clear any existing instance or cache for this class
        unset($dice->instances[$name], $dice->cache[$name]);
        $dice->rules[ltrim(strtolower($name), '\\')] = array_replace_recursive($dice->getRule($name), $rule);
    }

	/**
	 * Returns the rule that will be applied to the class $name when calling create()
	 * @param string name The name of the class to get the rules for
	 * @return array The rules for the specified class
	 */
	public function getRule(string $name): array {
		$lcName = strtolower(ltrim($name, '\\'));
		if (isset($this->rules[$lcName])) return $this->rules[$lcName];

		foreach ($this->rules as $key => $rule) { 							// Find a rule which matches the class described in $name where:
			if (empty($rule['instanceOf']) 		 							// It's not a named instance, the rule is applied to a class name
				&& $key !== '*' 				 							// It's not the default rule
				&& is_subclass_of($name, $key)								// The rule is applied to a parent class
				&& (!array_key_exists('inherit', $rule) || $rule['inherit'] === true )) // And that rule should be inherited to subclasses
			return $rule;
		}
		// No rule has matched, return the default rule if it's set
		return isset($this->rules['*']) ? $this->rules['*'] : [];
	}

	/**
	 * Returns a fully constructed object based on $name using $args and $share as constructor arguments if supplied
	 * @param string name The name of the class to instantiate
	 * @param array $args An array with any additional arguments to be passed into the constructor upon instantiation
	 * @param array $share a list of defined in shareInstances for objects higher up the object graph, should only be used internally
	 * @return object A fully constructed object based on the specified input arguments
	 */
	public function create(string $name, array $args = [], array $share = []) {
		// Is there a shared instance set? Return it. Better here than a closure for this, calling a closure is slower.
		if (!empty($this->instances[$name])) return $this->instances[$name];

		// Create a closure for creating the object if there isn't one already
		if (empty($this->cache[$name])) $this->cache[$name] = $this->getClosure(ltrim($name, '\\'), $this->getRule($name));

		// Call the cached closure which will return a fully constructed object of type $name
		return $this->cache[$name]($args, $share);
	}

	/**
	 * Returns a closure for creating object $name based on $rule, caching the reflection object for later use
	 * @param string $name the Name of the class to get the closure for
	 * @param array $rule The container can be fully configured using rules provided by associative arrays. See {@link https://r.je/dice.html#example3} for a description of the rules.
	 * @return callable A closure
	 */
	private function getClosure(string $name, array $rule) {
		// Reflect the class and constructor, this should only ever be done once per class and get cached
		$class = new \ReflectionClass(isset($rule['instanceOf']) ? $rule['instanceOf'] : $name);
		$constructor = $class->getConstructor();

		// Create parameter generating function in order to cache reflection on the parameters. This way $reflect->getParameters() only ever gets called once
		$params = $constructor ? $this->getParams($constructor, $rule) : null;
		//PHP throws a fatal error rather than an exception when trying to instantiate an interface, detect it and throw an exception instead
		if ($class->isInterface()) $closure = function() {
			throw new \InvalidArgumentException('Cannot instantiate interface');
		};
		// Get a closure based on the type of object being created: Shared, normal or constructorless
		else if ($params) $closure = function (array $args, array $share) use ($class, $params) {
			// This class has depenencies, call the $params closure to generate them based on $args and $share
			return new $class->name(...$params($args, $share));
		};
		else $closure = function () use ($class) { // No constructor arguments, just instantiate the class
			return new $class->name;
		};

		if (!empty($rule['shared'])) $closure = function (array $args, array $share) use ($class, $name, $constructor, $params, $closure) {
			//Internal classes may not be able to be constructed without calling the constructor and will not suffer from #7, construct them normally.
			if ($class->isInternal()) $this->instances[$class->name] = $this->instances['\\' . $class->name] = $closure($args, $share);
			else {
				//Otherwise, create the class without calling the constructor (and write to \$name and $name, see issue #68)
				$this->instances[$name] = $this->instances['\\' . $name] = $class->newInstanceWithoutConstructor();
				// Now call this constructor after constructing all the dependencies. This avoids problems with cyclic references (issue #7)
				if ($constructor) $constructor->invokeArgs($this->instances[$name], $params($args, $share));
			}
			return $this->instances[$name];
		};
		// If there are shared instances, create them and merge them with shared instances higher up the object graph
		if (isset($rule['shareInstances'])) $closure = function(array $args, array $share) use ($closure, $rule) {
			 foreach($rule['shareInstances'] as $instance) $share[] = $this->create($instance, [], $share);
             return $closure($args, $share);
		};
		// When $rule['call'] is set, wrap the closure in another closure which will call the required methods after constructing the object
		// By putting this in a closure, the loop is never executed unless call is actually set
		return isset($rule['call']) ? function (array $args, array $share) use ($closure, $class, $rule, $name) {
			// Construct the object using the original closure
			$object = $closure($args, $share);

			foreach ($rule['call'] as $call) {
				// Generate the method arguments using getParams() and call the returned closure
				$params = $this->getParams($class->getMethod($call[0]), ['shareInstances' => isset($rule['shareInstances']) ? $rule['shareInstances'] : [] ])(($this->expand(isset($call[1]) ? $call[1] : [])), $share);
				$return = $object->{$call[0]}(...$params);
				if (isset($call[2])) {
					if ($call[2] === self::CHAIN_CALL) {
						if (!empty($rule['shared'])) $this->instances[$name] = $return;
                        if (is_object($return)) $class = new \ReflectionClass(get_class($return));
						$object = $return;
					}
					else if (is_callable($call[2])) call_user_func($call[2], $return);
				}
			}
			return $object;
		} : $closure;
	}

	/**
	 * Looks for Dice::INSTANCE, Dice::GLOBAL or Dice::CONSTANT array keys in $param and when found returns an object based on the value see {@link https:// r.je/dice.html#example3-1}
	 * @param mixed $param Either a string or an array,
	 * @param array $share Array of instances from 'shareInstances', required for calls to `create`
	 * @param bool $createFromString
	 * @return mixed
	 */
	private function expand($param, array $share = [], bool $createFromString = false) {
		if (is_array($param)) {
			//if a rule specifies Dice::INSTANCE, look up the relevant instance
			if (isset($param[self::INSTANCE])) {
			    if ($param[self::INSTANCE] === self::SELF) return $this;
				//Check for 'params' which allows parameters to be sent to the instance when it's created
				//Either as a callback method or to the constructor of the instance
				$args = isset($param['params']) ? $this->expand($param['params']) : [];

				//Support Dice::INSTANCE by creating/fetching the specified instance
				if (is_array($param[self::INSTANCE])) $param[self::INSTANCE][0] = $this->expand($param[self::INSTANCE][0], $share, true);
				if (is_callable($param[self::INSTANCE])) return call_user_func($param[self::INSTANCE], ...$args);
				else return $this->create($param[self::INSTANCE], array_merge($args, $share));
			}
			else if (isset($param[self::GLOBAL])) return $GLOBALS[$param[self::GLOBAL]];
			else if (isset($param[self::CONSTANT])) return constant($param[self::CONSTANT]);
			else foreach ($param as $name => $value) $param[$name] = $this->expand($value, $share);
		}

		return is_string($param) && $createFromString ? $this->create($param) : $param;
	}
	/**
	* Looks through the array $search for any object which can be used to fulfil $param
	The original array $search is modifed so must be passed by reference.

	*/
	private function matchParam(\ReflectionParameter $param, $class, array &$search) {
		foreach ($search as $i => $arg) {
			if ($class && ($arg instanceof $class || ($arg === null && $param->allowsNull()))) {
				// The argument matched, return it and remove it from $search so it won't wrongly match another parameter
				return array_splice($search, $i, 1)[0];
			}
		}
		return false;
	}
	/**
	 * Returns a closure that generates arguments for $method based on $rule and any $args passed into the closure
	 * @param object $method An instance of ReflectionMethod (see: {@link http:// php.net/manual/en/class.reflectionmethod.php})
	 * @param array $rule The container can be fully configured using rules provided by associative arrays. See {@link https://r.je/dice.html#example3} for a description of the rules.
	 * @return callable A closure that uses the cached information to generate the arguments for the method
	 */
	private function getParams(\ReflectionMethod $method, array $rule) {
		// Cache some information about the parameter in $paramInfo so (slow) reflection isn't needed every time
		$paramInfo = [];
		foreach ($method->getParameters() as $param) {
			$type = $param->getType();

			$class = $type instanceof \ReflectionNamedType && !$type->isBuiltIn() ? $type->getName() : null;

			$paramInfo[] = [$class, $param, isset($rule['substitutions']) && array_key_exists($class, $rule['substitutions'])];
		}

		// Return a closure that uses the cached information to generate the arguments for the method
		return function (array $args, array $share = []) use ($paramInfo, $rule) {
			// If the rule has construtParams set, construct any classes reference and use them as $args
			if (isset($rule['constructParams'])) $args = array_merge($args, $this->expand($rule['constructParams'], $share));

			// Array of matched parameters
			$parameters = [];

			// Fnd a value for each method argument
			foreach ($paramInfo as list($class, $param, $sub)) {
				// Loop through $args and see whether or not each value can match the current parameter based on type hint
				if ($args && ($match = $this->matchParam($param, $class, $args)) !== false)  {
					$parameters[] = $match;
				}
				// Do the same with $share
				else if ($share && ($match = $this->matchParam($param, $class, $share)) !== false)  {
					$parameters[] = $match;
				}
				// When nothing from $args or $share matches but a class is type hinted, create an instance to use, using a substitution if set
				else if ($class)	try {
					if ($sub) {
						$parameters[] = $this->expand($rule['substitutions'][$class], $share, true);
					}
					else {
						$parameters[] = !$param->allowsNull() ? $this->create($class, [], $share) : null;
					}
				}
				catch (\InvalidArgumentException $e) {
				}
				// Support PHP 7 scalar type hinting,  is_a('string', 'foo') doesn't work so this is a hacky AF workaround: call_user_func('is_' . $type, '')

				//Find a match in $args for scalar types
				else if ($args && $param->getType()) {
					for ($i = 0; $i < count($args); $i++) {
						if (call_user_func('is_' . $param->getType()->getName(), $args[$i])) {
							$parameters[] = array_splice($args, $i, 1)[0];
						}
					}
				}
				else if ($args) {
					$parameters[] = $this->expand(array_shift($args));
				}
				// For variadic parameters, provide remaining $args
				else if ($param->isVariadic()) {
					$parameters = array_merge($parameters, $args);
				}
				// There's no type hint and nothing left in $args, provide the default value or null
				else {
					$parameters[] = $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null;
				}
			}
			return $parameters;
		};
	}
}
