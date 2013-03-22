<?php
require_once '../dice.php';
require_once 'PHPUnit\Framework\TestCase.php';


class DiceTest extends PHPUnit_Framework_TestCase {
	private $dice;


	protected function setUp() {
		parent::setUp ();
		$this->dice = new Dice();	
	}

	protected function tearDown() {
		$this->dice = null;		
		parent::tearDown ();
	}
	
	
	public function testAssign() {
		$obj = $this->getMock('stdClass', array(), array(), 'AssignMe');
		$this->dice->assign($obj);
		$result = $this->dice->create('AssignMe');		
		$this->assertSame($obj, $result);
	}
	
	
	
	public function testSetDefaultRule() {
		$defaultBehaviour = new DiceRule();
		$defaultBehaviour->shared = true;
		$defaultBehaviour->newInstances = array('Foo', 'Bar');
		$this->dice->addRule('*', $defaultBehaviour);		
		$this->assertSame($defaultBehaviour, $this->dice->getRule('*'));
	}

	public function testDefaultRuleWorks() {
		$defaultBehaviour = new DiceRule();
		$defaultBehaviour->shared = true;
		
		$this->dice->addRule('*', $defaultBehaviour);
		
		$rule = $this->dice->getRule('A');
		
		$this->assertTrue($rule->shared);
		
		$a1 = $this->dice->create('A');
		$a2 = $this->dice->create('A');
		
		$this->assertSame($a1, $a2);
	}
	
	
	public function testCreate() {
		$this->getMock('stdClass', array(), array(), 'TestCreate');
		$myobj = $this->dice->create('TestCreate');
		$this->assertInstanceOf('TestCreate', $myobj);
	}
	
	public function testCreateInvalid() {
		//"can't expect default exception". Not sure why.
		$this->setExpectedException('ErrorException');
		try {
			$this->dice->create('SomeClassThatDoesNotExist');
		}
		catch (Exception $e) {
			throw new ErrorException('Error occurred');
		}
	}
	

	/*
	 * Object graph creation cannot be tested with mocks because the constructor need to be tested.
	 * You can't set 'expects' on the objects which are created making them redundant for that as well
	 * Need real classes to test with unfortunately. 
	 */
	public function testObjectGraphCreation() {
		$a = $this->dice->create('A');
		$this->assertInstanceOf('B', $a->b);
		$this->assertInstanceOf('c', $a->b->c);
		$this->assertInstanceOf('D', $a->b->c->d);
		$this->assertInstanceOf('E', $a->b->c->e);
		$this->assertInstanceOf('F', $a->b->c->e->f);
	} 

	public function testNewInstances() {
		$rule = new DiceRule;
		$rule->shared = true;
		$this->dice->addRule('B', $rule);
		
		$rule = new DiceRule;
		$rule->newInstances[] = 'B';
		$this->dice->addRule('A', $rule);
		
		$a1 = $this->dice->create('A');
		$a2 = $this->dice->create('A');
		
		$this->assertNotSame($a1->b, $a2->b);
	}
	
	public function testSharedNamed() {
		$rule = new DiceRule;
		$rule->shared = true;
		$rule->instanceOf = 'A';
		
		$this->dice->addRule('[A]', $rule);
		
		$a1 = $this->dice->create('[A]');
		$a2 = $this->dice->create('[A]');
		$this->assertSame($a1, $a2);
	}
	
	public function testForceNewInstance() {
		$rule = new DiceRule;
		$rule->shared = true;
		$this->dice->addRule('A', $rule);
		
		$a1 = $this->dice->create('A');
		$a2 = $this->dice->create('A');
		
		$a3 = $this->dice->create('A', array(), null, true);
		
		$this->assertSame($a1, $a2);
		$this->assertNotSame($a1, $a3);
		$this->assertNotSame($a2, $a3);
	
	}
	
	
	public function testSharedRule() {
		$shared = new DiceRule;
		$shared->shared = true;
	
		$this->dice->addRule('MyObj', $shared);
	
		$obj = $this->dice->create('MyObj');
		$this->assertInstanceOf('MyObj', $obj);
	
		$obj2 = $this->dice->create('MyObj');
		$this->assertInstanceOf('MyObj', $obj2);
	
		$this->assertSame($obj, $obj2);
	
	
		//This check isn't strictly needed but it's nice to have that safety measure!
		$obj->setFoo('bar');
		$this->assertEquals($obj->getFoo(), $obj2->getFoo());
		$this->assertEquals($obj->getFoo(), 'bar');
		$this->assertEquals($obj2->getFoo(), 'bar');
	}
	
	
	public function testSubstitutionText() {
		$rule = new DiceRule;
		$rule->substitutions['B'] = new DiceInstance('ExtendedB');
		$this->dice->addRule('A', $rule);
		
		$a = $this->dice->create('A');
		
		$this->assertInstanceOf('ExtendedB', $a->b);
	}
	
