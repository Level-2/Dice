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

Dice 3.0 is compatible with 7.0 and up. Earlier versions of Dice support earlier versions of PHP. For PHP5.6 it is recommended that you use the 2.0 branch.


Performance
-----------

Dice uses reflection which is often wrongly labelled "slow". Reflection is considerably faster than loading and parsing a configuration file. There are a set of benchmarks [here](https://rawgit.com/TomBZombie/php-dependency-injection-benchmarks/master/test1-5_results.html) and [here](https://rawgit.com/TomBZombie/php-dependency-injection-benchmarks/master/test6_results.html) (To download the benchmark tool yourself see [this repository](https://github.com/TomBZombie/php-dependency-injection-benchmarks)) and Dice is faster than the others in most cases. 

In the real world test ([test 6](https://rawgit.com/TomBZombie/php-dependency-injection-benchmarks/master/test6_results.html)) Dice is neck-and-neck with Pimple (which requires writing an awful lot of configuration code) and although Symfony\DependencyInjection is faster at creating objects, it has a larger overhead and you need to create over 500 objects on each page load until it becomes faster than Dice. The same is true of Phalcon, the overhead of loading the Phalcon extension means that unless you're creating well over a thousand objects per HTTP request, the overhead is not worthwhile.


Credits
------------

Originally developed by Tom Butler (@TomBZombie), with many thanks to daniel-meister (@daniel-meister), Garrett W. (@garrettw), maxwilms (@maxwilms) for bug fixes, suggestions and improvements.


Updates
------------

30/04/2016

### 3.0 Release

3.0 (development version) has been released. Please note there are several new features and backwards incompatible changes.

#### 1) `addRule` has become `addRules` 

What would have previously been expressed as:

```php
$dice->addRule('Foo', ['shared' => true]);
```

is now:

```php
$dice->addRules(['Foo' => ['shared' => true]]);
```

This allows for adding multiple rules in a single call:

```php
$dice->addRules([
		'Foo' => ['shared' => true],
		'Bar' => ['instanceOf' => 'Baz']
]); 
```

#### 2) The `Jsonloader` class has been removed

Dice now fully supports JSON style configuration without the need for an additional class. To load a JSON configuration file you can now use:#

```php
$json = json_decode(file_get_contents('rules.json'));
$dice->addRules($json);
```


#### 3) `['instance' => '\Foo\Bar']` has been replaced

Using the key `instance` meant that it was impossible to pass an array that contained the key `instance` to a class instantiated by Dice as Dice would always try to create the instance. You should now use:

In native PHP

```php
[\Dice::INSTANCE => '\Foo\Bar']

```

Or in a JSON configuration file:

```json
	{"\\Dice::INSTANCE": "\\Foo\\Bar"}
```

#### 4) Support for reading constants from JSON

A limitation of JSON configuration is that it cannot read any values from PHP code. A common problem encountered with the JSON format is that although the following works using native PHP, the same cannot be expressed in JSON

```php

$dice->addRules(['PDO' => [
					'shared' => true,
					'constructParams'  => ['mysql:host=127.0.0.1;dbname=mydb', 'username', 'password'],
					'call' => [
								['setAttribute', [PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ]]

   					 ]
				]
		]);

```

This can't be expressed using JSON without providing the integers represented by `PDO::ATTR_DEFAULT_FETCH_MODE` and `PDO::FETCH_OBJ`.

As of 3.0 Dice allows reading PHP constants in the JSON format:

```json
{
	"PDO": {
		"shared": "true",
		"constructParams": ["mysql:host=127.0.0.1;dbname=mydb", "username", "password"],
		"call": [ [
					"setAttribute", [
							{"\\Dice::CONSTANT": "PDO::ATTR_DEFAULT_FETCH_MODE"},
							{"\\Dice::CONSTANT": "PDO::FETCH_OBJ"}
				  ]
				]
		]
	}
}

```

Although slightly more verbose, it is considerably more readable than providing the integer values of the constants.

#### 5) Read globals

In the same way as reading constants. Dice 3.0 also supports reading from the `$GLOBALS` array. This can be useful when a class requires `$_GET` or `$_POST`.

The below will pass `$_POST` and `$_GET` as constructor arguments:

```json
{
	"\\Level2\\Core\\Request": {
		"constructParams": [{"\\Dice::GLOBAL": "_POST"}, {"\\Dice::GLOBAL": "_GET"}]
	}
}

```



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
$rule->substitutions['A'] = new \Dice\Instance(function() {
	return new A;
});

$rule->call[] = ['someMethod', new \Dice\Instance(function() {
// '2' will be provided as the first argument when someMethod is called
return 2;
}]);

$rule->constructParams[] = new \Dice\Instance(function() {
	//'abc' will be providedas the first constructor parameter
	return 'abc';
});
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
