<?php
class SharedConstructorTest extends DiceTest
{
    public function testSharedConstructor()
    {
        $rule = [];
        $rule['sharedConstructor'] = ['true'];
        $this->dice->addRule('Greeter', $rule);

        $greeter = $this->dice->create('Greeter');
        $actual = $greeter->greet("John Doe");
        $expected = "Hi, John Doe!";
        $this->assertEquals($expected, $actual);
        $actual = $greeter->greet("John Doe");
        $expected = "From cache: Hi, John Doe!";
        $this->assertEquals($expected, $actual);

        $greeter2 = $this->dice->create('Greeter');
        $actual = $greeter2->greet("John Doe");
        $expected = "From cache: Hi, John Doe!";
        $this->assertEquals($expected, $actual);
        $this->assertSame($greeter, $greeter2);
    }
}
