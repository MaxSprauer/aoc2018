<?php

// Copyright 2018 Max Sprauer

require_once 'Map.class.php';
require_once 'Character.class.php';

//test_getReachableForCoord();
test_BFS_ex3();
test_BFS_ex2();
test_movement();

function test_movement()
{
    $map = new Map('ex1.txt');
    $map->print();
    
    // print_r($map->chars);

    $start = time();
    for ($count = 0; $count < 3; $count++) {
        $map->doRound();  
        $map->print();  
    }
    print "Time: " . (time() - $start) . "\n";
}

function test_getReachableForCoord()
{
    $map = new Map('input.txt');
    $map->print();
    
    print_r($map->chars);
    
    $r = array();
    $map->getReachableForCoord(new Coord(12, 2), $r);
    assert(count($r) == 349, count($r));
}

function test_BFS_ex3()
{
    $map = new Map('ex3.txt');
    $map->print();
    
    $paths = $map->BFS(new Elf(2, 1), new Goblin(4, 3));
    $map->printPaths($paths);
}

function test_BFS_ex2()
{
    $map = new Map('ex2.txt');
    $map->print();
    
    $paths = $map->BFS(new Elf(5, 4), new Goblin(2, 1));
    $map->printPaths($paths);
}
