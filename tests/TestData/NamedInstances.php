<?php

class Z {
	public $y1;
	public $y2;
	public function __construct(Y $y1, Y $y2) {
		$this->y1 = $y1;
		$this->y2 = $y2;
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


class Y {
	public $name;
	public function __construct($name) {
		$this->name = $name;
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