	public function testSubstitutionCallback() {
		$rule = new DiceRule;
		$injection = $this->dice;
		$rule->substitutions['B'] = function() use ($injection) {
			return $injection->create('ExtendedB');
		};
		
		$this->dice->addRule('A', $rule);
		
		$a = $this->dice->create('A');
		
		$this->assertInstanceOf('ExtendedB', $a->b);
	}
	
	public function testSubstitutionObject() {
		$rule = new DiceRule;

		$rule->substitutions['B'] = $this->dice->create('ExtendedB');
				
		$this->dice->addRule('A', $rule);
		
		$a = $this->dice->create('A');
		$this->assertInstanceOf('ExtendedB', $a->b);
	}
	
	public function testSubstitutionString() {
		$rule = new DiceRule;
	
		$rule->substitutions['B'] = new DiceInstance('ExtendedB');
	
		$this->dice->addRule('A', $rule);
	
		$a = $this->dice->create('A');
		$this->assertInstanceOf('ExtendedB', $a->b);
	}
	
	
	public function testConstructParams() {
		$rule = new DiceRule;
		$rule->constructParams = array('foo', 'bar');
		$this->dice->addRule('RequiresConstructorArgsA', $rule);
		
		$obj = $this->dice->create('RequiresConstructorArgsA');
		
		$this->assertEquals($obj->foo, 'foo');
		$this->assertEquals($obj->bar, 'bar');
	}
	
