<?php
namespace Dice;
use Interop\Container\ContainerInterface;
use Interop\Container\Exception\NotFoundException;

class Interop implements ContainerInterface {
    private $dice;

    public function __construct(Dice $dice) {
        $this->dice = $dice;
    }

    public function get($id) {
    	if ($this->has($id)) return $this->dice->create($id);
    	else throw new NotFoundException('Could not instantiate ' . $id);
    }

    public function has($id) {
    	return (class_exists($id) || $this->dice->getRule($id) != $this->dice->getRule('*'));
    }
}
