<?php

namespace Dice;

/**
 * Dice
 *
 * A minimal Dependency Injection Container for PHP
 *
 * @author      Tom Butler <tom@r.je>
 * @copyright   2012-2018 Tom Butler <tom@r.je>
 * @license     BSD https://opensource.org/licenses/bsd-license.php
 * @version     3.0
 * @link        https://github.com/Level-2/Dice
 */
class Dice
{
    const CONSTANT = 'Dice::CONSTANT';
    const GLOBAL = 'Dice::GLOBAL';
    const INSTANCE = 'Dice::INSTANCE';

    /**
     * Rules which have been set using addRule()
     *
     * @var array
     */
    private $rules = [];

    /**
     * A cache of closures based on class name so each class is only reflected once
     *
     * @var array
     */
    private $cache = [];

    /**
     * Stores any instances marked as 'shared' so create() can return the same instance
     *
     * @var array
     */
    private $instances = [];

    /**
     * Add a $rule to the class $name
     *
     * For the list of available rules see {@link https://r.je/dice.html#example3}
     *
     * @param string $name Class's name to add the rule to
     * @param array  $rule An associated array of rules
     *
     * @return void
     */
    public function addRule(string $name, array $rule)
    {
        if (isset($rule['instanceOf']) && (!array_key_exists('inherit', $rule) || $rule['inherit'] === true )) {
            $rule = array_replace_recursive($this->getRule($rule['instanceOf']), $rule);
        }
        // Allow substitutions rules to be defined with a leading a slash
        if (isset($rule['substitutions'])) {
            foreach ($rule['substitutions'] as $key => $value) {
                $rule[ltrim($key, '\\')] = $value;
            }
        }

        $this->rules[ltrim(strtolower($name), '\\')] = array_replace_recursive($this->getRule($name), $rule);
    }

    /**
     * Add multiple rules
     *
     * Add rules for multiple classes
     *
     * @param array $rules Array of rules in this format: [class_name => rule]
     *
     * @return void
     */
    public function addRules($rules)
    {
        if (is_string($rules)) {
            $rules = json_decode(file_get_contents($rules), true);
        }
        
        foreach ($rules as $name => $rule) {
            $this->addRule($name, $rule);
        }
    }

    /**
     * Get a class's rules
     *
     * Return an array of rules for the given class
     *
     * @param string $name Class's name
     *
     * @return array Array of rules
     */
    public function getRule(string $name):array
    {
        $lcName = strtolower(ltrim($name, '\\'));

        if (isset($this->rules[$lcName])) {
            return $this->rules[$lcName];
        }

        foreach ($this->rules as $key => $rule) {
            // Find a rule which matches the class described in $name where:
            // It's not a named instance, the rule is applied to a class name
            if (empty($rule['instanceOf'])
                // It's not the default rule
                && $key !== '*'
                // The rule is applied to a parent class
                && is_subclass_of($name, $key)
                // And that rule should be inherited to subclasses
                && (!array_key_exists('inherit', $rule) || $rule['inherit'] === true )) {
                    return $rule;
            }
        }

        // No rule has matched, return the default rule if it's set
        return isset($this->rules['*']) ? $this->rules['*'] : [];
    }

    /**
     * Create object
     *
     * Returns a fully constructed object based on $name using $args and $share as constructor arguments if supplied
     *
     * @param string $name  The name of the class to instantiate
     * @param array  $args  Array of additional arguments to be passed into the constructor
     * @param array  $share Whether this class instance should be shared
     *
     * @return void The constructed object
     */
    public function create(string $name, array $args = [], array $share = [])
    {
        // Is there a shared instance set? Return it.
        // Better here than a closure for this, calling a closure is slower.
        if (!empty($this->instances[$name])) {
            return $this->instances[$name];
        }

        // Create a closure for creating the object if there isn't one already
        if (empty($this->cache[$name])) {
            $this->cache[$name] = $this->getClosure($name, $this->getRule($name));
        }

        // Call the cached closure which will return a fully constructed object of type $name
        return $this->cache[$name]($args, $share);
    }

