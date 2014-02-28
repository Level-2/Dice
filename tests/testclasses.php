
<?php 
/* @description 		Dice - A minimal Dependency Injection Container for PHP
 * @author				Tom Butler tom@r.je
* @copyright			2012-2014 Tom Butler <tom@r.je>
* @link				http://r.je/dice.html
* @license				http://www.opensource.org/licenses/bsd-license.php  BSD License
* @version				1.1
*/
class Shared {
	public $uniq;
	
	public function __construct() {
		$this->uniq = uniqid();
	}
}

class TestSharedInstancesTop {
	public $share1;
	public $share2;
	
	public function __construct(SharedInstanceTest1 $share1, SharedInstanceTest2 $share2) {
		$this->share1 = $share1;
		$this->share2 = $share2;
	}	
}


class SharedInstanceTest1 {
	public $shared;
	
	public function __construct(Shared $shared) {
		$this->shared = $shared;		
	}
}


class SharedInstanceTest2 {
	public $shared;

	public function __construct(Shared $shared) {
		$this->shared = $shared;
	}
}

class TestCall {
	public $isCalled = false;
	
	public function callMe() {
		$this->isCalled = true;
	}
}

class TestCall2 {
	public $foo;
	public $bar;

	public function callMe($foo, $bar) {
		$this->foo = $foo;
		$this->bar = $bar;
	}
}


class TestCall3 {
	public $a;

	public function callMe(A $a) {
		$this->a = $a;
	}
}

class HasTwoSameDependencies {
	public $y2a;
	public $y2b;
	
	public function __construct(Y2 $y2a, Y2 $y2b) {
		$this->y2a = $y2a;
		$this->y2b = $y2b;
	}
}

class Y1 {
	public $y2;
	
	public function __construct(Y2 $y2) {
		$this->y2 = $y2;
	}
}


class Y2 {
	public $name; 
	
	public function __construct($name) {
		$this->name = $name;
	}
}

class Y3 extends Y2 {
	
}
class Z {
	public $y1;
	public $y2;
	public function __construct(Y $y1, Y $y2) {
		$this->y1 = $y1;
		$this->y2 = $y2;
	}
}

class Y {
	public $name;
	public function __construct($name) {
		$this->name = $name;
	}
}

class BestMatch {
	public $a;
	public $string;
	public $b;
	
	public function __construct($string, A $a, B $b) {
		$this->a = $a;
		$this->string = $string;
		$this->b = $b;
	}	
}


//Because the DIC's job is to create other classes, some dummy class definitions are required.
//Mocks cannot be used because the DIC relies on class definitions

class MyObj {
	private $foo;

	public function setFoo($foo) {
		$this->foo = $foo;
	}

	public function getFoo() {
		return $this->foo;
	}
}


class A2 {
	public $b;
	public $c;
	public $foo;

	public function __construct(B $b, C $c, $foo) {
		$this->b = $b;
		$this->foo = $foo;
		$this->c = $c;
	}
}


class A3 {
	public $b;
	public $c;
	public $foo;

	public function __construct(C $c, $foo, B $b) {
		$this->b = $b;
		$this->foo = $foo;
		$this->c = $c;
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

class RequiresConstructorArgsB {
	public $a;
	public $foo;
	public $bar;

	public function __construct(A $a, $foo, $bar) {
		$this->a = $a;
		$this->foo = $foo;
		$this->bar = $bar;
	}
}

interface interfaceTest {}

class InterfaceTestClass implements interfaceTest {

}
