<?php

// Copyright 2018 Max Sprauer

$lines = file_get_contents('input.txt');
$lines = explode("\n", $lines);

// I don't know if this algorithm is the best, but since only 1 character can differ,
//  1) Sort, compare neighbors
//  2) Sort reversed strings, compare neighbors
// Is this supposed to build on the checksums from part 1?  I don't see it.

sort($lines);
// print_r($lines);


for ($i = 0 ; $i < count($lines) - 1; $i++) {
    if (compareNeighbors($lines[$i], $lines[$i + 1])) {
        print "Bingo: {$lines[$i]}, {$lines[$i + 1]}\n";
    }
}


foreach ($lines as &$line) {
    $line = strrev($line);
}

sort($lines);
// print_r($lines);

for ($i = 0 ; $i < count($lines) - 1; $i++) {
    if (compareNeighbors($lines[$i], $lines[$i + 1])) {
        print "Bingo: {$lines[$i]}, {$lines[$i + 1]}\n";
    }
}


function compareNeighbors($a, $b) {
    $len = strlen($a);
    $miss = 0;

    for ($i = 0; $i < $len; $i++) {
        if ($a[$i] != $b[$i]) {
            if (++$miss > 1) {
                return false;
            }
        }

    }

    return true;
}