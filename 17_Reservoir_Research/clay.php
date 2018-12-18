<?php

// Copyright 2018 Max Sprauer
// This is bad.  It should probably be wrapped up in a class.  Instead we've got functions and globals.

$input = file('input.txt');

$map = array();
$xMin = 10000000;
$xMax = 0;
$yMin = 10000000;
$yMax = 0;

$map[500][0] = '+';

// x=409, y=1414..1426
foreach ($input as $line) {
    $x0 = $x1 = $y0 = $y1 = null;

    if (preg_match('/x=(\d+), y=(\d+)\.\.(\d+)/', $line, $m)) {
        $x0 = $x1 = $m[1];
        $y0 = $m[2];
        $y1 = $m[3];
    }

    if (preg_match('/y=(\d+), x=(\d+)\.\.(\d+)/', $line, $m)) {
        $y0 = $y1 = $m[1];
        $x0 = $m[2];
        $x1 = $m[3];
    }

    if ($x1) {
        assert ($x0 <= $x1);
        assert ($y0 <= $y1);

        if ($x0 < $xMin) {
            $xMin = $x0;
        }

        if ($x1 > $xMax) {
            $xMax = $x1;
        }

        if ($y0 < $yMin) {
            $yMin = $y0;
        }

        if ($y1 > $yMax) {
            $yMax = $y1;
        }

        for ($x = $x0; $x <= $x1; $x++) {
            for ($y = $y0; $y <= $y1; $y++) {
                $map[$x][$y] = '#';
            }
        }

    } else if (!empty($line)) {
        print "Bad line: $line\n";
    }
}

// print_r($map);
print "x: $xMin - $xMax, y: $yMin - $yMax\n";

$streamHeads = array(new StreamHead(500, 1));
iterate($map, $argv[1]);
printMap($map);


function printMap($map)
{
    global $xMin, $xMax, $yMin, $yMax;

    for ($y = 0; $y < $yMax; $y++) {
        for ($x = $xMin - 1; $x < $xMax + 1; $x++) {
            if (isset($map[$x][$y])) {
                print $map[$x][$y];
            } else {
                print '.';
            }
        }

        print "\n";
    }
}

function iterate(&$map, $iterations)
{
    global $streamHeads;
    $count = 0;

    do {
        $sh = array_shift($streamHeads);
        $f = "action_{$sh->state}";
        $newState = $f($map, $sh);
        assert(!empty($newState));
        $sh->state = $newState;
        $count++;

        if ($sh->state != 'done') {
            array_push($streamHeads, $sh);
        }

    } while ($count < $iterations && !empty($streamHeads));
}

function action_falling(&$map, &$sh)
{
    $x = $sh->x;
    $y = $sh->y;
    $nextState = null;

    // Fall down
    while (!isset($map[$x][$y])) { // what if it's |?
        $map[$x][$y] = '|';
        $y++;
    } 

    //TODO: Check done state
    $y--;
    $sh->y = $y;

    // Figure out if we're in a bucket
    $left = clayOnLeft($x, $y);
    $right = clayOnRight($x, $y);

    if ($left && $right && waterOrClayBelow($left, $right, $y)) {
        // We're in a bucket
        $nextState = 'filling';
        $sh->left = $left;
        $sh->right = $right;
    } else {
        $nextState = 'overflowing';
    }

    return $nextState;
}

function action_filling(&$map, &$sh)
{
    $x = $sh->x;
    $y = $sh->y;

    $left = clayOnLeft($x, $y);
    $right = clayOnRight($x, $y);

    while ($left && $right && $left >= $sh->left && $right <= $sh->right) {
        for ($i = $left + 1; $i < $right; $i++) {
            $map[$i][$y] = '~';
        }

        $y--;
        $left = clayOnLeft($x, $y);
        $right = clayOnRight($x, $y);    
    }

    $sh->y = $y;
    return 'overflowing';
}

function action_overflowing(&$map, &$sh)
{
    $x = $sh->x;
    $y = $sh->y;
    $nextState = null;

    // Overflow left and right until we hit clay or drop.  Each drop can be a new stream head.

    // Find borders
    $left = clayOnLeft($x, $y);
    $right = clayOnRight($x, $y);

    $fillLeft = ($left) ? max($left, $sh->left) : $sh->left;
    $fillRight = ($right) ? min($right, $sh->right) : $sh->right;

    // Fill area between left and right with |
    for ($i = $fillLeft + 1; $i < $fillRight; $i++) {
        $map[$i][$y] = '|';
    }

    if ($left == 0 || $left < $sh->left) {
        // Drop on the left at x = $sh->left

        // Add one more to cover the wall
        $map[$sh->left][$y] = '|';

        // Start new stream head
        addStreamHead($sh->left - 1, $y);
    }

    if ($right == 0 || $right > $sh->right) {
        // Drop on the right at x = $sh->right

        // Add one more to cover the wall
        $map[$sh->right][$y] = '|';

        // Start new stream head
        addStreamHead($sh->right + 1, $y);
    }

    return 'done';  // But we added one or two new stream heads
}

function addStreamHead($x, $y)
{
    global $streamHeads;

    foreach ($streamHeads as $sh) {
        if ($sh->x == $x && $sh->y == $y) {
            print "Dropped stream head at $x, $y\n";
            return;
        }
    }

    $streamHeads[] = new StreamHead($x, $y);
}

function clayOnLeft($x, $y)
{
    global $map, $xMin, $xMax;

    for ($i = $x; $i > $xMin; $i--) {
        if (isset($map[$i][$y]) && $map[$i][$y] == '#') {
            return $i;
            break;
        }
    }

    return 0;
}

function clayOnRight($x, $y)
{
    global $map, $xMin, $xMax;

    for ($i = $x; $i < $xMax; $i++) {
        if (isset($map[$i][$y]) && $map[$i][$y] == '#') {
            return $i;
        }
    }

    return 0;
}

function waterOrClayBelow($x1, $x2, $y)
{
    global $map;

    for ($x = $x1; $x <= $x2; $x++) {
        if (!isset($map[$x][$y+1])) {
            return false;
        }

    }

    return true;
}

class StreamHead
{
    public $x, $y;
    public $state;

    function __construct($x, $y)
    {
        $this->x = $x;
        $this->y = $y;
        $this->state = 'falling';
    }
}
