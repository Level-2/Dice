<?php

interface GlobalInterface
{

}

class GlobalImplementation implements GlobalInterface
{

}

class AnotherGlobalImplementation implements GlobalInterface
{

}

interface GlobalRuleInterface
{

}

class GlobalRuleImplementation implements GlobalRuleInterface
{

}

class Global1
{
    public $obj;
    public function __construct(GlobalInterface $obj)
    {
        $this->obj = $obj;
    }
}

class Global2
{
    public $std;
    public $obj;
    public function __construct(stdClass $std, GlobalInterface $obj)
    {
        $this->std = $std;
        $this->obj = $obj;
    }
}

class Global3
{
    public $obj;
    public $std;
    public function __construct(GlobalInterface $obj, stdClass $std)
    {
        $this->obj = $obj;
        $this->std = $std;
    }
}

class Global4
{
    public $glb;
    public $glbr;
    public function __construct(GlobalInterface $glb, GlobalRuleInterface $glbr)
    {
        $this->glb = $glb;
        $this->glbr = $glbr;
    }
}
