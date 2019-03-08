[Dice PHP Dependency Injection Container](https://r.je/dice.html)
======================================

Dice is a minimalist Dependency Injection Container for PHP with a focus on being lightweight and fast as well as requiring as little configuration as possible.


Project Goals
-------------

1) To be lightweight and not a huge library with dozens of files (Dice is a single 100 line class) yet support all features (and more) offered by much more complex containers

2) To "just work". Basic functionality should work with zero configuration

3) Where configuration is required, it should be as minimal and reusable as possible as well as easy to use.

4) Speed! (See [the section on performance](#performance))


Installation
------------

Just include the lightweight `Dice.php` in your project and it's usable without any further configuration:

Simple example:

```php
<?php
class A {
	public $b;

	public function __construct(B $b) {
		$this->b = $b;
	}
}

class B {

}

require_once 'Dice.php';
$dice = new \Dice\Dice;

$a = $dice->create('A');

var_dump($a->b); //B object

?>
```


Full Documentation
------------------

For complete documentation please see the [Dice PHP Dependency Injection container home page](https://r.je/dice.html)


PHP version compatibility
-------------------------

Dice is compatible with PHP 7.0 and up, there are archived versions of Dice which support PHP 5.6 however this is no longer maintanied.


Performance
-----------

Dice uses reflection which is often wrongly labelled "slow". Reflection is considerably faster than loading and parsing a configuration file. There are a set of benchmarks [here](https://rawgit.com/TomBZombie/php-dependency-injection-benchmarks/master/test1-5_results.html) and [here](https://rawgit.com/TomBZombie/php-dependency-injection-benchmarks/master/test6_results.html) (To download the benchmark tool yourself see [this repository](https://github.com/TomBZombie/php-dependency-injection-benchmarks)) and Dice is faster than the others in most cases.

In the real world test ([test 6](https://rawgit.com/TomBZombie/php-dependency-injection-benchmarks/master/test6_results.html)) Dice is neck-and-neck with Pimple (which requires writing an awful lot of configuration code) and although Symfony\DependencyInjection is faster at creating objects, it has a larger overhead and you need to create over 500 objects on each page load until it becomes faster than Dice. The same is true of Phalcon, the overhead of loading the Phalcon extension means that unless you're creating well over a thousand objects per HTTP request, the overhead is not worthwhile.


Credits
------------

Originally developed by Tom Butler (@TomBZombie), with many thanks to daniel-meister (@daniel-meister), Garrett W. (@garrettw), maxwilms (@maxwilms) for bug fixes, suggestions and improvements.


Updates
------------

### 15/11/2018 4.0 Release - Backwards incompatible

Dice is now immutable and has better support for other immutable objects.

**New Features**

#### 1. Dice is Immutable

This avoids [issues surrounding mutability](https://www.yegor256.com/2014/06/09/objects-should-be-immutable.html) where a Dice instance is passed around the application and reconfigured. The only difference is that `addRules` and `addRule` return a new Dice instance with the updated rules rather than changing the state of the existing instance.

```php

// Pre-4.0 code:
$dice->addRule('PDO', ['shared' => true]);

$db = $dice->create('PDO');

// 4.0 code:
$dice = $dice->addRule('PDO', ['shared' => true]);

$db = $dice->create('PDO');
```

From a practical perspective in most cases just put `$dice = ` in front of any `$dice->addRule()` call and it will work as before.

#### 2. Support for Object Method Chaining

One feature some immutable objects have is they offer object chaining.

Consider the following Object:

```php

$httpRequest = new HTTPRequest();
$httpRequest = $httpRequest->url('http://example.org')->method('POST')->postdata('foo=bar');
```

It was not possible for Dice to consturuct the configured object in previous versions. As of 4.0 Dice supports chaining method call using the `call` rule and the `Dice::CHAIN_CALL` constant:

```php
$dice = $dice->addRule('HTTPRequest',
                ['call' => [
                    ['url', ['http://example.org'], Dice::CHAIN_CALL],
                    ['method', ['POST'], Dice::CHAIN_CALL ],
                    ['postdata', ['foo=bar'], Dice::CHAIN_CALL]
                 ]
                ]
);
```

Dice will replace the HTTPRequest object with the result of the chained call. This is also useful for factories:


```php
$dice = $dice->addRule('MyDatabase',
                [
                	'instanceOf' => 'DatabaseFactory',
                	'call' => [
                		['get', ['Database'], Dice::CHAIN_CALL]
                 ]
                ]
);

$database = $dice->create('MyDatabase');
//Equivalent of:

$factory = new DatabaseFactory();
$database = $factory->get('Database');
```


### 06/03/2018 3.0 Release - Backwards incompatible

**New Features**

#### 1. The JSON loader has been removed in favour of a new `addRules` method.

```php
$dice->addRules([
	'\PDO' => [
		'shared' => true
	],
	'Framework\Router' => [
		'constructParams' => ['Foo', 'Bar']
	]
]);
```

The purpose of this addition is to make the JSON loader redundant. Loading of rules from a JSON file can easily be achieved with the code:

```php
$dice->addRules(json_decode(file_get_contents('rules.json')));
```

#### 2. Better JSON file support: constants and superglobals

In order to improve support for rules being defined in external JSON files, constants and superglobals can now be passed into objects created by Dice.

For example, passing the `$_SERVER` superglobal into a router instance and calling PDO's `setAttribute` with `PDO::ATTR_ERRMODE` and `PDO::ERRMODE_EXCEPTION` can be achieved like this in a JSON file:

_rules.json_

```json
{
	"Router": {
		"constructParams": [
			{"Dice::GLOBAL": "_SERVER"}
		]
	},
	"PDO": {
		"shared": true,
		"constructParams": [
			"mysql:dbname=testdb;host=127.0.0.1",
			"dbuser",
			"dbpass"
		],
		"call": [
					[
						"setAttribute",
						[
							{"Dice::CONSTANT": "PDO::ATTR_ERRMODE"},
							{"Dice::CONSTANT": "PDO::ERRMODE_EXCEPTION"}
						]
					]
		]
	}
}
```

```php
$dice->addRules(json_decode(file_get_contents('rules.json')));
```

**Backwards incompatible changes**

1. Dice 3.0 requires PHP 7.0 or above, PHP 5.6 is no longer supported.

2. Dice no longer supports `'instance'` keys to signify instances. For example:

```php
$dice->addRule('ClassName', [
	'constructParams' => ['instance' => '$NamedPDOInstance']
]);
```

As noted in issue #125 this made it impossible to pass an array to a constructor if the array had a key `'instance'`. Instead, the new `\Dice\Dice::INSTANCE` constant should be used:

```php
$dice->addRule('ClassName', [
	'constructParams' => [\Dice\Dice::INSTANCE => '$NamedPDOInstance']
]);
```
_to make the constant shorter to type out, you can `use \Dice\Dice;` and reference `Dice::INSTANCE`_

10/06/2016

** Backwards incompatible change **

Based on [Issue 110](https://github.com/Level-2/Dice/pull/110) named instances using `instanceOf` will now inherit the rules applied to the class they are instances of:

```php

$rule = [];
$rule['shared'] = true;

$dice->addRule('MyClass', $rule);

$rule = [];
$rule['instanceOf'] = 'MyClass';
$rule['constructParams'] = ['Foo', 'Bar'];

$dice->addRule('$MyNamedInstance', $rule);


```

`$dice->create('$MyNamedInstance')` will now create a class following the rules applied to both `MyClass` and `$MyNamedInstance` so the instance will be shared.

Previously only the rules applied to the named instance would be used.

To restore the old behaviour, set `inherit` to `false` on the named instance:

```php
$rule = [];
$rule['shared'] = true;

$dice->addRule('MyClass', $rule);

$rule = [];
$rule['instanceOf'] = 'MyClass';
$rule['constructParams'] = ['Foo', 'Bar'];


//Prevent the named instance inheriting rules from the class named in `instanceOf`:
$rule['inherit'] = false;

$dice->addRule('$MyNamedInstance', $rule);

```




29/10/2014
* Based on [Issue #15](https://github.com/TomBZombie/Dice/issues/15), Dice will now only call closures if they are wrapped in \Dice\Instance. **PLEASE NOTE: THIS IS BACKWARDS INCOMPATIBLE **.

Previously Dice ran closures that were passed as substitutions, constructParams and when calling methods:

```php

$rule->substitutions['A'] = function() {
	return new A;
};

$rule->call[] = ['someMethod', function() {
// '2' will be provided as the first argument when someMethod is called
return 2;
}];

$rule->constructParams[] = function() {
	//'abc' will be providedas the first constructor parameter
	return 'abc';
};
```

This behaviour has changed as it makes it impossible to provide a closure as a construct parameter or when calling a method because the closure was always called and executed.

To overcome this, Dice will now only call a closures if they're wrapped in \Dice\Instance:

```php
$rule->substitutions['A'] = ['instance' => function() {
	return new A;
}];

$rule->call[] = ['someMethod', ['instance' => function() {
// '2' will be provided as the first argument when someMethod is called
return 2;
}]]);

$rule->constructParams[] =  ['instance' => function() { {
	//'abc' will be providedas the first constructor parameter
	return 'abc';
}]);
```





04/09/2014
* Pushed PHP5.6 branch live. This is slightly more efficient using PHP5.6 features. For PHP5.4-PHP5.5 please see the relevant branch. This version will be maintained until PHP5.6 is more widespread.


26/08/2014
* Added PHP5.6 branch. Tidied up code by using PHP5.6 features. This will be moved to master when PHP5.6 is released

28/06/2014
* Greatly improved efficienty. Dice is now the fastest Dependency Injection Container for PHP!

06/06/2014
* Added support for cyclic references ( https://github.com/TomBZombie/Dice/issues/7 ). Please note this is poor design but this fix will stop the infinite loop this design creates.

27/03/2014
* Removed assign() method as this duplicated functionality available using $rule->shared
* Removed $callback argument in $dice->create() as the only real use for this feature can be better achieved using $rule->shareInstances
* Tidied up code, removing unused/undocumented features. Dice is now even more lightweight and faster.
* Fixed a bug where when using $rule->call it would use the substitution rules from the constructor on each method called
* Updated [Dice documentation](https://r.je/dice.html) to use shorthand array syntax

01/03/2014
* Added test cases for the Xml Loader and Loader Callback classes
* Added a JSON loader + test case
* Added all test cases to a test suite
* Moved to PHP5.4 array syntax. A PHP5.3 compatible version is now available in the PHP5.3 branch.
* Fixed an issue where using named instances would trigger the autoloader with an invalid class name every time a class was created


28/02/2014
* Added basic namespace support. Documentation update will follow shortly. Also moved the XML loader into its own file, you'll need to include it separately if you're using it.
* Please note: CHANGES ARE NOT BACKWARDS COMPATIBLE. However they are easily fixed by doing the following find/replaces:

```php
	new Dice => new \Dice\Dice
	new DiceInstance => new \Dice\Instance
	new DiceRule => new \Dice\Rule
```
