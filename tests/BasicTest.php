<?php
/* @description Dice - A minimal Dependency Injection Container for PHP *
 * @author Tom Butler tom@r.je *
 * @copyright 2012-2018 Tom Butler <tom@r.je> | https:// r.je/dice.html *
 * @license http:// www.opensource.org/licenses/bsd-license.php BSD License *
 * @version 3.0 */

class BasicTest extends DiceTest {

    public function testCreate() {
        $this->getMockBuilder('TestCreate')->getMock();
        $myobj = $this->dice->create('TestCreate');
        $this->assertInstanceOf('TestCreate', $myobj);
    }

    public function testCreateInvalid() {
        //"can't expect default exception". Not sure why.
        $this->expectException('ErrorException');
        try {
            $this->dice->create('SomeClassThatDoesNotExist');
        }
        catch (Exception $e) {
            throw new ErrorException('Error occurred');
        }
    }

    public function testNoConstructor() {
        $a = $this->dice->create('NoConstructor');
        $this->assertInstanceOf('NoConstructor', $a);
    }


    public function testSetDefaultRule() {
        $defaultBehaviour = [];
        $defaultBehaviour['shared'] = true;
        $this->dice->addRule('*', $defaultBehaviour);

        $rule = $this->dice->getRule('*');
        foreach ($defaultBehaviour as $name => $value) {
            $this->assertEquals($rule[$name], $defaultBehaviour[$name]);
        }
    }


    public function testDefaultRuleWorks() {
        $defaultBehaviour = [];
        $defaultBehaviour['shared'] = true;

        $this->dice->addRule('*', $defaultBehaviour);

        $rule = $this->dice->getRule('A');

        $this->assertTrue($rule['shared']);

        $a1 = $this->dice->create('A');
        $a2 = $this->dice->create('A');

        $this->assertSame($a1, $a2);
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

    public function testSharedNamed() {
        $rule = [];
        $rule['shared'] = true;
        $rule['instanceOf'] = 'A';

        $this->dice->addRule('[A]', $rule);

        $a1 = $this->dice->create('[A]');
        $a2 = $this->dice->create('[A]');
        $this->assertSame($a1, $a2);
    }

    public function testSharedRule() {
        $shared = [];
        $shared['shared'] = true;

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


    public function testInterfaceRule() {
        $rule = [];

        $rule['shared'] = true;
        $this->dice->addRule('interfaceTest', $rule);

        $one = $this->dice->create('InterfaceTestClass');
        $two = $this->dice->create('InterfaceTestClass');

        $this->assertSame($one, $two);
    }

    public function testCyclicReferences() {
        $rule = [];
        $rule['shared'] = true;

        $this->dice->addRule('CyclicB', $rule);

        $a = $this->dice->create('CyclicA');

        $this->assertInstanceOf('CyclicB', $a->b);
        $this->assertInstanceOf('CyclicA', $a->b->a);

        $this->assertSame($a->b, $a->b->a->b);
    }

    public function testInherit() {
        $rule = ['shared' => true, 'inherit' => false];

        $this->dice->addRule('ParentClass', $rule);
        $obj1 = $this->dice->create('Child');
        $obj2 = $this->dice->create('Child');
        $this->assertNotSame($obj1, $obj2);
    }

    public function testSharedOverride() {

        //Set everything to shared by default
        $this->dice->addRule('*', ['shared' => true]);

        $this->dice->addRule('A', ['shared' => false]);

        $a1 = $this->dice->create('A');
        $a2 = $this->dice->create('A');

        $this->assertNotSame($a1, $a2);

    }

    public function testOptionalInterface() {

        $optionalInterface = $this->dice->create('OptionalInterface');

        $this->assertEquals(null, $optionalInterface->obj);
    }


    public function testScalarTypeHintWithShareInstances() {

        $this->dice->addRule('ScalarTypeHint', ['shareInstances' => ['A']]);

        $obj = $this->dice->create('ScalarTypeHint');

        $this->assertInstanceOf('ScalarTypeHint', $obj);
    }

    public function testPassGlobals() {
        //write to the global $_GET variable
        $_GET['foo'] = 'bar';

        $this->dice->addRule('CheckConstructorArgs',
            [
                'constructParams' => [
                    [\Dice\Dice::GLOBAL => '_GET']
                ]
        ]);

        $obj = $this->dice->create('CheckConstructorArgs');

        $this->assertEquals($_GET, $obj->arg1);
    }

    public function testPassConstantString() {
        $this->dice->addRule('CheckConstructorArgs',
            [
                'constructParams' => [
                    [\Dice\Dice::CONSTANT => '\PDO::FETCH_ASSOC']
                ]
        ]);

        $obj = $this->dice->create('CheckConstructorArgs');

        $this->assertEquals(\PDO::FETCH_ASSOC, $obj->arg1);
    }
}