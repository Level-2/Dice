<?php
/* @description     Dice - A minimal Dependency Injection Container for PHP         *  
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2012-2015 Tom Butler <tom@r.je> | http://r.je/dice.html         *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.3.2                                                           */
require_once 'Dice.php';
require_once 'Loader/Callback.php';

class TestConfig {
	public $dbServer = '127.0.0.1';
	
	public function getFoo() {
		return 'abc';
	}
	
	public function getBar($bar) {
		return $bar;
	}
	
	public function getBaz($a, $b, $c) {
		return $a + $b + $c;
	}
	
	public function getObj() {
		$class = new stdClass;
		$class->foo = 'bar';
		return $class;
	}
}

class CallbackTest extends PHPUnit_Framework_TestCase {
	
	private $dice;
	
	protected function setUp() {
		parent::setUp ();
		
		$this->dice = $this->getMock('\\Dice\\Dice', array('create'));
		$this->dice->expects($this->once())->method('create')->with($this->equalTo('TestConfig'))->will($this->returnValue(new TestConfig));
	}
	

	protected function tearDown() {
		parent::tearDown ();
		$this->dice = null;
	}
	

	public function testProperty() {	
		$callback = new \Dice\Loader\Callback('TestConfig::dbServer');
		$result = $callback->run($this->dice);
		$this->assertEquals('127.0.0.1', $result);		
	}
	
	public function testMethod() {		
		$callback = new \Dice\Loader\Callback('TestConfig::getFoo()');
		$result = $callback->run($this->dice);
		$this->assertEquals('abc', $result);
	}
	
	public function testMethodArg() {
		$callback = new \Dice\Loader\Callback('TestConfig::getBar(foobar)');
		$result = $callback->run($this->dice);
		$this->assertEquals('foobar', $result);
	}
	
	
	public function testMethodArgs() {
		$callback = new \Dice\Loader\Callback('TestConfig::getBaz(10,20,30)');
		$result = $callback->run($this->dice);
		$this->assertEquals(60, $result);
	}
	
	public function testDeepLookup() {	
		$callback = new \Dice\Loader\Callback('TestConfig::getObj()::foo');
		$result = $callback->run($this->dice);
		$this->assertEquals('bar', $result);
	}
	
	
}

