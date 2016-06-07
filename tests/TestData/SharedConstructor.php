<?php
/**
 * @author Kirill @Nemoden K. <kovalchuk@drom.ru>
 * $ Date: Tue 07 Jun 2016 11:31:59 AM VLAT $
 */

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
