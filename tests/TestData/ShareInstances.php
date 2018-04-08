<?php
/* @description Dice - A minimal Dependency Injection Container for PHP *
 * @author Tom Butler tom@r.je *
 * @copyright 2012-2018 Tom Butler <tom@r.je> | https:// r.je/dice.html *
 * @license http:// www.opensource.org/licenses/bsd-license.php BSD License *
 * @version 3.0 */
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



class M1 {
	public $f;
	public function __construct(F $f) {
		$this->f = $f;
	}
}

class M2 {
	public $e;
	public function __construct(E $e) {
		$this->e = $e;
	}
}

class Foo77 {
	public $bar;

	public function __construct(Bar77 $bar) {
		$this->bar = $bar;
	}
}

class Bar77 {
	public $a;

	public function __construct($a) {
		$this->a = $a;
	}
}


class Baz77 {
	public static function create() {
		return new Bar77('Z');
	}
}

class Shared {
	public $uniq;

	public function __construct() {
		$this->uniq = uniqid();
	}
}

