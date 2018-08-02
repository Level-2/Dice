<?php
/* @description Dice - A minimal Dependency Injection Container for PHP *
 * @author Tom Butler tom@r.je *
 * @copyright 2012-2018 Tom Butler <tom@r.je> | https:// r.je/dice.html *
 * @license http:// www.opensource.org/licenses/bsd-license.php BSD License *
 * @version 3.0 */
class SubstitutionsTest extends DiceTest {
	public function testNoMoreAssign() {
		$rule = [];
		$rule['substitutions']['Bar77'] = [\Dice\Dice::INSTANCE => function() {
			return Baz77::create();
		}];

		$dice = $this->dice->addRule('Foo77', $rule);

		$foo = $dice->create('Foo77');

		$this->assertInstanceOf('Bar77', $foo->bar);
		$this->assertEquals('Z', $foo->bar->a);
	}

	public function testNullSubstitution() {
		$rule = [];
		$rule['substitutions']['B'] = null;
		$dice = $this->dice->addRule('MethodWithDefaultNull', $rule);
		$obj = $dice->create('MethodWithDefaultNull');
		$this->assertNull($obj->b);
	}

	public function testSubstitutionText() {
		$rule = [];
		$rule['substitutions']['B'] = [\Dice\Dice::INSTANCE => 'ExtendedB'];
		$dice = $this->dice->addRule('A', $rule);

		$a = $dice->create('A');

		$this->assertInstanceOf('ExtendedB', $a->b);
	}

	public function testSubstitutionTextMixedCase() {
		$rule = [];
		$rule['substitutions']['B'] = [\Dice\Dice::INSTANCE => 'exTenDedb'];
		$dice = $this->dice->addRule('A', $rule);

		$a = $dice->create('A');

		$this->assertInstanceOf('ExtendedB', $a->b);
	}

	public function testSubstitutionCallback() {
		$rule = [];
		$injection = $this->dice;
		$rule['substitutions']['B'] = [\Dice\Dice::INSTANCE => function() use ($injection) {
			return $injection->create('ExtendedB');
		}];

		$dice = $this->dice->addRule('A', $rule);

		$a = $dice->create('A');

		$this->assertInstanceOf('ExtendedB', $a->b);
	}


	public function testSubstitutionObject() {
		$rule = [];

		$rule['substitutions']['B'] = $this->dice->create('ExtendedB');

		$dice = $this->dice->addRule('A', $rule);

		$a = $dice->create('A');
		$this->assertInstanceOf('ExtendedB', $a->b);
	}

	public function testSubstitutionString() {
		$rule = [];

		$rule['substitutions']['B'] = [\Dice\Dice::INSTANCE => 'ExtendedB'];

		$dice = $this->dice->addRule('A', $rule);

		$a = $dice->create('A');
		$this->assertInstanceOf('ExtendedB', $a->b);
	}


	public function testSubFromString() {
		$rule = [
			'substitutions' => ['Bar' => 'Baz']
		];
		$dice = $this->dice->addRule('*', $rule);

		$obj = $dice->create('Foo');

		$this->assertInstanceOf('Baz', $obj->bar);

	}

	public function testSubstitutionWithFuncCall() {
		$rule = [];

		$rule['substitutions']['Bar'] = [\Dice\Dice::INSTANCE => ['Foo2', 'bar']];

		$dice = $this->dice->addRule('Foo', $rule);

		$a = $dice->create('Foo');
		$this->assertInstanceOf('Baz', $a->bar);
	}
}


class Foo {
	public $bar;
	public function __construct(Bar $bar) {
		$this->bar = $bar;
	}
}

class Foo2 {
	public function bar() {
		return new Baz;
	}
}

interface Bar {

}

class Baz implements Bar {

}
