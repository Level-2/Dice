<?php
/* @description Dice - A minimal Dependency Injection Container for PHP *
 * @author Tom Butler tom@r.je *
 * @copyright 2012-2018 Tom Butler <tom@r.je> | https:// r.je/dice.html *
 * @license http:// www.opensource.org/licenses/bsd-license.php BSD License *
 * @version 3.0 */
class NoConstructor {
	public $a = 'b';
}

class CyclicA {
	public $b;

	public function __construct(CyclicB $b) {
		$this->b = $b;
	}
}

class CyclicB {
	public $a;

	public function __construct(CyclicA $a) {
		$this->a = $a;
	}
}


class A {
	public $b;

	public function __construct(B $b) {
		$this->b = $b;
	}
}

class B {
	public $c;

	public function __construct(C $c) {
		$this->c = $c;
	}
}

class ExtendedB extends B {

}

class C {
	public $d;
	public $e;

	public function __construct(D $d, E $e) {
		$this->d = $d;
		$this->e = $e;
	}
}


class D {

}

class E {
	public $f;
	public function __construct(F $f) {
		$this->f = $f;
	}
}

class F {}

class RequiresConstructorArgsA {
	public $foo;
	public $bar;

	public function __construct($foo, $bar) {
		$this->foo = $foo;
		$this->bar = $bar;
	}
}

class MyObj {
	private $foo;

	public function setFoo($foo) {
		$this->foo = $foo;
	}

	public function getFoo() {
		return $this->foo;
	}
}


class MethodWithDefaultValue {
	public $a;
	public $foo;

	public function __construct(A $a, $foo = 'bar') {
		$this->a = $a;
		$this->foo = $foo;
	}
}

class MethodWithDefaultNull {
	public $a;
	public $b;
	public function __construct(A $a, B $b = null) {
		$this->a = $a;
		$this->b = $b;
	}
}


interface interfaceTest {}

class InterfaceTestClass implements interfaceTest {

}


class ParentClass {
}
class Child extends ParentClass {
}

class OptionalInterface {
	public $obj;

	public function __construct(InterfaceTest $obj = null) {
		$this->obj = $obj;
	}
}


class ScalarTypeHint {
	public function __construct(string $a = null) {

	}
}

class CheckConstructorArgs {
	public $arg1;

	public function __construct($arg1) {
		$this->arg1 = $arg1;
	}
}


class someclass {}

class someotherclass {
	public $obj;
	public function __construct(someclass $obj){
	    $this->obj = $obj;
	}
}