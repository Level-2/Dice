<?php
/* @description Dice - A minimal Dependency Injection Container for PHP *
 * @author Tom Butler tom@r.je *
 * @copyright 2012-2018 Tom Butler <tom@r.je> | https:// r.je/dice.html *
 * @license http:// www.opensource.org/licenses/bsd-license.php BSD License *
 * @version 3.0 */
class ConstructParamsTest extends DiceTest {

	public function testConstructParams() {
		$rule = [];
		$rule['constructParams'] = array('foo', 'bar');
		$dice = $this->dice->addRule('RequiresConstructorArgsA', $rule);

		$obj = $dice->create('RequiresConstructorArgsA');

		$this->assertEquals($obj->foo, 'foo');
		$this->assertEquals($obj->bar, 'bar');
	}


	public function testInternalClass() {
		$rule = [];
		$rule['constructParams'][] = '.';

		$dice = $this->dice->addRule('DirectoryIterator', $rule);

		$dir = $dice->create('DirectoryIterator');

		$this->assertInstanceOf('DirectoryIterator', $dir);
	}

	public function testInternalClassExtended() {
		$rule = [];
		$rule['constructParams'][] = '.';

		$dice = $this->dice->addRule('MyDirectoryIterator', $rule);

		$dir = $dice->create('MyDirectoryIterator');

		$this->assertInstanceOf('MyDirectoryIterator', $dir);
	}


	public function testInternalClassExtendedConstructor() {
		$rule = [];
		$rule['constructParams'][] = '.';

		$dice = $this->dice->addRule('MyDirectoryIterator2', $rule);

		$dir = $dice->create('MyDirectoryIterator2');

		$this->assertInstanceOf('MyDirectoryIterator2', $dir);
	}

	public function testDefaultNullAssigned() {
		$rule = [];
		$rule['constructParams'] = [ [\Dice\Dice::INSTANCE => 'A'], null];
		$dice = $this->dice->addRule('MethodWithDefaultNull', $rule);
		$obj = $dice->create('MethodWithDefaultNull');
		$this->assertNull($obj->b);
	}

	public function testConstructParamsNested() {
		$rule = [];
		$rule['constructParams'] = array('foo', 'bar');
		$dice = $this->dice->addRule('RequiresConstructorArgsA', $rule);

		$rule = [];
		$rule['shareInstances'] = array('D');
		$dice = $dice->addRule('ParamRequiresArgs', $rule);

		$obj = $dice->create('ParamRequiresArgs');

		$this->assertEquals($obj->a->foo, 'foo');
		$this->assertEquals($obj->a->bar, 'bar');
	}


	public function testConstructParamsMixed() {
		$rule = [];
		$rule['constructParams'] = array('foo', 'bar');
		$dice = $this->dice->addRule('RequiresConstructorArgsB', $rule);

		$obj = $dice->create('RequiresConstructorArgsB');

		$this->assertEquals($obj->foo, 'foo');
		$this->assertEquals($obj->bar, 'bar');
		$this->assertInstanceOf('A', $obj->a);
	}


	public function testSharedClassWithTraitExtendsInternalClass()	{
		$rule = [];
		$rule['constructParams'] = ['.'];

		$dice = $this->dice->addRule('MyDirectoryIteratorWithTrait', $rule);

		$dir = $dice->create('MyDirectoryIteratorWithTrait');

		$this->assertInstanceOf('MyDirectoryIteratorWithTrait', $dir);
	}

	public function testConstructParamsPrecedence() {
		$rule = [];
		$rule['constructParams'] = ['A', 'B'];
		$dice = $this->dice->addRule('RequiresConstructorArgsA', $rule);

		$a1 = $dice->create('RequiresConstructorArgsA');
		$this->assertEquals('A', $a1->foo);
		$this->assertEquals('B', $a1->bar);

		$a2 = $dice->create('RequiresConstructorArgsA', ['C', 'D']);
		$this->assertEquals('C', $a2->foo);
		$this->assertEquals('D', $a2->bar);
	}

	public function testNullScalar() {
		$rule = [];
		$rule['constructParams'] = [null];
		$dice = $this->dice->addRule('NullScalar', $rule);

		$obj = $dice->create('NullScalar');
		$this->assertEquals(null, $obj->string);
	}

	public function testNullScalarNested() {
		$rule = [];
		$rule['constructParams'] = [null];
		$dice = $this->dice->addRule('NullScalar', $rule);

		$obj = $dice->create('NullScalarNested');
		$this->assertEquals(null, $obj->nullScalar->string);
	}

	public function testNullableClassTypeHint() {
		$nullableClassTypeHint = $this->dice->create('NullableClassTypeHint');

		$this->assertEquals(null, $nullableClassTypeHint->obj);
	}

}