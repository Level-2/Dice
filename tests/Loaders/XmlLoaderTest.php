<?php
/* @description     Dice - A minimal Dependency Injection Container for PHP         *  
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2012-2015 Tom Butler <tom@r.je> | http://r.je/dice.html         *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.3.2                                                           */
require_once 'Dice.php';
require_once 'Loader/Xml.php';
require_once 'Loader/Callback.php';


class XmlLoaderTest extends PHPUnit_Framework_TestCase {
	private $dice;
	private $xmlLoader;

	protected function setUp() {
		parent::setUp ();
		$this->dice = $this->getMock('\\Dice\\Dice', array('getRule', 'addRule'));		
		$this->dice->expects($this->any())->method('getRule')->will($this->returnValue(new \Dice\Rule));
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
		
	
		$equivalentRule = new \Dice\Rule;
		$equivalentRule->shared = true;
		
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
		
		
		$equivalentRule = new \Dice\Rule;
		$equivalentRule->shared = true;
		
		$this->dice->expects($this->once())->method('addRule')->with($this->equalTo('A'), $this->equalTo($equivalentRule));
		$this->xmlLoader->load(simplexml_load_string($xml), $this->dice);
	}
	
	
	
	public function testConstructParams() {
		$xml = '<?xml version="1.0"?>
<dice>
	<rule>
		<name>A</name>
		<construct>
			<param>A</param>
			<param>B</param>
		</construct>
	</rule>
</dice>';
	
	
		$equivalentRule = new \Dice\Rule;
		$equivalentRule->constructParams[] = 'A';
		$equivalentRule->constructParams[] = 'B';
	
		$this->dice->expects($this->once())->method('addRule')->with($this->equalTo('A'), $this->equalTo($equivalentRule));
		$this->xmlLoader->load(simplexml_load_string($xml), $this->dice);
	}
	
	
	public function testSubstitutions() {
		$xml = '<?xml version="1.0"?>
<dice>
	<rule>
		<name>A</name>
		<substitute>
			<use>C</use>
			<as>B</as>
		</substitute>
	</rule>
</dice>';
	
	
		$equivalentRule = new \Dice\Rule;
		$equivalentRule->substitutions['B'] = new \Dice\Instance('C');
		
	
		$this->dice->expects($this->once())->method('addRule')->with($this->equalTo('A'), $this->equalTo($equivalentRule));
		$this->xmlLoader->load(simplexml_load_string($xml), $this->dice);
	}
	
	
	public function testSubstitutions2() {
		$xml = '<?xml version="1.0"?>
<dice>
	<rule>
		<name>A</name>
		<substitute>
			<use>C</use>
			<as>B</as>
		</substitute>
				
		<substitute>
			<use>E</use>
			<as>F</as>
		</substitute>
	</rule>
</dice>';
	
	
		$equivalentRule = new \Dice\Rule;
		$equivalentRule->substitutions['B'] = new \Dice\Instance('C');
		$equivalentRule->substitutions['F'] = new \Dice\Instance('E');
	
	
		$this->dice->expects($this->once())->method('addRule')->with($this->equalTo('A'), $this->equalTo($equivalentRule));
		$this->xmlLoader->load(simplexml_load_string($xml), $this->dice);
	}
	
	
	public function testNewInstances() {
		$xml = '<?xml version="1.0"?>
<dice>
	<rule>
		<name>A</name>
		<newinstance>C</newinstance>
		<newinstance>D</newinstance>
		<newinstance>E</newinstance>
	</rule>
</dice>';
		
		$equivalentRule = new \Dice\Rule;
		$equivalentRule->newInstances = ['C', 'D', 'E'];	
	
		$this->dice->expects($this->once())->method('addRule')->with($this->equalTo('A'), $this->equalTo($equivalentRule));
		$this->xmlLoader->load(simplexml_load_string($xml), $this->dice);
	}
	
	public function testInstanceOf() {
		$xml = '<?xml version="1.0"?>
<dice>
	<rule>
		<name>[C]</name>
		<instanceof>C</instanceof>
	</rule>
</dice>';
	
		$equivalentRule = new \Dice\Rule;
		$equivalentRule->instanceOf = 'C';
	
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
	
		$equivalentRule = new \Dice\Rule;
		$equivalentRule->call[] = ['setFoo', ['Foo', 'Bar']];
	
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
	
		$equivalentRule = new \Dice\Rule;
		$equivalentRule->inherit = true;
	
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
	
		$equivalentRule = new \Dice\Rule;
		$equivalentRule->inherit = false;
	
		$this->dice->expects($this->once())->method('addRule')->with($this->equalTo('A'), $this->equalTo($equivalentRule));
		$this->xmlLoader->load(simplexml_load_string($xml), $this->dice);
	}
	
	public function testShareInstance() {
		$xml = '<?xml version="1.0"?>
<dice>
	<rule>
		<name>A</name>
		<shareinstance>C</shareinstance>
		<shareinstance>D</shareinstance>
	</rule>
</dice>';
	
		$equivalentRule = new \Dice\Rule;
		$equivalentRule->shareInstances = ['C', 'D'];
		
		$this->dice->expects($this->once())->method('addRule')->with($this->equalTo('A'), $this->equalTo($equivalentRule));
		$this->xmlLoader->load(simplexml_load_string($xml), $this->dice);
	}
	
	
}
