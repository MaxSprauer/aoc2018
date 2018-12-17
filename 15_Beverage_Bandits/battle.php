<?php

// Copyright 2018 Max Sprauer

require_once 'Map.class.php';
require_once 'Character.class.php';

$map = new Map('input.txt');
$map->createCharacters();
$map->print();

print_r($map->chars);

$map->getReachableFor(9, 18, $r);
// assert(count($r) == 72, count($r));
print_r($r);