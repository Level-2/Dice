<?php
class SharedConstructorTest extends DiceTest
{
    public function testSharedConstructor()
    {
        $rule = [];
        $rule['sharedConstructor'] = ['true'];
        $this->dice->addRule('Test\SharedConstructor\Greeter', $rule);

        $greeter = $this->dice->create('Test\SharedConstructor\Greeter');
        $actual = $greeter->greet("John Doe");
        $expected = "Hi, John Doe!";
        $this->assertEquals($expected, $actual);
        $actual = $greeter->greet("John Doe");
        $expected = "From cache: Hi, John Doe!";
        $this->assertEquals($expected, $actual);

        $greeter2 = $this->dice->create('Test\SharedConstructor\Greeter');
        $actual = $greeter2->greet("John Doe");
        $expected = "From cache: Hi, John Doe!";
        $this->assertEquals($expected, $actual);
        $this->assertSame($greeter, $greeter2);
    }

    public function testSharedConstructorComplex()
    {
        $sharedConstructor = ['sharedConstructor' => true];
        $singleton = ['shared' => true];
        $this->dice->addRule('Test\SharedConstructor\BarBazShared', $sharedConstructor);
        $this->dice->addRule('Test\SharedConstructor\BarBaz', $sharedConstructor);
        $this->dice->addRule('Test\SharedConstructor\Baz', $singleton);

        $bar = $this->dice->create('Test\SharedConstructor\Bar', ['I am bar']);
        $baz = $this->dice->create('Test\SharedConstructor\Baz', ['I am baz']);
        $barBaz = $this->dice->create('Test\SharedConstructor\BarBaz', [$bar, $baz]);
        $strongWrapper = function ($str) {
            return "<strong>$str</strong>";
        };
        $bWrapper = function ($str) {
            return "<b>$str</b>";
        };
        $barBazShared = $this->dice->create(
            'Test\SharedConstructor\BarBazShared',
            [$barBaz, $strongWrapper]
        );
        $barBazShared2 = $this->dice->create(
            'Test\SharedConstructor\BarBazShared',
            [$barBaz, $strongWrapper]
        );

        $this->assertEquals($barBazShared->id, $barBazShared2->id);

        # Bar is not shared, so Dice will create new instance
        # Despite that the argument to Bar is the same
        $anotherBar = $this->dice->create(
            'Test\SharedConstructor\Bar',
            ['I am bar']
        );
        $anotherBarBaz = $this->dice->create(
            'Test\SharedConstructor\BarBaz',
            [$anotherBar, $baz]
        );
        $barBazShared3 = $this->dice->create(
            'Test\SharedConstructor\BarBazShared',
            [$anotherBarBaz, $strongWrapper]
        );
        $this->assertFalse($barBazShared3->id == $barBazShared->id);
        $this->assertFalse($barBazShared3->id == $barBazShared2->id);

        $this->assertEquals($barBazShared3->getBarBazWrapped(), $barBazShared->getBarBazWrapped());
        $this->assertEquals($barBazShared3->getBarBazWrapped(), $barBazShared2->getBarBazWrapped());

        # Baz is the same always even if we pass different param to the constructor
        $sameBaz = $this->dice->create(
            'Test\SharedConstructor\Baz',
            ['I am another baz']
        );
        $sameBarBaz = $this->dice->create(
            'Test\SharedConstructor\BarBaz',
            [$bar, $sameBaz]
        );
        $barBazShared4 = $this->dice->create(
            'Test\SharedConstructor\BarBazShared',
            [$sameBarBaz, $strongWrapper]
        );
        $this->assertEquals($barBazShared4->id, $barBazShared->id);

        # test change closure
        $barBazShared5 = $this->dice->create(
            'Test\SharedConstructor\BarBazShared',
            [$sameBarBaz, $bWrapper]
        );
        $this->assertFalse($barBazShared5->id == $barBazShared->id);
        $this->assertFalse($barBazShared5->id == $barBazShared4->id);
    }
}
