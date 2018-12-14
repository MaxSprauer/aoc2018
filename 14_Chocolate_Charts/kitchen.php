<?php

// Copyright 2018 Max Sprauer

$scores = [3, 7];
$elf1 = 0;
$elf2 = 1;

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
    }

} while ($count < 580741 + 10);

for ($i = 580741; $i < $count; $i++) {
    print $scores[$i];  // Can be 10 or 11 digits
}

print "\n";