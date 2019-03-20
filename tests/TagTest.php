<?php

use Dice\Dice;

class TagTest extends DiceTest {

    public function testNonexistentTag() {
        $objects = $this->dice->getAll( 'a tag' );
        $this->assertCount( 0, $objects );
    }

    public function testExistentTag() {
        $dice = $this->dice->addRules(
            [
                A::class => [ 'tag' => 'ABC' ],
                B::class => [ 'tag' => 'ABC' ],
                C::class => [ 'tag' => 'ABC' ]
            ]
        );

        $objects = iterator_to_array($dice->getAll('ABC'));

        $this->assertCount(3, $objects );
        $this->assertInstanceOf(A::class, $objects[0]);
        $this->assertInstanceOf(B::class, $objects[1]);
        $this->assertInstanceOf(C::class, $objects[2]);
    }

	public function testTagAddedTwice(){
		$dice = $this->dice->addRules(
			[
				A::class => [ 'tag' => 'AB' ],
				B::class => [ 'tag' => 'AB' ],
			]
		);

		$dice = $dice->addRule(B::class, [ 'tag' => 'AB' ]);

		$objects = iterator_to_array($dice->getAll('AB'));

		$this->assertCount(2, $objects );
		$this->assertInstanceOf(A::class, $objects[0]);
		$this->assertInstanceOf(B::class, $objects[1]);
	}

	public function testTagParameter(){
		$dice = $this->dice->addRules(
			[
				TestDispatcher::class => [
					'constructParams' => [
						Dice::TAG => 'Test Listeners'
					]
				],
				EventListenerA::class => [ 'tag' => 'Test Listeners' ],
				EventListenerB::class => [ 'tag' => 'Test Listeners' ]
			]
		);

		$testListener = $dice->create(TestDispatcher::class);

		$this->assertCount(2, $testListener->listeners);
		$this->assertInstanceOf(EventListenerA::class, $testListener->listeners[0]);
		$this->assertInstanceOf(EventListenerB::class, $testListener->listeners[1]);
	}


}