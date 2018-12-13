<?php

// Copyright 2018 Max Sprauer

define('INITIAL_STATE', '#.#####.#.#.####.####.#.#...#.......##..##.#.#.#.###..#.....#.####..#.#######.#....####.#....##....#');

$lines = file('input.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$rules = array();

foreach ($lines as $line) {
    $m = array();
    // ##.## => .
    if (preg_match('/^([#\.]{5}) => ([#\.])$/', $line, $m)) {
        $rules[$m[1]] = $m[2];
    }
}

print_r($rules);
//go(20, 24);
// States 1 and 5109 match?  (No, they don't.)
go(5109, 5000);

function go($generations, $padSize)
{
    global $rules;

    $state[0] = str_pad(INITIAL_STATE, strlen(INITIAL_STATE) + 2 * $padSize, '.', STR_PAD_BOTH);
    for ($g = 1; $g <= $generations; $g++) {
        // I don't understand how we're supposed to do this
        // $start = PAD - ($g - 1) * 2;
        // $end = PAD + strlen(INITIAL_STATE) + ($g - 1) * 2;
        $start = 2;
        $end = strlen($state[$g - 1]) - 3;
        $newState = $state[$g - 1];
    
        for ($i = $start; $i <= $end; $i++) {
            $five = substr($state[$g - 1], $i - 2, 5);
            if (isset($rules[$five])) {
                $newState[$i] = $rules[$five];
            } else {
                $newState[$i] = '.';   // This is not specified
            }
        }

        if ($index = array_search($newState, $state) !== false) {
            print "States $index and $g match.\n";
      //      print ("$newState\n{$states[$index]}\n");
      //      exit;
        }

        $state[$g] = $newState;

        if ($g % 1000 == 0) {
            print "Generation $g: Start: $start, End: $end\n{$state[$g]}\n";
        }
    }

    $sum = 0;
    for ($i = 0; $i < strlen($state[$generations]); $i++) {
        if ($state[$generations][$i] == '#') {
            $sum += ($i - $padSize);
        }
    }

    print $state[$generations] . "\n";
    print "Sum: $sum\n";
}
