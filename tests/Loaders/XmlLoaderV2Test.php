<?php
/* @description     Dice - A minimal Dependency Injection Container for PHP         *  
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2012-2015 Tom Butler <tom@r.je> | http://r.je/dice.html         *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         20.                                                             */

require_once 'Dice.php';
require_once 'Loader/Xml.php';


class XmlLoaderV2Test extends PHPUnit_Framework_TestCase {
	private $dice;
	private $xmlLoader;

	protected function setUp() {
		parent::setUp ();
		$dice = new \Dice\Dice;
		$this->dice = $this->getMock('\\Dice\\Dice', array('getRule', 'addRule'));		
		$this->dice->expects($this->any())->method('getRule')->will($this->returnValue($dice->getRule('*')));
		$this->xmlLoader = new \Dice\Loader\XML;
	}

	protected function tearDown() {
		$this->dice = null;	
		$this->xmlLoader = null;
		parent::tearDown ();
	}
	
	
	public function testSetDefaultRule() {
		$xml = '<?xml version="1.0"?>
<dice xmlns="https://r.je/dice/2.0">
	<rule name="*" shared="true"></rule>
</dice>';
		
	
		$equivalentRule = $this->dice->getRule('*');
		$equivalentRule['shared'] = true;
		
		$this->dice->expects($this->once())->method('addRule')->with($this->equalTo('*'), $this->equalTo($equivalentRule));
		$this->xmlLoader->load(simplexml_load_string($xml), $this->dice);
		
	}
	
	public function testShared() {
		$xml = '<?xml version="1.0"?>
<dice xmlns="https://r.je/dice/2.0">
	<rule name="A" shared="true">
		<name>A</name>
		<shared>true</shared>
	</rule>
</dice>';
		
		
		$equivalentRule = $this->dice->getRule('*');
		$equivalentRule['shared'] = true;
		
		$this->dice->expects($this->once())->method('addRule')->with($this->equalTo('A'), $this->equalTo($equivalentRule));
		$this->xmlLoader->load(simplexml_load_string($xml), $this->dice);
	}
	
	
	
	public function testConstructParams() {
		$xml = '<?xml version="1.0"?>
<dice xmlns="https://r.je/dice/2.0">
	<rule name="A">
		<constructParams>
			<param>A</param>
			<param>B</param>
		</constructParams>
	</rule>
</dice>';
	
	
		$equivalentRule = [];
		$equivalentRule['constructParams'][] = 'A';
		$equivalentRule['constructParams'][] = 'B';
	
		$this->dice->expects($this->once())->method('addRule')->with($this->equalTo('A'), $this->equalTo($equivalentRule));
		$this->xmlLoader->load(simplexml_load_string($xml), $this->dice);
	}
	
	
	public function testSubstitutions() {
		$xml = '<?xml version="1.0"?>
<dice xmlns="https://r.je/dice/2.0">
	<rule name="A">
		<name>A</name>
		<substitute use="C" as="B" />
	</rule>
</dice>';
	
	
		$equivalentRule = [];
		$equivalentRule['substitutions']['B'] = ['instance' => 'C'];
		
		$this->dice->expects($this->once())->method('addRule')->with($this->equalTo('A'), $this->equalTo($equivalentRule));
		$this->xmlLoader->load(simplexml_load_string($xml), $this->dice);
	}
	
	
	public function testSubstitutions2() {
		$xml = '<?xml version="1.0"?>
<dice xmlns="https://r.je/dice/2.0">
	<rule name="A">
		<substitute use="C" as="B" />
		<substitute use="E" as="F" />
	</rule>
</dice>';
	
	
		$equivalentRule = $this->dice->getRule('*');
		$equivalentRule['substitutions']['B'] = ['instance' => 'C'];
		$equivalentRule['substitutions']['F'] = ['instance' => 'E'];
	
	
		$this->dice->expects($this->once())->method('addRule')->with($this->equalTo('A'), $this->equalTo($equivalentRule));
		$this->xmlLoader->load(simplexml_load_string($xml), $this->dice);
	}
	
	
	public function testInstanceOf() {
		$xml = '<?xml version="1.0"?>
<dice xmlns="https://r.je/dice/2.0">
	<rule name="[C]" instanceOf="C" />
</dice>';
	
		$equivalentRule = $this->dice->getRule('*');
		$equivalentRule['instanceOf'] = 'C';
	
		$this->dice->expects($this->once())->method('addRule')->with($this->equalTo('[C]'), $this->equalTo($equivalentRule));
		$this->xmlLoader->load(simplexml_load_string($xml), $this->dice);
	}
	
	
	public function testCall() {
		$xml = '<?xml version="1.0"?>
<dice xmlns="https://r.je/dice/2.0">
	<rule name="A">
		<call method="setFoo">
            <param>Foo</param> 
            <param>Bar</param> 
		</call> 
	</rule>
</dice>';
	
		$equivalentRule = $this->dice->getRule('*');
		$equivalentRule['call'][] = ['setFoo', ['Foo', 'Bar']];
	
		$this->dice->expects($this->once())->method('addRule')->with($this->equalTo('A'), $this->equalTo($equivalentRule));
		$this->xmlLoader->load(simplexml_load_string($xml), $this->dice);
	}
	
	

	public function testInherit() {
		$xml = '<?xml version="1.0"?>
<dice xmlns="https://r.je/dice/2.0">
	<rule name="A" inherit="true" />
</dice>';
	
		$equivalentRule = $this->dice->getRule('*');
		$equivalentRule['inherit'] = true;
	
		$this->dice->expects($this->once())->method('addRule')->with($this->equalTo('A'), $this->equalTo($equivalentRule));
		$this->xmlLoader->load(simplexml_load_string($xml), $this->dice);
	}
	
	
	public function testInherit2() {
		$xml = '<?xml version="1.0"?>
<dice xmlns="https://r.je/dice/2.0">
	<rule name="A" inherit="false" />
</dice>';
	
		$equivalentRule = $this->dice->getRule('*');
		$equivalentRule['inherit'] = false;
	
		$this->dice->expects($this->once())->method('addRule')->with($this->equalTo('A'), $this->equalTo($equivalentRule));
		$this->xmlLoader->load(simplexml_load_string($xml), $this->dice);
	}
	
	public function testShareInstance() {
		$xml = '<?xml version="1.0"?>
<dice xmlns="https://r.je/dice/2.0">
	<rule name="A">
		<shareInstances>
			<instance>C</instance>
			<instance>D</instance>
		</shareInstances>
		
	</rule>
</dice>';
	
		$equivalentRule = [];
		$equivalentRule['shareInstances'] = ['C', 'D'];
		
		$this->dice->expects($this->once())->method('addRule')->with($this->equalTo('A'), $this->equalTo($equivalentRule));
		$this->xmlLoader->load(simplexml_load_string($xml), $this->dice);
	}
	
	
}
