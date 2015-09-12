<?php
/*@description        Dice - A minimal Dependency Injection Container for PHP
* @author             Tom Butler tom@r.je
* @copyright          2012-2015 Tom Butler <tom@r.je>
* @link               http://r.je/dice.html
* @license            http://www.opensource.org/licenses/bsd-license.php  BSD License
* @version            2.0
*/
class SubstitutionsTest extends DiceTest {
	public function testNoMoreAssign() {
		$rule = [];
		$rule['substitutions']['Bar77'] = ['instance' => function() {
			return Baz77::create();
		}];
		
		$this->dice->addRule('Foo77', $rule);
		
		$foo = $this->dice->create('Foo77');
		
		$this->assertInstanceOf('Bar77', $foo->bar);
		$this->assertEquals('Z', $foo->bar->a);
	}

	public function testNullSubstitution() {
		$rule = [];
		$rule['substitutions']['B'] = null;
		$this->dice->addRule('MethodWithDefaultNull', $rule);
		$obj = $this->dice->create('MethodWithDefaultNull');
		$this->assertNull($obj->b);
	}		
	
	public function testSubstitutionText() {
		$rule = [];
		$rule['substitutions']['B'] = ['instance' => 'ExtendedB'];
		$this->dice->addRule('A', $rule);
		
		$a = $this->dice->create('A');
		
		$this->assertInstanceOf('ExtendedB', $a->b);
	}

	public function testSubstitutionTextMixedCase() {
		$rule = [];
		$rule['substitutions']['B'] = ['instance' => 'exTenDedb'];
		$this->dice->addRule('A', $rule);
	
		$a = $this->dice->create('A');
	
		$this->assertInstanceOf('ExtendedB', $a->b);
	}

	public function testSubstitutionCallback() {
		$rule = [];
		$injection = $this->dice;
		$rule['substitutions']['B'] = ['instance' => function() use ($injection) {
			return $injection->create('ExtendedB');
		}];
		
		$this->dice->addRule('A', $rule);
		
		$a = $this->dice->create('A');
		
		$this->assertInstanceOf('ExtendedB', $a->b);
	}


	public function testSubstitutionObject() {
		$rule = [];

		$rule['substitutions']['B'] = $this->dice->create('ExtendedB');
				
		$this->dice->addRule('A', $rule);
		
		$a = $this->dice->create('A');
		$this->assertInstanceOf('ExtendedB', $a->b);
	}
	
	public function testSubstitutionString() {
		$rule = [];
	
		$rule['substitutions']['B'] = ['instance' => 'ExtendedB'];
	
		$this->dice->addRule('A', $rule);
	
		$a = $this->dice->create('A');
		$this->assertInstanceOf('ExtendedB', $a->b);
	}
	
	
	public function testSubFromString() {
		$rule = [
			'substitutions' => ['Bar' => 'Baz']
		];
		$this->dice->addRule('*', $rule);

		$this->dice->create('Foo');

	}

}


class Foo {
	public $bar;
	public function __construct(Bar $bar) {
		$this->bar = $bar;
	}
}

interface Bar {

}

class Baz implements Bar {

}