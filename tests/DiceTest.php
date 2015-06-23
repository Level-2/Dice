<?php
/*@description        Dice - A minimal Dependency Injection Container for PHP
* @author             Tom Butler tom@r.je
* @copyright          2012-2015 Tom Butler <tom@r.je>
* @link               http://r.je/dice.html
* @license            http://www.opensource.org/licenses/bsd-license.php  BSD License
* @version            2.0
*/
abstract class DiceTest extends PHPUnit_Framework_TestCase {
	protected $dice;

	public function __construct() {
		parent::__construct();
	//	spl_autoload_register(array($this, 'autoload'));

		//Load the test classes for this test
		$name = str_replace('Test', '', get_class($this));
		require_once 'tests/TestData/Basic.php';
		require_once 'tests/TestData/' . $name . '.php';
	}
	
	public function autoload($class) {
		//If Dice Triggers the autoloader the test fails
		//This generally means something invalid has been passed to 
		//a method such as is_subclass_of or dice is trying to construct
		//an object from something it shouldn't.
		$this->fail('Autoload triggered: ' . $class);
	}
	
	protected function setUp() {
		parent::setUp ();
		$this->dice = new \Dice\Dice();	
	}

	protected function tearDown() {
		$this->dice = null;		
		parent::tearDown ();
	}
}