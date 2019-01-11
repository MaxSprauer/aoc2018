<?php

// Copyright 2018 Max Sprauer

ini_set('xdebug.max_nesting_level', 1024);

define('PART_TWO', ($argc > 1));
define('ELF_POINTS', ($argc > 1) ? $argv[1] : 3);

require_once 'Map.class.php';
require_once 'Character.class.php';

//test_getReachableForCoord();
//test_BFS_ex3();
//test_BFS_ex2();
//test_movement();
//test_attack();
//test_combat(5);
partOne();


function test_combat($n)
{
    $map = new Map("combat{$n}.txt");
    $map->print();

    $done = false;
    $i = 1;
    do {
        $done = $map->doRound();
        $map->print($i++);
    } while (!$done);

}

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

function test_attack()
{
    $map = new Map('ex2.txt');
    $map->print();

    for ($i = 1; $i <= 48; $i++) {
        $map->doRound();
        $map->print($i);
    }
}

function partOne()
{
    $map = new Map('input.txt');
    $map->print();

    $i = 1;
    $done = false;

    do {
        $done = $map->doRound();
        $map->print($i++);
    } while (!$done);

}
