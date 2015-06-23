<?php

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
