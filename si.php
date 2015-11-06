<?php
include('Dice.php');

interface BI {};
interface CI {};

class A {
    public $b, $c;
    public function __construct(BI $b, CI $c) {
        $this->b = $b;
        $this->c = $c;
    }
}

class B implements BI{
    public $d;
    public function __construct(D $d) {
        $this->d = $d;
    }
}

class C implements CI{
    public $d;
    public function __construct(D $d) {
        $this->d = $d;
    }
}

class D {}

$rule = [
    'constructParams' => [
        ['instance' => 'B'],
        ['instance' => 'C'],
    ],
    'shareInstances' => ['D']
];

$dice = new Dice\Dice();
$dice->addRule('B', ['constructParams' => [
      ['instance' => 'D']
    ]
]);
$dice->addRule('A', $rule);



//Create an A object
$a = $dice->create('A');

var_dump($a);
/*
  class A#9 (2) {
  public $b =>
  class B#15 (1) {
    public $d =>
    class D#11 (0) {
    }
  }
  public $c =>
  class C#21 (1) {
    public $d =>
    class D#17 (0) {
    }
  }
}
*/