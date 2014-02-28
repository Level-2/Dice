<?php 
namespace foo;

class A {
	
}

class B {
	public $a;
	
	public function __construct(A $a) {
		$this->a = $a;	
	}
}

class ExtendedA extends A {
	
}