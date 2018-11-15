<?php
/* @description Dice - A minimal Dependency Injection Container for PHP *
 * @author Tom Butler tom@r.je *
 * @copyright 2012-2018 Tom Butler <tom@r.je> | https:// r.je/dice.html *
 * @license http:// www.opensource.org/licenses/bsd-license.php BSD License *
 * @version 3.0 */
class NamedInstancesTest extends DiceTest {
	public function testMultipleSharedInstancesByNameMixed() {
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
		$rule['constructParams'] = [ [\Dice\Dice::INSTANCE => 'Y'], [\Dice\Dice::INSTANCE => '[Y2]']];

		$dice = $dice->addRule('Z', $rule);

		$z = $dice->create('Z');
		$this->assertEquals($z->y1->name, 'FirstY');
		$this->assertEquals($z->y2->name, 'SecondY');
	}

	public function testNonSharedComponentByNameA() {
		$rule = [];
		$rule['instanceOf'] = 'ExtendedB';
		$dice = $this->dice->addRule('$B', $rule);

		$rule = [];
		$rule['constructParams'][] = [\Dice\Dice::INSTANCE => '$B'];
		$dice = $dice->addRule('A', $rule);

		$a = $dice->create('A');
		$this->assertInstanceOf('ExtendedB', $a->b);
	}

	public function testNonSharedComponentByName() {

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

	public function testSubstitutionByName() {
		$rule = [];
		$rule['instanceOf'] = 'ExtendedB';
		$dice = $this->dice->addRule('$B', $rule);

		$rule = [];
		$rule['substitutions']['B'] = [\Dice\Dice::INSTANCE => '$B'];

		$dice = $dice->addRule('A', $rule);
		$a = $dice->create('A');

		$this->assertInstanceOf('ExtendedB', $a->b);
	}

	public function testMultipleSubstitutions() {
		$rule = [];
		$rule['instanceOf'] = 'Y2';
		$rule['constructParams'][] = 'first';
		$dice = $this->dice->addRule('$Y2A', $rule);

		$rule = [];
		$rule['instanceOf'] = 'Y2';
		$rule['constructParams'][] = 'second';
		$dice = $dice->addRule('$Y2B', $rule);

		$rule = [];
		$rule['constructParams'] = array([\Dice\Dice::INSTANCE => '$Y2A'], [\Dice\Dice::INSTANCE => '$Y2B']);
		$dice = $dice->addRule('HasTwoSameDependencies', $rule);

		$twodep = $dice->create('HasTwoSameDependencies');

		$this->assertEquals('first', $twodep->y2a->name);
		$this->assertEquals('second', $twodep->y2b->name);
	}

	public function testNamedInstanceCallWithInheritance() {
		$rule1 = [];
		$rule1['call'] = [
				['callMe', [1, 3] ],
				['callMe', [3, 4] ]
		];

		$dice = $this->dice->addRule('Y', $rule1);

		$rule2 = [];
		$rule2['instanceOf'] = 'Y';
		$rule2['constructParams'] = ['Foo'];

		$dice = $dice->addRule('$MyInstance', $rule2);

		$this->assertEquals(array_merge_recursive($rule1, $rule2), $dice->getRule('$MyInstance'));

	}

	public function testNamedInstanceCallWithoutInheritance() {
		$rule1 = [];
		$rule1['call'] = [
				['callMe', [1, 3] ],
				['callMe', [3, 4] ]
		];

		$dice = $this->dice->addRule('Y', $rule1);

		$rule2 = [];
		$rule2['instanceOf'] = 'Y';
		$rule2['inherit'] = false;
		$rule2['constructParams'] = ['Foo'];

		$dice = $dice->addRule('$MyInstance', $rule2);

		$this->assertEquals($rule2, $dice->getRule('$MyInstance'));
	}

}