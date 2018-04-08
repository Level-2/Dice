<?php
/* @description Dice - A minimal Dependency Injection Container for PHP *
 * @author Tom Butler tom@r.je *
 * @copyright 2012-2018 Tom Butler <tom@r.je> | https:// r.je/dice.html *
 * @license http:// www.opensource.org/licenses/bsd-license.php BSD License *
 * @version 3.0 */

class ConsumeArgsTop {
    public $s;
    public $a;

    public function __construct(ConsumeArgsSub $a, $s) {
        $this->a = $a;
        $this->s = $s;
    }
}
class ConsumeArgsSub {
    public $s;

    public function __construct($s) {
        $this->s = $s;
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

class A4 {
	public $m1;
	public $m2;
	public function __construct(M1 $m1, M2 $m2) {
		$this->m1 = $m1;
		$this->m2 = $m2;
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

//From: https://github.com/TomBZombie/Dice/issues/62#issuecomment-112370319
class ScalarConstructors {
	public $string;
	public $null;

  public function __construct($string, $null) {
    $this->string = $string;
    $this->null = $null;
  }
}