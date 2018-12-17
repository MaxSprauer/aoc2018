<?php

// Copyright 2018 Max Sprauer

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
iterate($map, 60);
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
    $count = 0;
    $heads = array([500, 0]);

    // head can be sand or clay or a stream

    do {
        list($x, $y) = array_shift($heads);

        // Open sand below, turn it into a stream
        if (!isset($map[$x][$y + 1])) {
            $map[$x][$y + 1] = '|';
            array_push($heads, [$x, $y + 1]);
            $count++;
        } else {
            $below = $map[$x][$y + 1];

            if ($below == '#') {
                if ($map[$x][$y] != '~' && clayOnBothSides($x, $y)) {
                    // In a bucket.  Turn this into water.  Try to move left and right.
                    $map[$x][$y] = '~';
                    array_push($heads, [$x - 1, $y]);
                    array_push($heads, [$x + 1, $y]);
                    $count++;                    
                }
            }





        }

    } while ($count < $iterations);
}

function clayOnBothSides($x, $y)
{
    return clayOnLeft($x, $y) && clayOnRight($x, $y);
}

function clayOnLeft($x, $y)
{
    global $map, $xMin, $xMax;

    for ($i = $x; $i > $xMin; $i--) {
        if (isset($map[$i][$y]) && $map[$i][$y] == '#') {
            return true;
            break;
        }
    }

    return false;
}

function clayOnRight($x, $y)
{
    global $map, $xMin, $xMax;

    for ($i = $x; $i < $xMax; $i++) {
        if (isset($map[$i][$y]) && $map[$i][$y] == '#') {
            return true;
        }
    }

    return false;
}

function waterOrClayBelow($x, $y)
{


}


