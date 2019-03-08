<?php
/* @description Dice - A minimal Dependency Injection Container for PHP *
 * @author Tom Butler tom@r.je *
 * @copyright 2012-2018 Tom Butler <tom@r.je> | https:// r.je/dice.html *
 * @license http:// www.opensource.org/licenses/bsd-license.php BSD License *
 * @version 3.0 */
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