    /**
     * Get a closure
     *
     * Returns a closure for creating object $name based on $rule, caching the reflection object for later use
     *
     * @param string $name Name of the class to get the closure for
     * @param array  $rule Assosiative array of rules, see {@link https://r.je/dice.html#example3} for usage
     *
     * @return callable The closure
     */
    private function getClosure(string $name, array $rule)
    {
        // Reflect the class and constructor, this should only ever be done once per class and get cached
        $class = new \ReflectionClass(isset($rule['instanceOf']) ? $rule['instanceOf'] : $name);
        $constructor = $class->getConstructor();

        // Create parameter generating function in order to cache reflection on the parameters.
        // This way $reflect->getParameters() only ever gets called once
        $params = $constructor ? $this->getParams($constructor, $rule) : null;

        // PHP throws a fatal error rather than an exception when trying to instantiate an interface,
        // detect it and throw an exception instead
        if ($class->isInterface()) {
            $closure = function () {
                throw new \InvalidArgumentException('Cannot instantiate interface');
            };
        // Get a closure based on the type of object being created: Shared, normal or constructorless
        } elseif (!empty($rule['shared'])) {
            $closure = function (array $args, array $share) use ($class, $name, $constructor, $params) {
                // Shared instance: create the class without calling the constructor
                // (and write to \$name and $name, see issue #68)
                $this->instances[$name] = $this->instances[ltrim($name, '\\')] = $class->newInstanceWithoutConstructor();

                // Now call this constructor after constructing all the dependencies.
                // This avoids problems with cyclic references (issue #7)
                if ($constructor) {
                    $constructor->invokeArgs($this->instances[$name], $params($args, $share));
                }

                return $this->instances[$name];
            };
        } elseif ($params) {
            $closure = function (array $args, array $share) use ($class, $params) {
                // This class has depenencies, call the $params closure to generate them based on $args and $share
                return new $class->name(...$params($args, $share));
            };
        } else {
            $closure = function () use ($class) {
                // No constructor arguments, just instantiate the class
                return new $class->name;
            };
        }

        // If there are shared instances, create them and merge them with shared instances higher up the object graph
        if (isset($rule['shareInstances'])) {
            $closure = function (array $args, array $share) use ($closure, $rule) {
                return $closure($args, array_merge($args, $share, array_map([$this, 'create'], $rule['shareInstances'])));
            };
        }

        /*
         When $rule['call'] is set, wrap the closure in another closure
         which will call the required methods after constructing the object
         By putting this in a closure, the loop is never executed unless call is actually set
         */
        if (isset($rule['call'])) {
            return function (array $args, array $share) use ($closure, $class, $rule) {
                // Construct the object using the original closure
                $object = $closure($args, $share);
    
                foreach ($rule['call'] as $call) {
                    // Generate the method arguments using getParams() and call the returned closure
                    $params = $this->getParams($class->getMethod($call[0]), ['shareInstances' => isset($rule['shareInstances']) ? $rule['shareInstances'] : [] ])(($this->expand(isset($call[1]) ? $call[1] : [])));
                    $return = $object->{$call[0]}(...$params);
                    if (isset($call[2]) && is_callable($call[2])) {
                        call_user_func($call[2], $return);
                    }
                }
                return $object;
            };
        } else {
            return $closure;
        }
    }

