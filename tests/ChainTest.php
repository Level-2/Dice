<?php
/* @description Dice - A minimal Dependency Injection Container for PHP *
 * @author Tom Butler tom@r.je *
 * @copyright 2012-2018 Tom Butler <tom@r.je> | https:// r.je/dice.html *
 * @license http:// www.opensource.org/licenses/bsd-license.php BSD License *
 * @version 3.0 */
class ChainTest extends DiceTest {
	public function testChainCall() {
		$dice = $this->dice->addRules([
				'$someClass' => [
					'instanceOf' => 'Factory',
					'call' => [
						['get', [], \Dice\Dice::CHAIN_CALL]
					]
				]
		]);

		$obj = $dice->create('$someClass');

		$this->assertInstanceOf('FactoryDependency', $obj);

	}

    public function testMultipleChainCall() {
        $dice = $this->dice->addRules([
            '$someClass' => [
                'instanceOf' => 'Factory',
                'call' => [
                    ['get', [], \Dice\Dice::CHAIN_CALL],
                    ['getBar', [], \Dice\Dice::CHAIN_CALL]
                ]
            ]
        ]);

        $obj = $dice->create('$someClass');

        $this->assertEquals('bar', $obj);

    }

	public function testChainCallShared() {
		$dice = $this->dice->addRules([
				'$someClass' => [
					'shared' => true,
					'instanceOf' => 'Factory',
					'call' => [
						['get', [], \Dice\Dice::CHAIN_CALL]
					]
				]
		]);

		$obj = $dice->create('$someClass');

		$this->assertInstanceOf('FactoryDependency', $obj);
	}


	public function testChainCallInject() {
		$dice = $this->dice->addRules([
				'FactoryDependency' => [
					'instanceOf' => 'Factory',
					'call' => [
						['get', [], \Dice\Dice::CHAIN_CALL]
					]
				]
		]);

		$obj = $dice->create('RequiresFactoryDependecy');

		$this->assertInstanceOf('FactoryDependency', $obj->dep);
	}

	public function testChainCallInjectShared() {
		$dice = $this->dice->addRules([
				'FactoryDependency' => [
					'shared' => true,
					'instanceOf' => 'Factory',
					'call' => [
						['get', [], \Dice\Dice::CHAIN_CALL]
					]
				]
		]);


		$dice->create('FactoryDependency');

		$obj = $dice->create('RequiresFactoryDependecy');

		$this->assertInstanceOf('FactoryDependency', $obj->dep);

		$obj2 = $dice->create('RequiresFactoryDependecy');


		$this->assertNotSame($obj, $obj2);
		$this->assertSame($obj->dep, $obj2->dep);
	}

}


class Factory {
	public function get() {
		return new FactoryDependency;
	}
}

class FactoryDependency {
    public function getBar() {
        return 'bar';
    }
}

class RequiresFactoryDependecy {
	public $dep;

	public function __construct(FactoryDependency $dep) {
		$this->dep = $dep;
	}
}