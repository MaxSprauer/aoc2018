<?php

// Copyright 2018 Max Sprauer

$lines = file_get_contents('input.txt');
$lines = explode("\n", $lines);
$containsDouble = 0;
$containsTriple = 0;

foreach ($lines as $line) {
    // There's gotter be a slicker way to do this
    list($delta2, $delta3) = getLetterCounts($line);
    if ($delta2 > 0) {
        $containsDouble++;
    }
    
    if ($delta3 > 0) {
        $containsTriple++;
    }
}
 
print "Checksum: " . ($containsDouble * $containsTriple) . "\n"; 

function getLetterCounts($str) {
    $len = strlen($str);
    $twice = 0;
    $thrice = 0;
    $counts = array();

    for ($i = 0; $i < $len; $i++) {
        if (!isset($counts[$str[$i]])) {
            $counts[$str[$i]] = 1;
        } else {
            $counts[$str[$i]]++;
        }
    }

    foreach ($counts as $letter => $count) {
        switch ($count) {
            case 2:
                $twice++;
                break;
            
            case 3:
                $thrice++;
                break;
        }
    }

    return array($twice, $thrice);
}