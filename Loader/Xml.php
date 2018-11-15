<?php
/* @description Dice - A minimal Dependency Injection Container for PHP *
 * @author Tom Butler tom@r.je *
 * @copyright 2012-2018 Tom Butler <tom@r.je> | https:// r.je/dice.html *
 * @license http:// www.opensource.org/licenses/bsd-license.php BSD License *
 * @version 3.0 */
namespace Dice\Loader;
class Xml {
	private function getComponent(\SimpleXmlElement $element, $forceInstance = false) {
		if ($forceInstance) return [\Dice\Dice::INSTANCE => (string) $element];
		else if ($element->instance) return [\Dice\Dice::INSTANCE => (string) $element->instance];
		else return (string) $element;
	}

	private function loadV1(\SimpleXmlElement $xml, \Dice\Dice $dice) {
		$rules = [];

		foreach ($xml as $key => $value) {
			$rule = $dice->getRule((string) $value->name);

			if (isset($value->shared)) $rule['shared'] = ((string) $value->shared === 'true');

			if (isset($value->inherit)) $rule['inherit'] = ($value->inherit == 'false') ? false : true;
			if ($value->call) {
				foreach ($value->call as $name => $call) {
					$callArgs = [];
					if ($call->params) 	foreach ($call->params->children() as $key => $param) 	$callArgs[] = $this->getComponent($param);
					$rule['call'][] = [(string) $call->method, $callArgs];
				}
			}
			if ($value->instanceOf) $rule['instanceOf'] = (string) $value->instanceOf;
			if ($value->newInstances) foreach ($value->newInstances as $ni) $rule['newInstances'][] = (string) $ni;
			if ($value->substitutions) foreach ($value->substitutions as $use) 	$rule['substitutions'][(string) $use->as] = $this->getComponent($use->use, true);
			if ($value->constructParams) foreach ($value->constructParams->children() as $child) $rule['constructParams'][] = $this->getComponent($child);
			if ($value->shareInstances) foreach ($value->shareInstances as $share) $rule['shareInstances'][] = $this->getComponent($share);
			$rules[$value['name']] = $rule;
			$dice->addRule((string) $value->name, $rule);
		}
		return $rules;
	}

	private function loadV2(\SimpleXmlElement $xml, \Dice\Dice $dice) {
		$rules = [];

		foreach ($xml as $key => $value) {
			$rule = $dice->getRule((string) $value->name);

			if ($value->call) {
				foreach ($value->call as $name => $call) {
					$callArgs = [];
					foreach ($call->children() as $key => $param) 	$callArgs[] = $this->getComponent($param);
					$rule['call'][] = [(string) $call['method'], $callArgs];
				}
			}
			if (isset($value['inherit'])) $rule['inherit'] = ($value['inherit'] == 'false') ? false : true;
			if ($value['instanceOf']) $rule['instanceOf'] = (string) $value['instanceOf'];
			if (isset($value['shared'])) $rule['shared'] = ((string) $value['shared'] === 'true');
			if ($value->constructParams) foreach ($value->constructParams->children() as $child) $rule['constructParams'][] = $this->getComponent($child);
			if ($value->substitute) foreach ($value->substitute as $use) $rule['substitutions'][(string) $use['as']] = $this->getComponent($use['use'], true);
			if ($value->shareInstances) foreach ($value->shareInstances->children() as $share) $rule['shareInstances'][] = $this->getComponent($share);
			$rules[$value['name']] = $rule;
			$dice->addRule((string) $value['name'], $rule);
		}
		return $rules;
	}

	public function load($xml, \Dice\Dice $dice = null, $displayWarning = true) {

		if ($displayWarning) {
			trigger_error('Deprecated: The XML loader is being removed in the next version of Dice please use $xmlLoader->convert(\'' . $xml . '\', \'path/to/rules.json\'); to convert the rules to JSON format', E_USER_WARNING);
		}

		if ($dice === null) $dice = new \Dice\Dice;
		if (!($xml instanceof \SimpleXmlElement)) $xml = simplexml_load_file($xml);
		$ns = $xml->getNamespaces();
		$nsName = (isset($ns[''])) ? $ns[''] : '';

		if ($nsName == 'https://r.je/dice/2.0') return $this->loadV2($xml, $dice);
		else return $this->loadV1($xml, $dice);
	}

	public function convert($xml, $outputJson) {
		$rules = $this->load($xml);

		file_put_contents($outputJson, json_encode($rules, JSON_PRETTY_PRINT));
	}
}
