<?php

// Copyright 2018 Max Sprauer

class Coord 
{
    public $x, $y;
    static $cache = array();

    public function __construct($x, $y)
    {
        $this->x = $x;
        $this->y = $y;
    }

    public static function NewFromCoords($x, $y)
    {
        if (!isset(self::$cache["$x,$y"])) {
            self::$cache["$x,$y"] = new Coord($x, $y);
        }

        return self::$cache["$x,$y"];
    }

    public static function NewFromString($str)
    {
        if (!isset(self::$cache[$str])) {
            list($x, $y) = explode(',', $str);
            self::$cache[$str] = new Coord($x, $y);
        }

        return self::$cache[$str];
    }

    public function __toString()
    {
        return "$this->x,$this->y";
    }

    public function equals(Coord $b)
    {
        return $this->x == $b->x && $this->y == $b->y;
    }

    public function ordinal($width)
    {
        return ($this->y * $width) + $this->x;
    }
}

abstract class Character extends Coord
{
    public $power = 3;
    public $hitPoints = 200;
    public $id;
    static $currentId = 1;

    abstract function getDescription();

    public function __construct($x, $y)
    {
        $this->id = self::$currentId++;
        parent::__construct($x, $y);
    }

    public function inRangeOf(Character &$char)
    {
        return (($this->y == $char->y && abs($this->x - $char->x) == 1)
            || ($this->x == $char->x && abs($this->y - $char->y) == 1));
    }

    public function getMovesToCoord(Coord &$c)
    {
        return (abs($this->x - $c->x) + abs($this->y - $c->y));
    }
}

class Elf extends Character
{
    public function getDescription()
    {
        return "E{$this->id}({$this->hitPoints})";
    }
}

class Goblin extends Character
{
    public function getDescription()
    {
        return "G{$this->id}({$this->hitPoints})";
    }
}
