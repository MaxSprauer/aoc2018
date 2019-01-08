<?php

// Copyright 2018 Max Sprauer

class Coord 
{
    public $x, $y;

    public function __construct($x, $y)
    {
        $this->x = $x;
        $this->y = $y;
    }

    public function __toString()
    {
        return "$x,$y";
    }

    public function equals(Coord $b)
    {
        return $this->x == $b->x && $this->y == $b->y;
    }
}

abstract class Character extends Coord
{
    public $power = 3;
    public $hitPoints = 200;

    abstract function getDescription();

    public function inRangeOf($char)
    {
        return (abs($this->x - $char->x) == 1 xor abs($this->y - $char->y) == 1);
    }

    public function ordinal($width)
    {
        return ($this->y * $width) + $this->x;
    }

    public function getMovesToCoord($x, $y)
    {
        return (abs($this->x - $x) + abs($this->y - $y));
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