	public function testConstructParamsMixed() {
		$rule = new DiceRule;
		$rule->constructParams = array('foo', 'bar');
		$this->dice->addRule('RequiresConstructorArgsB', $rule);
		
		$obj = $this->dice->create('RequiresConstructorArgsB');
		
		$this->assertEquals($obj->foo, 'foo');
		$this->assertEquals($obj->bar, 'bar');
		$this->assertInstanceOf('A', $obj->a);
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
	
	public function testCreateCallback() {
		$result = false;
		$callback = function($params) use (&$result) {
			$result = $params;
		};
		
		$this->dice->create('A', array(), $callback);
		
		$this->assertTrue(is_array($result));
		$this->assertInstanceOf('B', $result[0]);
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
	
	public function testMultipleSharedInstancesByNameMixed() {
		$rule = new DiceRule;
		$rule->shared = true;
		$rule->constructParams[] = 'FirstY';
		
		$this->dice->addRule('Y', $rule);
		
		$rule = new DiceRule;
		$rule->instanceOf = 'Y';
		$rule->shared = true;
		$rule->constructParams[] = 'SecondY';
		
		$this->dice->addRule('[Y2]', $rule);
		
		$rule = new DiceRule;
		$rule->constructParams = array(new DiceInstance('Y'), new DiceInstance('[Y2]'));
		
		$this->dice->addRule('Z', $rule);
		
		$z = $this->dice->create('Z');
		$this->assertEquals($z->y1->name, 'FirstY');
		$this->assertEquals($z->y2->name, 'SecondY');		
		
	}
	
	public function testNonSharedComponentByNameA() {
		$rule = new DiceRule;
		$rule->instanceOf = 'ExtendedB';
		$this->dice->addRule('$B', $rule);
		
		$rule = new DiceRule;
		$rule->constructParams[] = new DiceInstance('$B');
		$this->dice->addRule('A', $rule);
		
		$a = $this->dice->create('A');
		$this->assertInstanceOf('ExtendedB', $a->b);
	}
	
	public function testNonSharedComponentByName() {
		
		$rule = new DiceRule;
		$rule->instanceOf = 'Y3';
		$rule->constructParams[] = 'test';
		
		
		$this->dice->addRule('$Y2', $rule);
		
		
		$y2 = $this->dice->create(new DiceInstance('$Y2'));
		//echo $y2->name;
		$this->assertInstanceOf('Y3', $y2);
		
		$rule = new DiceRule;

		$rule->constructParams[] = new DiceInstance('$Y2');
		$this->dice->addRule('Y1', $rule);
		
		$y1 = $this->dice->create('Y1');
		$this->assertInstanceOf('Y3', $y1->y2);
	}
	
	public function testSubstitutionByName() {
		$rule = new DiceRule;
		$rule->instanceOf = 'ExtendedB';
		$this->dice->addRule('$B', $rule);
		
		$rule = new DiceRule;
		$rule->substitutions['B'] = new DiceInstance('$B');
				
		$this->dice->addRule('A', $rule);		
		$a = $this->dice->create('A');
		
		$this->assertInstanceOf('ExtendedB', $a->b);
	}
	
	public function testMultipleSubstitutions() {
		$rule = new DiceRule;
		$rule->instanceOf = 'Y2';
		$rule->constructParams[] = 'first';
		$this->dice->addRule('$Y2A', $rule);
		
		$rule = new DiceRule;
		$rule->instanceOf = 'Y2';
		$rule->constructParams[] = 'second';
		$this->dice->addRule('$Y2B', $rule);
		
		$rule = new DiceRule;
		$rule->constructParams = array(new DiceInstance('$Y2A'), new DiceInstance('$Y2B'));
		$this->dice->addRule('HasTwoSameDependencies', $rule);
		
		$twodep = $this->dice->create('HasTwoSameDependencies');
		
		$this->assertEquals('first', $twodep->y2a->name);
		$this->assertEquals('second', $twodep->y2b->name);
		
	}
	
	
	public function testCall() {
		$rule = new DiceRule;
		$rule->call[] = array('callMe', array());
		$this->dice->addRule('TestCall', $rule);
		$object = $this->dice->create('TestCall');
		$this->assertTrue($object->isCalled);
	}
	
	public function testCallWithParameters() {
		$rule = new DiceRule;
		$rule->call[] = array('callMe', array('one', 'two'));
		$this->dice->addRule('TestCall2', $rule);
		$object = $this->dice->create('TestCall2');
		$this->assertEquals('one', $object->foo);
		$this->assertEquals('two', $object->bar);
	}
	
	
	//
	public function testInterfaceRule() {
		$rule = new DiceRule;

		$rule->shared = true;
		$this->dice->addRule('interfaceTest', $rule);
		
		$one = $this->dice->create('InterfaceTestClass');
		$two = $this->dice->create('InterfaceTestClass');
		
		
		$this->assertSame($one, $two);
		
		
		
	}
	
	
	public function testBestMatch() {
		$bestMatch = $this->dice->create('BestMatch', array('foo', $this->dice->create('A')));
		$this->assertEquals('foo', $bestMatch->string);
		$this->assertInstanceOf('A', $bestMatch->a);
	}
	
	
	public function testShareInstances() {
		$rule = new DiceRule();
		$rule->shareInstances = array(new DiceInstance('Shared'));
		$this->dice->addRule('TestSharedInstancesTop', $rule);
		
		
		$shareTest = $this->dice->create('TestSharedInstancesTop');
		
		$this->assertinstanceOf('TestSharedInstancesTop', $shareTest);
		
		$this->assertInstanceOf('SharedInstanceTest1', $shareTest->share1);
		$this->assertInstanceOf('SharedInstanceTest2', $shareTest->share2);
		
		$this->assertSame($shareTest->share1->shared, $shareTest->share2->shared);
		$this->assertEquals($shareTest->share1->shared->uniq, $shareTest->share2->shared->uniq);
		
	}
	
	public function testShareInstancesMultiple() {
		$rule = new DiceRule();
		$rule->shareInstances = array(new DiceInstance('Shared'));
		$this->dice->addRule('TestSharedInstancesTop', $rule);
	
	
		$shareTest = $this->dice->create('TestSharedInstancesTop');
	
		$this->assertinstanceOf('TestSharedInstancesTop', $shareTest);
	
		$this->assertInstanceOf('SharedInstanceTest1', $shareTest->share1);
		$this->assertInstanceOf('SharedInstanceTest2', $shareTest->share2);
	
		$this->assertSame($shareTest->share1->shared, $shareTest->share2->shared);
		$this->assertEquals($shareTest->share1->shared->uniq, $shareTest->share2->shared->uniq);
		
		
		$shareTest2 = $this->dice->create('TestSharedInstancesTop');
		$this->assertSame($shareTest2->share1->shared, $shareTest2->share2->shared);
		$this->assertEquals($shareTest2->share1->shared->uniq, $shareTest2->share2->shared->uniq);
		
		$this->assertNotSame($shareTest->share1->shared, $shareTest2->share2->shared);
		$this->assertNotEquals($shareTest->share1->shared->uniq, $shareTest2->share2->shared->uniq);
	
	}
	

}

class Shared {
	public $uniq;
	
	public function __construct() {
		$this->uniq = uniqid();
	}
}

class TestSharedInstancesTop {
	public $share1;
	public $share2;
	
	public function __construct(SharedInstanceTest1 $share1, SharedInstanceTest2 $share2) {
		$this->share1 = $share1;
		$this->share2 = $share2;
	}	
}


class SharedInstanceTest1 {
	public $shared;
	
	public function __construct(Shared $shared) {
		$this->shared = $shared;		
	}
}


class SharedInstanceTest2 {
	public $shared;

	public function __construct(Shared $shared) {
		$this->shared = $shared;
	}
}

class TestCall {
	public $isCalled = false;
	
	public function callMe() {
		$this->isCalled = true;
	}
}

class TestCall2 {
	public $foo;
	public $bar;

	public function callMe($foo, $bar) {
		$this->foo = $foo;
		$this->bar = $bar;
	}
}


class TestCall3 {
	public $a;

	public function callMe(A $a) {
		$this->a = $a;
	}
}

class HasTwoSameDependencies {
	public $y2a;
	public $y2b;
	
	public function __construct(Y2 $y2a, Y2 $y2b) {
		$this->y2a = $y2a;
		$this->y2b = $y2b;
	}
}

class Y1 {
	public $y2;
	
	public function __construct(Y2 $y2) {
		$this->y2 = $y2;
	}
}


class Y2 {
	public $name; 
	
	public function __construct($name) {
		$this->name = $name;
	}
}

class Y3 extends Y2 {
	
}
class Z {
	public $y1;
	public $y2;
	public function __construct(Y $y1, Y $y2) {
		$this->y1 = $y1;
		$this->y2 = $y2;
	}
}

class Y {
	public $name;
	public function __construct($name) {
		$this->name = $name;
	}
}

class BestMatch {
	public $a;
	public $string;
	public $b;
	
	public function __construct($string, A $a, B $b) {
		$this->a = $a;
		$this->string = $string;
		$this->b = $b;
	}	
}


//Because the DIC's job is to create other classes, some dummy class definitions are required.
//Mocks cannot be used because the DIC relies on class definitions

class MyObj {
	private $foo;

	public function setFoo($foo) {
		$this->foo = $foo;
	}

	public function getFoo() {
		return $this->foo;
	}
}


class A2 {
	public $b;
	public $c;
	public $foo;

	public function __construct(B $b, C $c, $foo) {
		$this->b = $b;
		$this->foo = $foo;
		$this->c = $c;
	}
}


class A3 {
	public $b;
	public $c;
	public $foo;

	public function __construct(C $c, $foo, B $b) {
		$this->b = $b;
		$this->foo = $foo;
		$this->c = $c;
	}
}
class A {
	public $b;

	public function __construct(B $b) {
		$this->b = $b;
	}
}

class B {
	public $c;

	public function __construct(C $c) {
		$this->c = $c;
	}
}

class ExtendedB extends B {

}

class C {
	public $d;
	public $e;

	public function __construct(D $d, E $e) {
		$this->d = $d;
		$this->e = $e;
	}
}


class D {

}

class E {
	public $f;
	public function __construct(F $f) {
		$this->f = $f;
	}
}

class F {}

class RequiresConstructorArgsA {
	public $foo;
	public $bar;

	public function __construct($foo, $bar) {
		$this->foo = $foo;
		$this->bar = $bar;
	}
}

class RequiresConstructorArgsB {
	public $a;
	public $foo;
	public $bar;

	public function __construct(A $a, $foo, $bar) {
		$this->a = $a;
		$this->foo = $foo;
		$this->bar = $bar;
	}
}

interface interfaceTest {}

class InterfaceTestClass implements interfaceTest {

}
