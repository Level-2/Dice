<?php
/*@description        Dice - A minimal Dependency Injection Container for PHP
* @author             Tom Butler tom@r.je
* @copyright          2012-2015 Tom Butler <tom@r.je>
* @link               http://r.je/dice.html
* @license            http://www.opensource.org/licenses/bsd-license.php  BSD License
* @version            2.0
*/
class NamespaceTest extends DiceTest {
	public function testNamespaceBasic() {
		$a = $this->dice->create('Foo\\A');
		$this->assertInstanceOf('Foo\\A', $a);
	}
	
	
	public function testNamespaceWithSlash() {
		$a = $this->dice->create('\\Foo\\A');
		$this->assertInstanceOf('\\Foo\\A', $a);
	}
	
	public function testNamespaceWithSlashrule() {
		$rule = [];
		$rule['substitutions']['Foo\\A'] = ['instance' => 'Foo\\ExtendedA'];
		$this->dice->addRule('\\Foo\\B', $rule);
		
		$b = $this->dice->create('\\Foo\\B');
		$this->assertInstanceOf('Foo\\ExtendedA', $b->a);
	}
	
	public function testNamespaceWithSlashruleInstance() {
		$rule = [];
		$rule['substitutions']['Foo\\A'] = ['instance' => 'Foo\\ExtendedA'];
		$this->dice->addRule('\\Foo\\B', $rule);
	
		$b = $this->dice->create('\\Foo\\B');
		$this->assertInstanceOf('Foo\\ExtendedA', $b->a);
	}
	
	public function testNamespaceTypeHint() {
		$rule = [];
		$rule['shared'] = true;
		$this->dice->addRule('Bar\\A', $rule);
		
		$c = $this->dice->create('Foo\\C');
		$this->assertInstanceOf('Bar\\A', $c->a);
		
		$c2 = $this->dice->create('Foo\\C');
		$this->assertNotSame($c, $c2);
		
		//Check the rule has been correctly recognised for type hinted classes in a different namespace
		$this->assertSame($c2->a, $c->a);
	}
	
	public function testNamespaceInjection() {
		$b = $this->dice->create('Foo\\B');
		$this->assertInstanceOf('Foo\\B', $b);
		$this->assertInstanceOf('Foo\\A', $b->a);		
	}
		
	
	public function testNamespaceRuleSubstitution() {
		$rule = [];
		$rule['substitutions']['Foo\\A'] = ['instance' => 'Foo\\ExtendedA'];
		$this->dice->addRule('Foo\\B', $rule);
		
		$b = $this->dice->create('Foo\\B');
		$this->assertInstanceOf('Foo\\ExtendedA', $b->a);
	}
	
}