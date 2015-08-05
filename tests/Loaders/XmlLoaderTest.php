<?php
/* @description     Dice - A minimal Dependency Injection Container for PHP         *  
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2012-2015 Tom Butler <tom@r.je> | http://r.je/dice.html         *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         20.                                                             */

require_once 'Dice.php';
require_once 'Loader/Xml.php';


class XmlLoaderTest extends PHPUnit_Framework_TestCase {
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
<dice>
	<rule>
		<name>*</name>
		<shared>true</shared>
	</rule>
</dice>';
		
	
		$equivalentRule = $this->dice->getRule('*');
		$equivalentRule['shared'] = true;
		
		$this->dice->expects($this->once())->method('addRule')->with($this->equalTo('*'), $this->equalTo($equivalentRule));
		$this->xmlLoader->load(simplexml_load_string($xml), $this->dice);
		
	}
	
	public function testShared() {
		$xml = '<?xml version="1.0"?>
<dice>
	<rule>
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
<dice>
	<rule>
		<name>A</name>
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
<dice>
	<rule>
		<name>A</name>
		<substitutions>
			<use>C</use>
			<as>B</as>
		</substitutions>
	</rule>
</dice>';
	
	
		$equivalentRule = [];
		$equivalentRule['substitutions']['B'] = ['instance' => 'C'];
		
		$this->dice->expects($this->once())->method('addRule')->with($this->equalTo('A'), $this->equalTo($equivalentRule));
		$this->xmlLoader->load(simplexml_load_string($xml), $this->dice);
	}
	
	
	public function testSubstitutions2() {
		$xml = '<?xml version="1.0"?>
<dice>
	<rule>
		<name>A</name>
		<substitutions>
			<use>C</use>
			<as>B</as>
		</substitutions>
				
		<substitutions>
			<use>E</use>
			<as>F</as>
		</substitutions>
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
<dice>
	<rule>
		<name>[C]</name>
		<instanceOf>C</instanceOf>
	</rule>
</dice>';
	
		$equivalentRule = $this->dice->getRule('*');
		$equivalentRule['instanceOf'] = 'C';
	
		$this->dice->expects($this->once())->method('addRule')->with($this->equalTo('[C]'), $this->equalTo($equivalentRule));
		$this->xmlLoader->load(simplexml_load_string($xml), $this->dice);
	}
	
	
	public function testCall() {
		$xml = '<?xml version="1.0"?>
<dice>
	<rule>
		<name>A</name>
		<call>
			<method>setFoo</method> 
           <params> 
                <param>Foo</param> 
                <param>Bar</param> 
            </params>
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
<dice>
	<rule>
		<name>A</name>
		<inherit>true</inherit>
	</rule>
</dice>';
	
		$equivalentRule = $this->dice->getRule('*');
		$equivalentRule['inherit'] = true;
	
		$this->dice->expects($this->once())->method('addRule')->with($this->equalTo('A'), $this->equalTo($equivalentRule));
		$this->xmlLoader->load(simplexml_load_string($xml), $this->dice);
	}
	
	
	public function testInherit2() {
		$xml = '<?xml version="1.0"?>
<dice>
	<rule>
		<name>A</name>
		<inherit>false</inherit>
	</rule>
</dice>';
	
		$equivalentRule = $this->dice->getRule('*');
		$equivalentRule['inherit'] = false;
	
		$this->dice->expects($this->once())->method('addRule')->with($this->equalTo('A'), $this->equalTo($equivalentRule));
		$this->xmlLoader->load(simplexml_load_string($xml), $this->dice);
	}
	
	public function testShareInstance() {
		$xml = '<?xml version="1.0"?>
<dice>
	<rule>
		<name>A</name>
		<shareInstances>C</shareInstances>
		<shareInstances>D</shareInstances>
	</rule>
</dice>';
	
		$equivalentRule = [];
		$equivalentRule['shareInstances'] = ['C', 'D'];
		
		$this->dice->expects($this->once())->method('addRule')->with($this->equalTo('A'), $this->equalTo($equivalentRule));
		$this->xmlLoader->load(simplexml_load_string($xml), $this->dice);
	}
	
	
}
