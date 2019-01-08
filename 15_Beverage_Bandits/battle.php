<?php

// Copyright 2018 Max Sprauer

require_once 'Map.class.php';
require_once 'Character.class.php';

//test_getReachableForCoord();
test_BFS();

function test_getReachableForCoord()
{
    $map = new Map('input.txt');
    $map->createCharacters();
    $map->print();
    
    print_r($map->chars);
    
    $r = array();
    $map->getReachableForCoord(new Coord(12, 2), $r);
    assert(count($r) == 349, count($r));
}

function test_BFS()
{
    $map = new Map('ex3.txt');
    $map->createCharacters();
    $map->print();
    
    $paths = $map->BFS(new Elf(2, 1), new Goblin(4, 3));
    print_r($paths);
}