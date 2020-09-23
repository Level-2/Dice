<?php


namespace Dice\tests\TestData;


class SharedInstanceFactory
{

    public $shInst = null;

    public function factory():SharedInstance
    {
        if (!$this->shInst)
            $this->shInst = new SharedInstance();
        return $this->shInst;
    }

}

interface SharedInstanceInterface
{
}

class SharedInstance implements SharedInstanceInterface
{

}