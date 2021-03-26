<?php
/* @description Dice - A minimal Dependency Injection Container for PHP *
 * @author Tom Butler tom@r.je *
 * @copyright 2012-2018 Tom Butler <tom@r.je> | https:// r.je/dice.html *
 * @license http:// www.opensource.org/licenses/bsd-license.php BSD License *
 * @version 3.0 */
class NamedInstancesTest extends DiceTest
{
    public function testMultipleSharedInstancesByNameMixed()
    {
        $rule = [];
        $rule['shared'] = true;
        $rule['constructParams'][] = 'FirstY';

        $dice = $this->dice->addRule('Y', $rule);

        $rule = [];
        $rule['instanceOf'] = 'Y';
        $rule['shared'] = true;
        $rule['inherit'] = false;
        $rule['constructParams'][] = 'SecondY';

        $dice = $dice->addRule('[Y2]', $rule);

        $rule = [];
        $rule['constructParams'] = [
            [\Dice\Dice::INSTANCE => 'Y'],
            [\Dice\Dice::INSTANCE => '[Y2]'],
        ];

        $dice = $dice->addRule('Z', $rule);

        $z = $dice->create('Z');
        $this->assertEquals($z->y1->name, 'FirstY');
        $this->assertEquals($z->y2->name, 'SecondY');
    }

    public function testNonSharedComponentByNameA()
    {
        $rule = [];
        $rule['instanceOf'] = 'ExtendedB';
        $dice = $this->dice->addRule('$B', $rule);

        $rule = [];
        $rule['constructParams'][] = [\Dice\Dice::INSTANCE => '$B'];
        $dice = $dice->addRule('A', $rule);

        $a = $dice->create('A');
        $this->assertInstanceOf('ExtendedB', $a->b);
    }

    public function testNonSharedComponentByName()
    {
        $rule = [];
        $rule['instanceOf'] = 'Y3';
        $rule['constructParams'][] = 'test';

        $dice = $this->dice->addRule('$Y2', $rule);

        $y2 = $dice->create('$Y2');
        //echo $y2->name;
        $this->assertInstanceOf('Y3', $y2);

        $rule = [];

        $rule['constructParams'][] = [\Dice\Dice::INSTANCE => '$Y2'];
        $dice = $dice->addRule('Y1', $rule);

        $y1 = $dice->create('Y1');
        $this->assertInstanceOf('Y3', $y1->y2);
    }

    public function testSubstitutionByName()
    {
        $rule = [];
        $rule['instanceOf'] = 'ExtendedB';
        $dice = $this->dice->addRule('$B', $rule);

        $rule = [];
        $rule['substitutions']['B'] = [\Dice\Dice::INSTANCE => '$B'];

        $dice = $dice->addRule('A', $rule);
        $a = $dice->create('A');

        $this->assertInstanceOf('ExtendedB', $a->b);
    }

    public function testMultipleSubstitutions()
    {
        $rule = [];
        $rule['instanceOf'] = 'Y2';
        $rule['constructParams'][] = 'first';
        $dice = $this->dice->addRule('$Y2A', $rule);

        $rule = [];
        $rule['instanceOf'] = 'Y2';
        $rule['constructParams'][] = 'second';
        $dice = $dice->addRule('$Y2B', $rule);

        $rule = [];
        $rule['constructParams'] = [
            [\Dice\Dice::INSTANCE => '$Y2A'],
            [\Dice\Dice::INSTANCE => '$Y2B'],
        ];
        $dice = $dice->addRule('HasTwoSameDependencies', $rule);

        $twodep = $dice->create('HasTwoSameDependencies');

        $this->assertEquals('first', $twodep->y2a->name);
        $this->assertEquals('second', $twodep->y2b->name);
    }

    public function testNamedInstanceCallWithInheritance()
    {
        $rule1 = [];
        $rule1['call'] = [['callMe', [1, 3]], ['callMe', [3, 4]]];

        $dice = $this->dice->addRule('Y', $rule1);

        $rule2 = [];
        $rule2['instanceOf'] = 'Y';
        $rule2['constructParams'] = ['Foo'];

        $dice = $dice->addRule('$MyInstance', $rule2);

        $this->assertEquals(
            array_merge_recursive($rule1, $rule2),
            $dice->getRule('$MyInstance')
        );
    }

    public function testNamedInstanceCallWithoutInheritance()
    {
        $rule1 = [];
        $rule1['call'] = [['callMe', [1, 3]], ['callMe', [3, 4]]];

        $dice = $this->dice->addRule('Y', $rule1);

        $rule2 = [];
        $rule2['instanceOf'] = 'Y';
        $rule2['inherit'] = false;
        $rule2['constructParams'] = ['Foo'];

        $dice = $dice->addRule('$MyInstance', $rule2);

        $this->assertEquals($rule2, $dice->getRule('$MyInstance'));
    }

    public function testNamedMultipleSharedInstances()
    {
        $dice = new Dice\Dice();
        $p1 = 'mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4';
        $p2 = 'mysql:host=$host2;port=$port2;dbname=$name;charset=utf8mb4';
        $rule1 = [
            'constructParams' => [$p1],
            'shared' => true,
        ];
        $dice = $dice->addRule(Y::class, $rule1);
        $rule2 = [
            'instanceOf' => Y::class,
            'constructParams' => [$p2],
            'shared' => true,
        ];
        $dice = $dice->addRule('$y2', $rule2);
        $y1 = $dice->create(Y::class);
        $y2 = $dice->create('$y2');
        $this->assertInstanceOf(Y::class, $y1);
        $this->assertInstanceOf(Y::class, $y2);
        $this->assertEquals($p1, $y1->name);
        $this->assertEquals($p2, $y2->name);
    }

    public function testMultiplePDO()
    {
        $dice = new \Dice\Dice();

        $rule = [
            'constructParams' => ['My Exception'],
            'shared' => true,
        ];
        $dice = $dice->addRule(\RuntimeException::class, $rule);
        $rule = [
            'instanceOf' => \RuntimeException::class,
            'constructParams' => ['My Exception 2'],
            'shared' => true,
        ];
        $dice = $dice->addRule('$exception2', $rule);

        $ex1 = $dice->create(\RuntimeException::class);
        $this->assertEquals('My Exception', $ex1->getMessage());
        $ex2 = $dice->create('$exception2');
        $this->assertEquals('My Exception 2', $ex2->getMessage());
        $this->assertFalse($ex1 === $ex2);
        $secondEx1 = $dice->create(\RuntimeException::class);
        $this->assertTrue($ex1 === $secondEx1);
    }
}
