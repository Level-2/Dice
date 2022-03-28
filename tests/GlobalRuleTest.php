<?php
/* @description Dice - A minimal Dependency Injection Container for PHP *
 * @author Tom Butler tom@r.je *
 * @copyright 2012-2018 Tom Butler <tom@r.je> | https:// r.je/dice.html *
 * @license http:// www.opensource.org/licenses/bsd-license.php BSD License *
 * @version 3.0 */
class GlobalRuleTest extends DiceTest
{
    public function testGlobal1()
    {
		$rule = [
			'substitutions' => ['GlobalInterface' => 'GlobalImplementation']
		];
		$dice = $this->dice->addRule('*', $rule);

		$obj = $dice->create('Global1');

		$this->assertInstanceOf('GlobalImplementation', $obj->obj);
    }

    public function testGlobal2()
    {
		$rule = [
			'substitutions' => ['GlobalInterface' => 'GlobalImplementation']
		];
		$dice = $this->dice->addRule('*', $rule);

		$obj = $dice->create('Global2');

		$this->assertInstanceOf('GlobalImplementation', $obj->obj);
    }

    public function testGlobal3()
    {
		$rule = [
			'substitutions' => ['GlobalInterface' => 'GlobalImplementation']
		];
		$dice = $this->dice->addRule('*', $rule);

		$obj = $dice->create('Global3');

		$this->assertInstanceOf('GlobalImplementation', $obj->obj);
    }

    public function testGlobal4()
    {
		$rule = [
            'substitutions' => [
                'GlobalRuleInterface' => 'GlobalRuleImplementation',
                'GlobalInterface' => 'GlobalImplementation'
            ]
		];
		$dice = $this->dice->addRule('*', $rule);

		$obj = $dice->create('Global4');

        $this->assertInstanceOf('GlobalImplementation', $obj->glb);
        $this->assertInstanceOf('GlobalRuleImplementation', $obj->glbr);
    }

    public function testGlobal4_1()
    {
		$glbRule = [
            'substitutions' => ['GlobalInterface' => 'GlobalImplementation']
        ];
        $glb4Rule = [
            'substitutions' => ['GlobalRuleInterface' => 'GlobalRuleImplementation']
		];
		$dice = $this->dice->addRule('*', $glbRule);
        $dice = $dice->addRule('Global4', $glb4Rule);

		$obj = $dice->create('Global4');

        $this->assertInstanceOf('GlobalImplementation', $obj->glb);
        $this->assertInstanceOf('GlobalRuleImplementation', $obj->glbr);
    }

    public function testGlobal4_2()
    {
		$glbRule = [
            'substitutions' => ['GlobalRuleInterface' => 'GlobalRuleImplementation']
        ];
        $glb4Rule = [
            'substitutions' => ['GlobalInterface' => 'GlobalImplementation']
		];
		$dice = $this->dice->addRule('*', $glbRule);
        $dice = $dice->addRule('Global4', $glb4Rule);

		$obj = $dice->create('Global4');

        $this->assertInstanceOf('GlobalImplementation', $obj->glb);
        $this->assertInstanceOf('GlobalRuleImplementation', $obj->glbr);
    }

    public function testGlobal4_3()
    {
		$glbRule = [
            'substitutions' => ['GlobalInterface' => 'GlobalImplementation']
        ];
        $glb4Rule = [
            'substitutions' => [
                'GlobalInterface' => 'AnotherGlobalImplementation',
                'GlobalRuleInterface' => 'GlobalRuleImplementation'
            ]
		];
		$dice = $this->dice->addRule('*', $glbRule);
        $dice = $dice->addRule('Global4', $glb4Rule);

		$obj = $dice->create('Global4');

        $this->assertInstanceOf('AnotherGlobalImplementation', $obj->glb);
        $this->assertInstanceOf('GlobalRuleImplementation', $obj->glbr);
    }

    public function testGlobal4_4()
    {
		$glbRule = [
            'substitutions' => ['GlobalRuleInterface' => 'GlobalRuleImplementation']
        ];
        $glb4Rule = [
            'substitutions' => ['GlobalInterface' => 'GlobalImplementation']
		];
        $dice = $this->dice->addRule('Global4', $glb4Rule);
		$dice = $dice->addRule('*', $glbRule);

		$obj = $dice->create('Global4');

        $this->assertInstanceOf('GlobalImplementation', $obj->glb);
        $this->assertInstanceOf('GlobalRuleImplementation', $obj->glbr);
    }

    public function testGlobal4_5()
    {
		$glbRule = [
            'substitutions' => ['GlobalInterface' => 'GlobalImplementation']
        ];
        $glb4Rule = [
            'substitutions' => [
                'GlobalInterface' => 'AnotherGlobalImplementation',
                'GlobalRuleInterface' => 'GlobalRuleImplementation'
            ]
		];
        $dice = $this->dice->addRule('Global4', $glb4Rule);
		$dice = $dice->addRule('*', $glbRule);

		$obj = $dice->create('Global4');

        $this->assertInstanceOf('AnotherGlobalImplementation', $obj->glb);
        $this->assertInstanceOf('GlobalRuleImplementation', $obj->glbr);
    }

    public function testNoSubstitutionKey()
    {
		$rule = [
			'substitutions' => ['GlobalInterface' => 'GlobalImplementation']
        ];
        $dice = $this->dice->addRule('Global3', ['shared'=> true]);
		$dice = $dice->addRule('*', $rule);

		$obj = $dice->create('Global3');

		$this->assertInstanceOf('GlobalImplementation', $obj->obj);
    }
}
