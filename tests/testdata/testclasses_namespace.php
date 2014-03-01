<?php 
/* @description 		Dice - A minimal Dependency Injection Container for PHP
 * @author				Tom Butler tom@r.je
* @copyright			2012-2014 Tom Butler <tom@r.je>
* @link				http://r.je/dice.html
* @license				http://www.opensource.org/licenses/bsd-license.php  BSD License
* @version				1.1
*/
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