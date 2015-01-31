<?php

require_once 'dice.php';
require_once 'loader/callback.php';

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

