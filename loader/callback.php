<?php 
/* @description 		Dice - A minimal Dependency Injection Container for PHP
 * @author				Tom Butler tom@r.je
* @copyright			2012-2014 Tom Butler <tom@r.je>
* @link				http://r.je/dice.html
* @license				http://www.opensource.org/licenses/bsd-license.php  BSD License
* @version				1.1.1
*/

namespace Dice\Loader;

class Callback {
	private $str;

	public function __construct($str) {
		$this->str = $str;
	}

	public function create(\Dice\Dice $dic) {
		$parts = explode('::', trim($this->str, '{}'));
		$object = $dic->create(array_shift($parts));
		while ($var = array_shift($parts)) {
			if (strpos($var, '(') !== false) {
				$args = explode(',', substr($var, strpos($var, '(')+1, strpos($var, ')')-strpos($var, '(')-1));
				$object = call_user_func_array(array($object, substr($var, 0, strpos($var, '('))), ($args[0] == null) ? array() : $args);
			}
			else $object = $object->$var;
		}
		return $object;
	}
}
