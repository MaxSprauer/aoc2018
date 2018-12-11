<?php

// Copyright 2018 Max Sprauer

define('WIDTH', 300);
define('HEIGHT', 300);
define('SUBGRID_WIDTH', 3);
define('SUBGRID_HEIGHT', 3);
define('SERIAL', 5719);

assert(4 == powerLevel(3, 5, 8));
assert(-5 == powerLevel(122, 79, 57));
assert(0 == powerLevel(217, 196, 39));
assert(4 == powerLevel(101, 153, 71));

// partOne(SERIAL);
partTwo(SERIAL);

function partOne($serial) 
{
    // Initialize grid
    $grid = array();
    for ($x = 1; $x <= WIDTH; $x++) {
        for ($y = 1; $y <= HEIGHT; $y++) {
            $grid[$x][$y] = powerLevel($x, $y, $serial);
        }
    }

    // Find subgrid values
    $winner = null;
    $winX = $winY = -1;

    for ($x = 1; $x <= WIDTH - SUBGRID_WIDTH + 1; $x++) {
        for ($y = 1; $y <= HEIGHT - SUBGRID_HEIGHT + 1; $y++) {
            $val = subgridValue($grid, $x, $y);
            // printf("(%3d, %3d): %5d\n", $x, $y, $val);
            if (is_null($winner) || $val >= $winner) {
                $winner = $val;
                $winX = $x;
                $winY = $y;
            }
        }
    }

    print "Winner: $winner ($winX, $winY)\n";
}

function partTwo($serial) 
{
    // Initialize grid
    $grid = array();
    for ($x = 1; $x <= WIDTH; $x++) {
        for ($y = 1; $y <= HEIGHT; $y++) {
            $grid[$x][$y] = powerLevel($x, $y, $serial);
        }
    }

    // Find subgrid values
    $winner = null;
    $winX = $winY = $winSize = -1;

    for ($size = 1; $size <= WIDTH; $size++) {
        for ($x = 1; $x <= WIDTH - $size + 1; $x++) {
            for ($y = 1; $y <= HEIGHT - $size + 1; $y++) {
                $val = subgridValue($grid, $x, $y, $size);
                // printf("(%3d, %3d): %5d\n", $x, $y, $val);
                if (is_null($winner) || $val >= $winner) {
                    $winner = $val;
                    $winX = $x;
                    $winY = $y;
                    $winSize = $size;
                }
            }
        }
    }

    print "Winner: $winner ($winX, $winY, $winSize)\n";
}


function  subgridValue($grid, $startX, $startY, $size = SUBGRID_WIDTH)
{
    $sum = 0;

    for ($x = $startX; $x < $startX + $size; $x++) {
        for ($y = $startY; $y < $startY + $size; $y++) {
            $sum += $grid[$x][$y];
        }
    }

    return $sum;
}

function powerLevel($x, $y, $serial)
{
    $rackId = $x + 10;
    $powerLevel = $rackId * $y;
    $powerLevel += $serial;
    $powerLevel *= $rackId;
    
    if ($powerLevel < 100) {
        $powerLevel = 0;
    } else {
        $powerLevel = substr($powerLevel, -3, 1);
    }

    return $powerLevel - 5;
}