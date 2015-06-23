<?php
/*@description        Dice - A minimal Dependency Injection Container for PHP
* @author             Tom Butler tom@r.je
* @copyright          2012-2015 Tom Butler <tom@r.je>
* @link               http://r.je/dice.html
* @license            http://www.opensource.org/licenses/bsd-license.php  BSD License
* @version            2.0
*/
class NamedInstancesTest extends DiceTest {
	public function testMultipleSharedInstancesByNameMixed() {
		$rule = [];
		$rule['shared'] = true;
		$rule['constructParams'][] = 'FirstY';
		
		$this->dice->addRule('Y', $rule);
		
		$rule = [];
		$rule['instanceOf'] = 'Y';
		$rule['shared'] = true;
		$rule['constructParams'][] = 'SecondY';
		
		$this->dice->addRule('[Y2]', $rule);
		
		$rule = [];
		$rule['constructParams'] = [ ['instance' => 'Y'], ['instance' => '[Y2]']];
		
		$this->dice->addRule('Z', $rule);
		
		$z = $this->dice->create('Z');
		$this->assertEquals($z->y1->name, 'FirstY');
		$this->assertEquals($z->y2->name, 'SecondY');		
	}

	public function testNonSharedComponentByNameA() {
		$rule = [];
		$rule['instanceOf'] = 'ExtendedB';
		$this->dice->addRule('$B', $rule);
		
		$rule = [];
		$rule['constructParams'][] = ['instance' => '$B'];
		$this->dice->addRule('A', $rule);
		
		$a = $this->dice->create('A');
		$this->assertInstanceOf('ExtendedB', $a->b);
	}
	
	public function testNonSharedComponentByName() {
		
		$rule = [];
		$rule['instanceOf'] = 'Y3';
		$rule['constructParams'][] = 'test';
		
		
		$this->dice->addRule('$Y2', $rule);
		
		
		$y2 = $this->dice->create('$Y2');
		//echo $y2->name;
		$this->assertInstanceOf('Y3', $y2);
		
		$rule = [];

		$rule['constructParams'][] = ['instance' => '$Y2'];
		$this->dice->addRule('Y1', $rule);
		
		$y1 = $this->dice->create('Y1');
		$this->assertInstanceOf('Y3', $y1->y2);
	}
	
	public function testSubstitutionByName() {
		$rule = [];
		$rule['instanceOf'] = 'ExtendedB';
		$this->dice->addRule('$B', $rule);
		
		$rule = [];
		$rule['substitutions']['B'] = ['instance' => '$B'];
				
		$this->dice->addRule('A', $rule);		
		$a = $this->dice->create('A');
		
		$this->assertInstanceOf('ExtendedB', $a->b);
	}
	
	public function testMultipleSubstitutions() {
		$rule = [];
		$rule['instanceOf'] = 'Y2';
		$rule['constructParams'][] = 'first';
		$this->dice->addRule('$Y2A', $rule);
		
		$rule = [];
		$rule['instanceOf'] = 'Y2';
		$rule['constructParams'][] = 'second';
		$this->dice->addRule('$Y2B', $rule);
		
		$rule = [];
		$rule['constructParams'] = array(['instance' => '$Y2A'], ['instance' => '$Y2B']);
		$this->dice->addRule('HasTwoSameDependencies', $rule);
		
		$twodep = $this->dice->create('HasTwoSameDependencies');
		
		$this->assertEquals('first', $twodep->y2a->name);
		$this->assertEquals('second', $twodep->y2b->name);		
	}

	
	
}