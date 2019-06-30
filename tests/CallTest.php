<?php
/* @description Dice - A minimal Dependency Injection Container for PHP *
 * @author Tom Butler tom@r.je *
 * @copyright 2012-2018 Tom Butler <tom@r.je> | https:// r.je/dice.html *
 * @license http:// www.opensource.org/licenses/bsd-license.php BSD License *
 * @version 3.0 */
class CallTest extends DiceTest {
	public function testCall() {
		$rule = [];
		$rule['call'][] = array('callMe', array());
		$dice = $this->dice->addRule('TestCall', $rule);
		$object = $dice->create('TestCall');
		$this->assertTrue($object->isCalled);
	}

	public function testCallWithParameters() {
		$rule = [];
		$rule['call'][] = array('callMe', array('one', 'two'));
		$dice = $this->dice->addRule('TestCall2', $rule);
		$object = $dice->create('TestCall2');
		$this->assertEquals('one', $object->foo);
		$this->assertEquals('two', $object->bar);
	}

	public function testCallWithInstance() {
		$rule = [];
		$rule['call'][] = array('callMe', array([\Dice\Dice::INSTANCE => 'A']));
		$dice = $this->dice->addRule('TestCall3', $rule);
		$object = $dice->create('TestCall3');

		$this->assertInstanceOf('a', $object->a);

	}

	public function testCallAutoWireInstance() {
		$rule = [];
		$rule['call'][] = array('callMe', []);
		$dice = $this->dice->addRule('TestCall3', $rule);
		$object = $dice->create('TestCall3');

		$this->assertInstanceOf('a', $object->a);
	}

	public function testCallReturnValue() {
		$rule = [];

		$returnValue = null;

		$rule['call'][] = array('callMe', [], function($return) use (&$returnValue) {
			$returnValue = $return;
		});


		$dice = $this->dice->addRule('TestCall3', $rule);
		$object = $dice->create('TestCall3');

		$this->assertInstanceOf('a', $object->a);
		$this->assertEquals('callMe called', $returnValue);
	}


	public function testCallChain() {
		$rules = [
			'TestCallImmutable' => [
				'call' => [
					['call1', ['foo'], \Dice\Dice::CHAIN_CALL],
					['call2', ['bar'], \Dice\Dice::CHAIN_CALL]
				]
			]
		];

		$dice = $this->dice->addRules($rules);

		$object = $dice->create('TestCallImmutable');

		$this->assertEquals('foo', $object->a);
		$this->assertEquals('bar', $object->b);
	}

	public function testCallShareVariadic() {
	    // Shared params should not be passed to variadic call

        $rules = [
            'TestCallVariadic' => [
                'call' => [
                    ['callMe', ['test1']]
                ]
            ]
        ];

        $dice = $this->dice->addRules($rules);
        $object = $dice->create('TestCallVariadic', [], [new F()]);

        $this->assertEquals(['test1'], $object->data);
    }
}