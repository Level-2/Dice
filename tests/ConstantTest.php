<?php
/*@description        Dice - A minimal Dependency Injection Container for PHP
* @author             Tom Butler tom@r.je
* @copyright          2012-2015 Tom Butler <tom@r.je>
* @link               http://r.je/dice.html
* @license            http://www.opensource.org/licenses/bsd-license.php  BSD License
* @version            2.0
*/
class ConstantTest extends DiceTest {
	public function testConstructorConstant() {
		$this->dice->addRule('HasConstructorArgs', ['constructParams' => [
				[\Dice\Dice::CONSTANT => '\PDO::ATTR_EMULATE_PREPARES']
		]]);

		$obj = $this->dice->create('HasConstructorArgs');

		$this->assertEquals($obj->arg, \PDO::ATTR_EMULATE_PREPARES);
	}
}