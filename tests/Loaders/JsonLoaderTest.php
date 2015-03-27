<?php
/* @description     Dice - A minimal Dependency Injection Container for PHP         *  
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2012-2015 Tom Butler <tom@r.je> | http://r.je/dice.html         *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.3.2                                                           */

require_once 'Dice.php';
require_once 'Loader/Json.php';
require_once 'Loader/Callback.php';


class JsonLoaderTest extends PHPUnit_Framework_TestCase {
	private $dice;
	private $jsonLoader;

	protected function setUp() {
		parent::setUp ();
		$this->dice = $this->getMock('\\Dice\\Dice', array('getRule', 'addRule'));		
		$this->dice->expects($this->any())->method('getRule')->will($this->returnValue(new \Dice\Rule));
		$this->jsonLoader = new \Dice\Loader\Json;
	}

	protected function tearDown() {
		$this->dice = null;	
		$this->jsonLoader = null;
		parent::tearDown ();
	}
	
	
	public function testSetDefaultRule() {
		$json = '{
"rules": [				
		{
			"name": "*",
			"shared": true
		}
	]
}';
		
	
		$equivalentRule = new \Dice\Rule;
		$equivalentRule->shared = true;
		
		$this->dice->expects($this->once())->method('addRule')->with($this->equalTo('*'), $this->equalTo($equivalentRule));
		$this->jsonLoader->load($json, $this->dice);
		
	}
	
	public function testShared() {
		$json = '{
"rules": [				
		{
			"name": "A",
			"shared": true
		}
	]
}';
		
		
		$equivalentRule = new \Dice\Rule;
		$equivalentRule->shared = true;
		
		$this->dice->expects($this->once())->method('addRule')->with($this->equalTo('A'), $this->equalTo($equivalentRule));
		$this->jsonLoader->load($json, $this->dice);
	}
	
	
	
	public function testConstructParams() {
		$json = '{
"rules": [				
		{
			"name": "A",
			"construct": ["A", "B"]
		}
	]
}';	
	
		$equivalentRule = new \Dice\Rule;
		$equivalentRule->constructParams[] = 'A';
		$equivalentRule->constructParams[] = 'B';
	
		$this->dice->expects($this->once())->method('addRule')->with($this->equalTo('A'), $this->equalTo($equivalentRule));
		$this->jsonLoader->load($json, $this->dice);
	}
	
	
	public function testSubstitutions() {
		$json = '{
"rules": [				
		{
			"name": "A",
			"substitute": {"B": {"instance": "C"}}
		}
	]
}';	
	
	
	
		$equivalentRule = new \Dice\Rule;
		$equivalentRule->substitutions['B'] = new \Dice\Instance('C');
		
	
		$this->dice->expects($this->once())->method('addRule')->with($this->equalTo('A'), $this->equalTo($equivalentRule));
		$this->jsonLoader->load($json, $this->dice);
	}
	
	
	public function testSubstitutions2() {
		$json = '{
"rules": [				
		{
			"name": "A",
			"substitute": {"B": {"instance": "C"}, "F": {"instance": "E"}}
		}
	]
}';	
	
		$equivalentRule = new \Dice\Rule;
		$equivalentRule->substitutions['B'] = new \Dice\Instance('C');
		$equivalentRule->substitutions['F'] = new \Dice\Instance('E');
	
	
		$this->dice->expects($this->once())->method('addRule')->with($this->equalTo('A'), $this->equalTo($equivalentRule));
		$this->jsonLoader->load($json, $this->dice);
	}
	

	public function testSubstitutionsCall() {
		$json = '{
"rules": [				
		{
			"name": "A",
			"substitute": {"B": {"call": "JsonLoaderTest::foo()"}}
		}
	]
}';	
	
		$equivalentRule = new \Dice\Rule;
		$equivalentRule->substitutions['B'] = new \Dice\Instance([new \Dice\Loader\Callback('JsonLoaderTest::foo()'), 'run']);
	
	
		$this->dice->expects($this->once())->method('addRule')->with($this->equalTo('A'), $this->equalTo($equivalentRule));
		$this->jsonLoader->load($json, $this->dice);
	}
	

	public function testNewInstances() {
		$json = '{
"rules": [
		{
			"name": "A",
			"newinstances": ["C", "D", "E"]
		}
	]
}';		
		$equivalentRule = new \Dice\Rule;
		$equivalentRule->newInstances = ['C', 'D', 'E'];	
	
		$this->dice->expects($this->once())->method('addRule')->with($this->equalTo('A'), $this->equalTo($equivalentRule));
		$this->jsonLoader->load($json, $this->dice);
	}
	

	public function testInstanceOf() {
		$json = '{
"rules": [
		{
			"name": "[C]",
			"instanceof": "C"
		}
	]
}';		
		$equivalentRule = new \Dice\Rule;
		$equivalentRule->instanceOf = 'C';
	
		$this->dice->expects($this->once())->method('addRule')->with($this->equalTo('[C]'), $this->equalTo($equivalentRule));
		$this->jsonLoader->load($json, $this->dice);
	}
	
	
	public function testCall() {
		$json = '{
"rules": [
		{
			"name": "A",
			"call": [
					["setFoo", ["Foo", "Bar"]]
				]
		}
	]
}';		
	
		$equivalentRule = new \Dice\Rule;
		$equivalentRule->call[] = ['setFoo', ['Foo', 'Bar']];
	
		$this->dice->expects($this->once())->method('addRule')->with($this->equalTo('A'), $this->equalTo($equivalentRule));
		$this->jsonLoader->load($json, $this->dice);
	}
	
	

	public function testInherit() {
		$json = '{
"rules": [
		{
			"name": "A",
			"inherit": true
		}
	]
}';		
	
	
		$equivalentRule = new \Dice\Rule;
		$equivalentRule->inherit = true;
	
		$this->dice->expects($this->once())->method('addRule')->with($this->equalTo('A'), $this->equalTo($equivalentRule));
		$this->jsonLoader->load($json, $this->dice);
	}
	
	
	public function testInherit2() {
		$json = '{
"rules": [
		{
			"name": "A",
			"inherit": false
		}
	]
}';		
		$equivalentRule = new \Dice\Rule;
		$equivalentRule->inherit = false;
	
		$this->dice->expects($this->once())->method('addRule')->with($this->equalTo('A'), $this->equalTo($equivalentRule));
		$this->jsonLoader->load($json, $this->dice);
	}
	
	
	
	public function testShareInstance() {
		$json = '{
"rules": [
		{
			"name": "A",
			"shareinstances": ["C", "D"]
		}
	]
}';		
		$equivalentRule = new \Dice\Rule;
		$equivalentRule->shareInstances = ['C', 'D'];
		
		$this->dice->expects($this->once())->method('addRule')->with($this->equalTo('A'), $this->equalTo($equivalentRule));
		$this->jsonLoader->load($json, $this->dice);
	}
	
	
}
