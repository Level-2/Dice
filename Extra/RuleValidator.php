<?php
namespace Dice\Extra;
class RuleValidator {
	private $dice;

	public function __construct(\Dice\Dice $dice) {
		$this->dice = $dice;
	}

	public function addRule($name, array $rule) {
		$this->checkValidKeys($rule);
		$this->checkBoolean($rule, 'inherit');
		$this->checkBoolean($rule, 'shared');
		$this->checkNumericArray($rule, 'constructParams');
		$this->checkNumericArray($rule, 'shareInstances');
		$this->checkNumericArray($rule, 'call');
		$this->dice->addRule($name, $rule);
	}

	private function checkValidKeys($rule) {
		$validKeys = ['call', 'shared', 'substitutions', 'instanceOf', 'inherit', 'shareInstances', 'constructParams'];
		foreach ($rule as $name => $value) {
			if (!in_array($name, $validKeys)) throw new \InvalidArgumentException('Invalid rule option: '. $name);
		}
	}

	public function create($name, array $args = [], array $share = []) {
		return $this->dice->create($name, $args, $share);
	}

	public function checkBoolean($rule, $key) {
		if (!isset($rule[$key])) return;

		if (!is_bool($rule[$key])) throw new \InvalidArgumentException('Rule option ' . $key . ' must be true or false');
	}

	public function checkNumericArray($rule, $key) {
		if (!isset($rule[$key])) return;

		if (count(array_filter(array_keys($rule[$key]), 'is_string')) > 0) throw new \InvalidArgumentException('Rule option ' . $key . ' must be a seqential array not an associative array');

	}

	public function checkAssocArray($rule, $key) {
		if (!isset($rule[$key])) return;

		if (count(array_filter(array_keys($rule[$key]), 'is_string')) === 0) throw new \InvalidArgumentException('Rule option ' . $key . ' must be a an associative array');

	}

}