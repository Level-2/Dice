<?php 
/* @description 		Dice - A minimal Dependency Injection Container for PHP
 * @author				Tom Butler tom@r.je
* @copyright			2012-2014 Tom Butler <tom@r.je>
* @link					http://r.je/dice.html
* @license				http://www.opensource.org/licenses/bsd-license.php  BSD License
* @version				1.1.1
*/
namespace Dice\Loader;

class Json {
	private function getComponent($str, $createInstance = false) {
		if ($createInstance) return (strpos((string) $str, '{') === 0) ? array(new Callback($str), 'create') : new \Dice\Instance((string) $str);
		else return (strpos((string) $str, '{') === 0) ? array(new Callback($str), 'create') : (string) $str;
	}

	public function load($json, \Dice\Dice $dic) {
		$map = json_decode($json);
		if (!is_object($map)) throw new \Exception('Could not decode josn: ' . json_last_error_msg());
		$rules = array();
		foreach ($map->rules as $value) {
			$rule = clone $dic->getRule((string) $value->name);
			if (isset($value->shared)) $rule->shared = $value->shared;
			if (isset($value->inherit)) $rule->inherit = $value->inherit;						
			if (isset($value->call)) foreach ($value->call as $call) $rule->call[] = $call;
			if (isset($value->instanceof)) $rule->instanceOf = $value->instanceof;
			if (isset($value->newinstances)) foreach ($value->newinstances as $ni) $rule->newInstances[] =  $ni;
			if (isset($value->substitute)) foreach ($value->substitute as $as => $use) $rule->substitutions[$as] = $this->getComponent($use, true);
			if (isset($value->construct)) 	foreach ($value->construct as $child) $rule->constructParams[] = $this->getComponent((string) $child);
			if (isset($value->shareinstances)) foreach ($value->shareinstances as $share) $rule->shareInstances[] = $this->getComponent((string) $share, false);
			$dic->addRule($value->name, $rule);
		}
	}
}
