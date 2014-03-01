<?php 
/* @description 		Dice - A minimal Dependency Injection Container for PHP
 * @author				Tom Butler tom@r.je
* @copyright			2012-2014 Tom Butler <tom@r.je>
* @link					http://r.je/dice.html
* @license				http://www.opensource.org/licenses/bsd-license.php  BSD License
* @version				1.1.1
*/
namespace Dice\Loader;

class XML {
	private function getComponent($str, $createInstance = false) {
		if ($createInstance) return (strpos((string) $str, '{') === 0) ? array(new Callback($str), 'create') : new \Dice\Instance((string) $str);
		else return (strpos((string) $str, '{') === 0) ? array(new Callback($str), 'create') : (string) $str;
	}

	public function load($map, \Dice\Dice $dic) {
		if (!($map instanceof \SimpleXmlElement)) $map = simplexml_load_file($map);
		foreach ($map as $key => $value) {
			$rule = clone $dic->getRule((string) $value->name);
			$rule->shared = ($value->shared == 'true');
			$rule->inherit = ($value->inherit == 'false') ? false : true;
			if ($value->call) {
				foreach ($value->call as $name => $call) {
					$callArgs = array();
					if ($call->params) 	foreach ($call->params->children() as $key => $param) 	$callArgs[] = $this->getComponent((string) $param, ($key == 'instance'));
					$rule->call[] = array((string) $call->method, $callArgs);
				}
			}
			if ($value->instanceof) $rule->instanceOf = (string) $value->instanceof;
			if ($value->newinstance) foreach ($value->newinstance as $ni) $rule->newInstances[] = (string) $ni;
			if ($value->substitute) foreach ($value->substitute as $use) 	$rule->substitutions[(string) $use->as] = $this->getComponent((string) $use->use, true);
			if ($value->construct) 	foreach ($value->construct->children() as $child) $rule->constructParams[] = $this->getComponent((string) $child);
			if ($value->shareinstance) foreach ($value->shareinstance as $share) $rule->shareInstances[] = $this->getComponent((string) $share, false);
			$dic->addRule((string) $value->name, $rule);
		}
	}
}