<?php

// Copyright 2018 Max Sprauer

$lines = file_get_contents('input.txt');
$lines = explode("\n", $lines);

foreach ($lines as $line) {
    if (!empty($line)) { 
        $changes[] = trim($line, ' +');   
    }
}
 
// print_r($changes);

$freq = array_sum($changes);
print "Frequency: $freq\n";

// Part Two

$seen = array();
$i = 0;
$newFreq = 0;

do {
    $seen[] = $newFreq;
    $newFreq += $changes[$i]; 
    $i = ($i + 1) % count($changes); 
} while (!in_array($newFreq, $seen));

print "Number seen: " . count($seen) . "\n";
print "First duplicate: $newFreq\n";
