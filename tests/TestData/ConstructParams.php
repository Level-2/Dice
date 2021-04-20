<?php
/* @description Dice - A minimal Dependency Injection Container for PHP *
 * @author Tom Butler tom@r.je *
 * @copyright 2012-2018 Tom Butler <tom@r.je> | https:// r.je/dice.html *
 * @license http:// www.opensource.org/licenses/bsd-license.php BSD License *
 * @version 3.0 */
class MyDirectoryIterator extends DirectoryIterator {

}


class MyDirectoryIterator2 extends DirectoryIterator {
	public function __construct($f) {
		parent::__construct($f);
	}
}

class ParamRequiresArgs {
    public $a;

    public function __construct(D $d, RequiresConstructorArgsA $a) {
        $this->a = $a;
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



trait MyTrait {
	public function foo() {}
}

class MyDirectoryIteratorWithTrait extends DirectoryIterator {
	use MyTrait;
}


class NullScalar {
	public $string;

	public function __construct($string = null) {
		$this->string = $string;
    }
}

class NullScalarNested {
	public $nullScalar;

	public function __construct(NullScalar $nullScalar) {
		$this->nullScalar = $nullScalar;
    }
}



class NB {}

class NC {}

class MethodWithTwoDefaultNullC {
	public $a;
	public $b;
	public function __construct($a = null, NB $b = null) {
		$this->a = $a;
		$this->b = $b;
	}
}

class MethodWithTwoDefaultNullCC {
	public $a;
	public $b;
	public $c;
	public function __construct($a = null, NB $b = null, NC $c = null) {
		$this->a = $a;
		$this->b = $b;
		$this->c = $c;
	}
}


class NullableClassTypeHint {
	public $obj;

	public function __construct(?D $obj) {
		$this->obj = $obj;
	}
}
