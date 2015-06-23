<?php
/*@description        Dice - A minimal Dependency Injection Container for PHP
* @author             Tom Butler tom@r.je
* @copyright          2012-2015 Tom Butler <tom@r.je>
* @link               http://r.je/dice.html
* @license            http://www.opensource.org/licenses/bsd-license.php  BSD License
* @version            2.0
*/
class CallTest extends DiceTest {
	public function testCall() {
		$rule = [];
		$rule['call'][] = array('callMe', array());
		$this->dice->addRule('TestCall', $rule);
		$object = $this->dice->create('TestCall');
		$this->assertTrue($object->isCalled);
	}
	
	public function testCallWithParameters() {
		$rule = [];
		$rule['call'][] = array('callMe', array('one', 'two'));
		$this->dice->addRule('TestCall2', $rule);
		$object = $this->dice->create('TestCall2');
		$this->assertEquals('one', $object->foo);
		$this->assertEquals('two', $object->bar);
	}
	
	public function testCallWithInstance() {
		$rule = [];
		$rule['call'][] = array('callMe', array(['instance' => 'A']));
		$this->dice->addRule('TestCall3', $rule);
		$object = $this->dice->create('TestCall3');
		
		$this->assertInstanceOf('a', $object->a);
	
	}
}