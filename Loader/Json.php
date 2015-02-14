<?php 
/* @description     Dice - A minimal Dependency Injection Container for PHP         *  
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2012-2015 Tom Butler <tom@r.je> | http://r.je/dice.html         *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.3.2                                                           */
namespace Dice\Loader;
class Json {
	private function getComponent($input) {
		if (is_array($input)) foreach ($input as &$value) $value = $this->getComponent($value);
		
		if (is_object($input) && isset($input->instance)) return new \Dice\Instance($input->instance);
		else if (is_object($input) && isset($input->call)) return new \Dice\Instance([new Callback($input->call), 'run']);
		else return $input;
	}

	public function load($json, \Dice\Dice $dice = null) {
		if ($dice === null) $dice = new \Dice\Dice;
		$map = json_decode($json);
		if (!is_object($map)) throw new \Exception('Could not decode json: ' . json_last_error_msg());
		$rules = [];
		foreach ($map->rules as $value) {
			$rule = clone $dice->getRule($value->name);
			if (isset($value->shared)) $rule->shared = $value->shared;
			if (isset($value->inherit)) $rule->inherit = $value->inherit;						
			if (isset($value->call)) foreach ($value->call as $call) $rule->call[] = $this->getComponent($call);
			if (isset($value->instanceof)) $rule->instanceOf = $value->instanceof;
			if (isset($value->newinstances)) foreach ($value->newinstances as $ni) $rule->newInstances[] =  $ni;
			if (isset($value->substitute)) foreach ($value->substitute as $as => $use) $rule->substitutions[$as] = $this->getComponent($use, true);
			if (isset($value->construct)) 	foreach ($value->construct as $child) $rule->constructParams[] = $this->getComponent($child);
			if (isset($value->shareinstances)) foreach ($value->shareinstances as $share) $rule->shareInstances[] = $this->getComponent($share, false);
			$dice->addRule($value->name, $rule);
		}
		return $dice;
	}
}