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

printMap($map, 'initial.txt');
print "x: $xMin - $xMax, y: $yMin - $yMax\n";

// Add some growth room
$xMin -= 20;
$xMax += 20;

$streamHeads = array(new StreamHead(500, 1));
iterate($map, isset($argv[1]) ? $argv[1] : 10000);
printMap($map);
// print_r($streamHeads);


function printMap($map, $filename = 'map.txt')
{
    global $xMin, $xMax, $yMin, $yMax;

    // I'm printing starting at 1,1 for my sanity --- so the coords in the editor match the actual coords.
    // The faucet itself (line 0) is not visible.
    ob_start();
    for ($y = 1 /* 0 */; $y < $yMax; $y++) {
        for ($x = 1 /*$xMin - 1*/; $x <= $xMax + 1; $x++) {
            if (isset($map[$x][$y])) {
                print $map[$x][$y];
            } else {
                print '.';
            }
        }

        print "\n";
    }
    file_put_contents($filename, ob_get_contents());
    ob_clean();
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
        } else {
            unset($sh);
        }

    } while ($count < $iterations && !empty($streamHeads));
}

function action_falling(&$map, &$sh)
{
    global $yMax;

    $x = $sh->x;
    $y = $sh->y;
    $nextState = null;

    // Fall until we hit something
    while (!isset($map[$x][$y]) && $y <= $yMax) {
        $map[$x][$y] = '|';
        $y++;
    } 

    $waterBelow = ($map[$x][$y] == '~');
    $streamBelow = ($map[$x][$y] == '|');

    $y--;
    $sh->y = $y;

    // Figure out if we're in a bucket
    $firstClayOnLeft = clayOnLeft($x, $y);
    $firstClayOnRight = clayOnRight($x, $y);

    if ($y + 1 >= $yMax) {
        $nextState = 'done';
    } else if ($firstClayOnLeft && $firstClayOnRight && clayOrWaterBelowRange($firstClayOnLeft, $firstClayOnRight, $y)) {
        // We're in a bucket or part of a partially-filled U-shaped bucket (line 192)
        $nextState = 'filling';
        $sh->left = $firstClayOnLeft;
        $sh->right = $firstClayOnRight;
    } else if (clayBelowRange($x, $x, $y)) {
        // We've hit the edge of a bucket or a box/ledge within a larger bucket (line 828)
        list($clayBelowLeft, $clayBelowRight) = getRangeOfClayBelow($x, $y);
        $nextState = 'overflowing';
        $sh->left = $clayBelowLeft;
        $sh->right = $clayBelowRight; 
    } else if ($streamBelow) {
        // We've hit another stream.  This stream is done.
        $nextState = 'done';
    } else if ($waterBelow) {
        // We've hit water.  This stream is done.
        $nextState = 'done';
    } else {
        assert(false, "Unhandled case: Stream head [{$sh->id}]: $x, $y");
        $nextState = 'done';
    }

    return $nextState;
}

function action_filling(&$map, &$sh)
{
    $x = $sh->x;
    $y = $sh->y;

    $firstClayOnLeft = clayOnLeft($x, $y);
    $firstClayOnRight = clayOnRight($x, $y);

    while ($firstClayOnLeft && $firstClayOnRight && $firstClayOnLeft >= $sh->left && $firstClayOnRight <= $sh->right) {
        for ($i = $firstClayOnLeft + 1; $i < $firstClayOnRight; $i++) {
            $map[$i][$y] = '~';

            // Check if the space below is empty (not clay or water) and start a new stream head 
            if (!isset($map[$i][$y+1])) {
                addStreamHead($i, $y + 1);
            }
        }

        $y--;
        $firstClayOnLeft = clayOnLeft($x, $y);
        $firstClayOnRight = clayOnRight($x, $y);    
    }

    $sh->y = $y;
    return 'overflowing';
}

function action_overflowing(&$map, &$sh)
{
    $x = $sh->x;
    $y = $sh->y;
    $nextState = null;

    if (isset($map[$x][$y]) && $map[$x][$y] == '~') {
        // If an inner bucket is overflowing into an already-filled outer bucket, we're done
        return 'done';
    }
 
    // Overflow left and right until we hit clay or drop.  Each drop will be a new stream head.

    // Find borders
    $firstClayOnLeft = clayOnLeft($x, $y);
    $firstClayOnRight = clayOnRight($x, $y);

    // If there is a wall before we hit the edge of the bucket ($sh), respect that.
    $fillLeft = ($firstClayOnLeft) ? max($firstClayOnLeft, $sh->left) : $sh->left;
    $fillRight = ($firstClayOnRight) ? min($firstClayOnRight, $sh->right) : $sh->right;

    // Fill area between left and right with |
    for ($i = $fillLeft + 1; $i < $fillRight; $i++) {
        $map[$i][$y] = '|';
    }

    if ($firstClayOnLeft == 0 || $firstClayOnLeft < $sh->left) {
        // Drop on the left at x = $sh->left

        // Add one more to cover the wall
        $map[$sh->left][$y] = '|';

        // Start new stream head
        addStreamHead($sh->left - 1, $y);
    }

    if ($firstClayOnRight == 0 || $firstClayOnRight > $sh->right) {
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
            print "Dropped duplicate stream head [{$sh->id}] at $x, $y\n";
            return;
        }
    }

    $streamHeads[] = new StreamHead($x, $y);
    print "Stream Heads: " . count($streamHeads) . "\n";
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

function clayBelowRange($x1, $x2, $y)
{
    global $map;

    for ($x = $x1; $x <= $x2; $x++) {
        if (!isset($map[$x][$y+1]) || $map[$x][$y+1] != '#') {
            return false;
        }
    }

    return true;
}

function clayOrWaterBelowRange($x1, $x2, $y)
{
    global $map;

    for ($x = $x1; $x <= $x2; $x++) {
        if (!isset($map[$x][$y+1]) || ($map[$x][$y+1] != '#' && $map[$x][$y+1] != '~')) {
            return false;
        }
    }

    return true;
}

function getRangeOfClayBelow($x, $y)
{
    global $map, $xMax;

    for ($left = $x; $left > 0; $left--) {
        if (!isset($map[$left][$y+1]) || $map[$left][$y+1] != '#') {
            break;
        }
    }

    for ($right = $x; $right < $xMax; $right++) {
        if (!isset($map[$right][$y+1]) || $map[$right][$y+1] != '#') {
            break;
        }
    }

    return array($left + 1, $right - 1);
}

class StreamHead
{
    public $x, $y;          // Location of stream head between states
    public $state;          // String decribing current state 
    public $left, $right;   // Left/right X coordinates to fill or overflow between
    public $id;             // Unique ID for debugging purposes 
    public static $lastId = 0;

    function __construct($x, $y)
    {
        $this->x = $x;
        $this->y = $y;
        $this->state = 'falling';
        $this->id = ++static::$lastId;   
    }
}
