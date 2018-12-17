<?php

// Copyright 2018 Max Sprauer

abstract class Character
{
    public $x, $y;
    public $power = 3;
    public $hitPoints = 200;

    public function __construct($x, $y)
    {
        $this->x = $x;
        $this->y = $y;
    }

    abstract function getDescription();

    function inRangeOf($char)
    {
        return (abs($this->x - $char->x) == 1 xor abs($this->y - $char->y) == 1);
    }

}

class Elf extends Character
{
    public function getDescription()
    {
        return "E({$this->hitPoints})";
    }
}

class Goblin extends Character
{
    public function getDescription()
    {
        return "G({$this->hitPoints})";
    }
}
