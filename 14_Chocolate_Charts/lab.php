<?php

// Copyright 2018 Max Sprauer

$scores = [3, 7];
$elf1 = 0;
$elf2 = 1;
$pos = false;

do {
    // Add new scores
    $new = $scores[$elf1] + $scores[$elf2];
    if (intdiv($new, 10) > 0) {
        $scores[] = 1; 
    }
    $scores[] = $new % 10;

    // Move elves
    $count = count($scores);
    $elf1 = ($elf1 + 1 + $scores[$elf1]) % $count;
    $elf2 = ($elf2 + 1 + $scores[$elf2]) % $count;

    // print implode(' ', $scores) . "\n";

    if ($count % 10000 == 0) {
        print "Scores: $count\n";
    
        // This is horrible, so only do it once in a while.
        $pos = strpos(implode('', $scores), '580741'); 
    }
} while ($pos === false);

print "$pos\n";