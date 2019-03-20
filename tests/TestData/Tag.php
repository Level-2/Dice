<?php
class EventListenerA {};
class EventListenerB {};

class TestDispatcher {
	public $listeners;

	public function __construct(array $listeners){
		$this->listeners = $listeners;
	}
}