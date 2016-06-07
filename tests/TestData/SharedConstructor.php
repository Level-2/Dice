<?php
# First test-case. We are interested in getting data from cache if we created an object twice
# with same constructor params using Dice

namespace Test\SharedConstructor;

class Greeter
{
    protected $greeting;

    protected $cache = array();

    public function __construct($greeting = "Hi")
    {
        $this->greeting = $greeting;
    }

    public function greet($name)
    {
        if (!empty($this->cache[$name])) {
            return "From cache: " . $this->cache[$name];
        }
        $this->cache[$name] = sprintf("%s, %s!", $this->greeting, $name);
        return $this->cache[$name];
    }
}

# Second test case. We are interested in more complex sharedConstructor usage.
# Namely, we want constructor to receive objects and closures so we can test
# arguments are hashed correctly
class BarBazShared
{
    private $barBaz;
    private $clo;
    public $id;

    public function __construct(BarBaz $barBaz, \Closure $clo)
    {
        $this->barBaz = $barBaz;
        $this->clo = $clo;
        $this->id = microtime(); // should stay the same if instance is the same
    }

    public function getUid()
    {
    }

    public function getBarBazWrapped()
    {
        $clo = $this->clo;
        return $clo(sprintf(
            "Bar: %s, baz: %s",
            $this->barBaz->getBar()->value(),
            $this->barBaz->getBaz()->value()
        ));
    }
}

class BarBaz
{
    private $bar;
    private $baz;

    public function __construct(Bar $bar, Baz $baz)
    {
        $this->bar = $bar;
        $this->baz = $baz;
    }

    public function getBar()
    {
        return $this->bar;
    }

    public function getBaz()
    {
        return $this->baz;
    }
}

class Bar
{
    private $bar;

    public function __construct($bar)
    {
        $this->bar = $bar;
    }

    public function value()
    {
        return $this->bar;
    }
}

class Baz
{
    private $baz;

    public function __construct($baz)
    {
        $this->baz = $baz;
    }

    public function value()
    {
        return $this->baz;
    }
}