    /**
     * Looks for Dice::INSTANCE, Dice::GLOBAL or Dice::CONSTANT array keys in $param
     * and when found returns an object based on the value.
     * See {@link https://r.je/dice.html#example3-1}
     *
     * @param array|string $param            Either a string or an array
     * @param array        $share            Array of instances from 'shareInstances'
     * @param bool         $createFromString
     *
     * @return mixed
     */
    private function expand($param, array $share = [], bool $createFromString = false)
    {
        if (is_array($param)) {
            //if a rule specifies Dice::INSTANCE, look up the relevant instance
            if (isset($param[self::INSTANCE])) {
                //Check for 'params' which allows parameters to be sent to the instance when it's created
                //Either as a callback method or to the constructor of the instance
                $args = isset($param['params']) ? $this->expand($param['params']) : [];

                //Support Dice::INSTANCE by creating/fetching the specified instance
                if (is_callable($param[self::INSTANCE])) {
                    return call_user_func($param[self::INSTANCE], ...$args);
                } else {
                    return $this->create($param[self::INSTANCE], array_merge($args, $share));
                }
            } elseif (isset($param[self::GLOBAL])) {
                return $GLOBALS[$param[self::GLOBAL]];
            } elseif (isset($param[self::CONSTANT])) {
                return constant($param[self::CONSTANT]);
            } else {
                foreach ($param as $name => $value) {
                    $param[$name] = $this->expand($value, $share);
                }
            }
        }

        return is_string($param) && $createFromString ? $this->create($param) : $param;
    }

     /**
      * Returns a closure that generates arguments for $method based on $rule and any $args passed into the closure
      *
      * @param \ReflectionMethod $method An instance of ReflectionMethod
      *                                  See: {@link https://secure.php.net/manual/en/class.reflectionmethod.php}
      * @param array             $rule   An associative array of rules
      *                                  See {@link https://r.je/dice.html#example3} for usage
      *
      * @return callable A closure that uses the cached information to generate the arguments for the method
      */
    private function getParams(\ReflectionMethod $method, array $rule)
    {
        // Cache some information about the parameter in $paramInfo so (slow) reflection isn't needed every time
        $paramInfo = [];
        foreach ($method->getParameters() as $param) {
            $class = $param->getClass() ? $param->getClass()->name : null;
            $paramInfo[] = [$class, $param, isset($rule['substitutions']) && array_key_exists($class, $rule['substitutions'])];
        }

        // Return a closure that uses the cached information to generate the arguments for the method
        return function (array $args, array $share = []) use ($paramInfo, $rule) {
            // Now merge all the possible parameters: user-defined in the rule via constructParams, shared instances and the $args argument from $dice->create();
            if ($share || isset($rule['constructParams'])) {
                $args = array_merge($args, isset($rule['constructParams']) ? $this->expand($rule['constructParams'], $share) : [], $share);
            }

            $parameters = [];

            // Now find a value for each method parameter
            foreach ($paramInfo as list($class, $param, $sub)) {
                // First loop through $args and see whether or not each value
                // can match the current parameter based on type hint
                if ($args) {
                    // This if statement actually gives a ~10% speed increase when $args isn't set
                    foreach ($args as $i => $arg) {
                        if ($class && ($arg instanceof $class || ($arg === null && $param->allowsNull()))) {
                            // The argument matched, store it and remove it from $args so it won't wrongly match another parameter
                            $parameters[] = array_splice($args, $i, 1)[0];
                            // Move on to the next parameter
                            continue 2;
                        }
                    }
                }

                // When nothing from $args matches but a class is type hinted, create an instance to use, using a substitution if set
                if ($class) {
                    try {
                        $parameters[] = $sub ? $this->expand($rule['substitutions'][$class], $share, true) : $this->create($class, [], $share);
                    } catch (\InvalidArgumentException $e) {
                    }
                // For variadic parameters, provide remaining $args
                } elseif ($param->isVariadic()) {
                    $parameters = array_merge($parameters, $args);
                } elseif ($args && (!$param->getType() || call_user_func('is_' . $param->getType()->getName(), $args[0]))) {
                    // There is no type hint, take the next available value from $args (and remove it from $args to stop it being reused)
                    // Support PHP 7 scalar type hinting,  is_a('string', 'foo') doesn't work so this a
                    // is hacky AF workaround: call_user_func('is_' . $type, '')
                    // There's no type hint and nothing left in $args, provide the default value or null
                    $parameters[] = $this->expand(array_shift($args));
                } else {
                    $parameters[] = $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null;
                }
            }

            // variadic functions will only have one argument. To account for those, append any remaining arguments to the list
            return $parameters;
        };
    }
}
