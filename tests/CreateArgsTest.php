<?php
/* @description Dice - A minimal Dependency Injection Container for PHP *
 * @author Tom Butler tom@r.je *
 * @copyright 2012-2018 Tom Butler <tom@r.je> | https:// r.je/dice.html *
 * @license http:// www.opensource.org/licenses/bsd-license.php BSD License *
 * @version 3.0 */
class CreateArgsTest extends DiceTest {

	public function testConsumeArgs() {
		$rule = [];
		$rule['constructParams'] = ['A'];
		$dice = $this->dice->addRule('ConsumeArgsSub', $rule);
		$foo = $dice->create('ConsumeArgsTop',['B']);

		$this->assertEquals('A', $foo->a->s);
	}


    public function testConstructArgs() {
		$obj = $this->dice->create('RequiresConstructorArgsA', array('foo', 'bar'));
		$this->assertEquals($obj->foo, 'foo');
		$this->assertEquals($obj->bar, 'bar');
	}

	public function testConstructArgsMixed() {
		$obj = $this->dice->create('RequiresConstructorArgsB', array('foo', 'bar'));
		$this->assertEquals($obj->foo, 'foo');
		$this->assertEquals($obj->bar, 'bar');
		$this->assertInstanceOf('A', $obj->a);
	}

	public function testCreateArgs1() {
		$a = $this->dice->create('A', array($this->dice->create('ExtendedB')));
		$this->assertInstanceOf('ExtendedB', $a->b);
	}


	public function testCreateArgs2() {
		$a2 = $this->dice->create('A2', array($this->dice->create('ExtendedB'), 'Foo'));
		$this->assertInstanceOf('B', $a2->b);
		$this->assertInstanceOf('C', $a2->c);
		$this->assertEquals($a2->foo, 'Foo');
	}


	public function testCreateArgs3() {
		//reverse order args. It should be smart enough to handle this.
		$a2 = $this->dice->create('A2', array('Foo', $this->dice->create('ExtendedB')));
		$this->assertInstanceOf('B', $a2->b);
		$this->assertInstanceOf('C', $a2->c);
		$this->assertEquals($a2->foo, 'Foo');
	}

	public function testCreateArgs4() {
		$a2 = $this->dice->create('A3', array('Foo', $this->dice->create('ExtendedB')));
		$this->assertInstanceOf('B', $a2->b);
		$this->assertInstanceOf('C', $a2->c);
		$this->assertEquals($a2->foo, 'Foo');
	}

	public function testBestMatch() {
		$bestMatch = $this->dice->create('BestMatch', array('foo', $this->dice->create('A')));
		$this->assertEquals('foo', $bestMatch->string);
		$this->assertInstanceOf('A', $bestMatch->a);
	}

	public function testTwoDefaultNullClass() {
		$obj = $this->dice->create('MethodWithTwoDefaultNullC');
        $this->assertNull($obj->a);
		$this->assertInstanceOf('NB',$obj->b);
    }

    public function testTwoDefaultNullClassClass() {
		$obj = $this->dice->create('MethodWithTwoDefaultNullCC');
        $this->assertNull($obj->a);
		$this->assertInstanceOf('NB',$obj->b);
		$this->assertInstanceOf('NC',$obj->c);
    }

    public function testScalarConstructorArgs() {
    	$obj = $this->dice->create('ScalarConstructors', ['string', null]);
    	$this->assertEquals('string', $obj->string);
    	$this->assertEquals(null, $obj->null);
    }

}