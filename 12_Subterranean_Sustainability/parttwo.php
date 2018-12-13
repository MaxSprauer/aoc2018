<?php

// Copyright 2018 Max Sprauer

// At Gen 1000, this pattern starts at 990.  Gen 2000, 1990.
// ###.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#
// It moves 1 to the right every generation.  (Or at least 1000 every 1000.)

$start = 50000000000 - 11;
$state = '###.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#.#';

$sum = 0;
for ($i = 0; $i < strlen($state); $i++) {
    if ($state[$i] == '#') {
        $sum += ($start + $i);
    }
}

print "$sum\n";