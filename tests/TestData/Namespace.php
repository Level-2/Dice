<?php
namespace Foo {

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


class C {
	public $a;
		
	public function __construct(\Bar\A $a) {
		$this->a = $a;
	}
}

}

namespace Bar {
	class A {
		
	}	
	
	class B {
		
	}
}