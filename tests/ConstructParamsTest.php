<?php
/*@description        Dice - A minimal Dependency Injection Container for PHP
* @author             Tom Butler tom@r.je
* @copyright          2012-2015 Tom Butler <tom@r.je>
* @link               http://r.je/dice.html
* @license            http://www.opensource.org/licenses/bsd-license.php  BSD License
* @version            2.0
*/
class ConstructParamsTest extends DiceTest {

	public function testConstructParams() {
		$rule = [];
		$rule['constructParams'] = array('foo', 'bar');
		$this->dice->addRules(['RequiresConstructorArgsA' =>  $rule]);
		
		$obj = $this->dice->create('RequiresConstructorArgsA');
		
		$this->assertEquals($obj->foo, 'foo');
		$this->assertEquals($obj->bar, 'bar');
	}


	public function testInternalClass() {
		$rule = [];
		$rule['constructParams'][] = '.';
		
		$this->dice->addRules(['DirectoryIterator' =>  $rule]);
		
		$dir = $this->dice->create('DirectoryIterator');
		
		$this->assertInstanceOf('DirectoryIterator', $dir);
	}
	
	public function testInternalClassExtended() {
		$rule = [];
		$rule['constructParams'][] = '.';
	
		$this->dice->addRules(['MyDirectoryIterator' =>  $rule]);
	
		$dir = $this->dice->create('MyDirectoryIterator');
	
		$this->assertInstanceOf('MyDirectoryIterator', $dir);
	}
	
	
	public function testInternalClassExtendedConstructor() {
		$rule = [];
		$rule['constructParams'][] = '.';
	
		$this->dice->addRules(['MyDirectoryIterator2' =>  $rule]);
	
		$dir = $this->dice->create('MyDirectoryIterator2');
	
		$this->assertInstanceOf('MyDirectoryIterator2', $dir);
	}

	public function testDefaultNullAssigned() {
		$rule = [];
		$rule['constructParams'] = [ ['instance' => 'A'], null];
		$this->dice->addRules(['MethodWithDefaultNull' =>  $rule]);
		$obj = $this->dice->create('MethodWithDefaultNull');
		$this->assertNull($obj->b);
	}

	public function testConstructParamsNested() {
		$rule = [];
		$rule['constructParams'] = array('foo', 'bar');
		$this->dice->addRules(['RequiresConstructorArgsA' =>  $rule]);

		$rule = [];
		$rule['shareInstances'] = array('D');
		$this->dice->addRules(['ParamRequiresArgs' =>  $rule]);
		
		$obj = $this->dice->create('ParamRequiresArgs');
		
		$this->assertEquals($obj->a->foo, 'foo');
		$this->assertEquals($obj->a->bar, 'bar');
	}

	
	public function testConstructParamsMixed() {
		$rule = [];
		$rule['constructParams'] = array('foo', 'bar');
		$this->dice->addRules(['RequiresConstructorArgsB' =>  $rule]);
		
		$obj = $this->dice->create('RequiresConstructorArgsB');
		
		$this->assertEquals($obj->foo, 'foo');
		$this->assertEquals($obj->bar, 'bar');
		$this->assertInstanceOf('A', $obj->a);
	}


	public function testSharedClassWithTraitExtendsInternalClass()	{
		$rule = [];
		$rule['constructParams'] = ['.'];

		$this->dice->addRules(['MyDirectoryIteratorWithTrait' =>  $rule]);

		$dir = $this->dice->create('MyDirectoryIteratorWithTrait');

		$this->assertInstanceOf('MyDirectoryIteratorWithTrait', $dir);
	}

	public function testConstructParamsPrecedence() {
		$rule = [];
		$rule['constructParams'] = ['A', 'B'];
		$this->dice->addRules(['RequiresConstructorArgsA' =>  $rule]);

		$a1 = $this->dice->create('RequiresConstructorArgsA');
		$this->assertEquals('A', $a1->foo);
		$this->assertEquals('B', $a1->bar);

		$a2 = $this->dice->create('RequiresConstructorArgsA', ['C', 'D']);
		$this->assertEquals('C', $a2->foo);
		$this->assertEquals('D', $a2->bar);
	}

	public function testNullScalar() {
		$rule = [];
		$rule['constructParams'] = [null];
		$this->dice->addRules(['NullScalar' =>  $rule]);

		$obj = $this->dice->create('NullScalar');
		$this->assertEquals(null, $obj->string);
	}

	public function testNullScalarNested() {
		$rule = [];
		$rule['constructParams'] = [null];
		$this->dice->addRules(['NullScalar' =>  $rule]);

		$obj = $this->dice->create('NullScalarNested');
		$this->assertEquals(null, $obj->nullScalar->string);
	}

}