<?php
/* @description Dice - A minimal Dependency Injection Container for PHP *
 * @author Tom Butler tom@r.je *
 * @copyright 2012-2018 Tom Butler <tom@r.je> | https:// r.je/dice.html *
 * @license http:// www.opensource.org/licenses/bsd-license.php BSD License *
 * @version 3.0 */
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

		return 'callMe called';
	}
}


class TestCallImmutable {
	public $a;
	public $b;

	public function call1($a) {
		$new = clone $this;
		$new->a = $a;
		return $new;
	}

	public function call2($b) {
		$new = clone $this;
		$new->b = $b;
		return $new;
	}
}

class TestCallVariadic {
    public $data;

    public function callMe(...$data) {
        $this->data = $data;
    }
}