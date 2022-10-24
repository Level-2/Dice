<?php

/* @description Dice - A minimal Dependency Injection Container for PHP *
 * @author Tom Butler tom@r.je *
 * @copyright 2012-2018 Tom Butler <tom@r.je> | https:// r.je/dice.html *
 * @license http:// www.opensource.org/licenses/bsd-license.php BSD License *
 */
class CreateArgsTest extends DiceTest
{
    public function testConsumeArgs()
    {
        $rule = [];
        $rule["constructParams"] = ["A"];
        $dice = $this->dice->addRule("ConsumeArgsSub", $rule);
        $foo = $dice->create("ConsumeArgsTop", ["B"]);

        $this->assertEquals("A", $foo->a->s);
    }

    public function testConstructArgs()
    {
        $obj = $this->dice->create("RequiresConstructorArgsA", ["foo", "bar"]);
        $this->assertEquals($obj->foo, "foo");
        $this->assertEquals($obj->bar, "bar");
    }

    public function testConstructArgsMixed()
    {
        $obj = $this->dice->create("RequiresConstructorArgsB", ["foo", "bar"]);
        $this->assertEquals($obj->foo, "foo");
        $this->assertEquals($obj->bar, "bar");
        $this->assertInstanceOf("A", $obj->a);
    }

    public function testCreateArgs1()
    {
        $a = $this->dice->create("A", [$this->dice->create("ExtendedB")]);
        $this->assertInstanceOf("ExtendedB", $a->b);
    }

    public function testCreateArgs2()
    {
        $a2 = $this->dice->create("A2", [
            $this->dice->create("ExtendedB"),
            "Foo",
        ]);
        $this->assertInstanceOf("B", $a2->b);
        $this->assertInstanceOf("C", $a2->c);
        $this->assertEquals($a2->foo, "Foo");
    }

    public function testCreateArgs3()
    {
        //reverse order args. It should be smart enough to handle this.
        $a2 = $this->dice->create("A2", [
            "Foo",
            $this->dice->create("ExtendedB"),
        ]);
        $this->assertInstanceOf("B", $a2->b);
        $this->assertInstanceOf("C", $a2->c);
        $this->assertEquals($a2->foo, "Foo");
    }

    public function testCreateArgs4()
    {
        $a2 = $this->dice->create("A3", [
            "Foo",
            $this->dice->create("ExtendedB"),
        ]);
        $this->assertInstanceOf("B", $a2->b);
        $this->assertInstanceOf("C", $a2->c);
        $this->assertEquals($a2->foo, "Foo");
    }

    public function testBestMatch()
    {
        $bestMatch = $this->dice->create("BestMatch", [
            "foo",
            $this->dice->create("A"),
        ]);
        $this->assertEquals("foo", $bestMatch->string);
        $this->assertInstanceOf("A", $bestMatch->a);
    }

    public function testTwoDefaultNullClass()
    {
        $obj = $this->dice->create("MethodWithTwoDefaultNullC");
        $this->assertNull($obj->a);
        $this->assertNull($obj->b);
        //        $this->assertInstanceOf("NB", $obj->b);
    }

    public function testTwoDefaultNullClassClass()
    {
        $obj = $this->dice->create("MethodWithTwoDefaultNullCC");
        $this->assertNull($obj->a);
        $this->assertNull($obj->b);
        $this->assertNull($obj->c);
        // $this->assertInstanceOf("NB", $obj->b);
        // $this->assertInstanceOf("NC", $obj->c);
    }

    public function testScalarConstructorArgs()
    {
        $obj = $this->dice->create("ScalarConstructors", ["string", null]);
        $this->assertEquals("string", $obj->string);
        $this->assertEquals(null, $obj->null);
    }

    /**
     * Test case for issue #8
     * @link https://github.com/moddengine/Dice/issues/8
     */
    public function testArrayArgConstructors() {
        $dice = $this->dice->addRule('$defaultRenderer', [
            'shared' => true,
            'instanceOf' => ArrayArgRenderer::class,
            'constructParams' => [
                'foo',
                [1,2,3 => ['test']],
                'bar'
            ]
        ])->addRule('*', [
            'substitutions' => [
                ArrayArgRenderer::class => '$defaultRenderer'
            ]
        ]);
        $obj = $dice->create(ArrayArgController::class);
        $this->assertEquals('foo', $obj->renderer->a);
        $this->assertEquals([1,2,3=>['test']], $obj->renderer->list);
        $this->assertEquals('bar', $obj->renderer->b);
    }
}
