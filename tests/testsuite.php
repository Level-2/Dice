<?php
require_once 'dicetest.php';
require_once 'loaders/jsonloadertest.php';
require_once 'loaders/xmlloadertest.php';
require_once 'loaders/callbacktest.php';
/**
/**
 * Static test suite.
 */
class DiceTestSuite extends PHPUnit_Framework_TestSuite {
	
	/**
	 * Constructs the test suite handler.
	 */
	public function __construct() {
		$this->setName('DiceTestSuite');		
		$this->addTestSuite('DiceTest');		
		$this->addTestSuite('JsonLoaderTest');		
		$this->addTestSuite('XmlLoaderTest');
		$this->addTestSuite('CallbackTest');
	}
	
	/**
	 * Creates the suite.
	 */
	public static function suite() {
		return new self ();
	}
}

