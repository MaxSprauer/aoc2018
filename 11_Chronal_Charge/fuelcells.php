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
$startTime = time();
partTwo(SERIAL);
print "Time: " . (time() - $startTime) . "\n";

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
    $cache = array();
    $cacheHits = 0;

    // Pre-cache 10x10 blocks
    $size = 10;
    for ($x = WIDTH - $size + 1; $x >= 1; $x--) {
        for ($y = HEIGHT - $size + 1; $y >= 1; $y--) {
            $cache["$x,$y,10"] = subgridValue($grid, $x, $y, 10, 10);
        }
    }

    print "Entries in cache: " . count($cache) . "\n";

    for ($size = 1; $size <= WIDTH; $size++) {
        for ($x = WIDTH - $size + 1; $x >= 1; $x--) {
            for ($y = HEIGHT - $size + 1; $y >= 1; $y--) {

                // Cache subgrids of size 10
                if ($size < 10) {
                    $val = subgridValue($grid, $x, $y, $size, $size);
                } else if ($size == 10) {
                    $coord = "$x,$y,10";
                    assert(isset($cache[$coord]), $coord);
                    $cacheHits++;
                    $val = $cache[$coord];
                } else if ($size > 10) {
                    $blocksToUse = intdiv($size, 10);
                    $overflow = $size % 10;
                    $val = 0;

                    // First calculate "overflow" outside of 10x10 blocks
                    $val += subgridValue($grid, $x, $y, $overflow, $overflow);
                    $val += subgridValue($grid, $x + $overflow, $y, $blocksToUse * 10, $overflow);
                    $val += subgridValue($grid, $x, $y + $overflow, $overflow, $blocksToUse * 10);

                    // Then add 10x10 blocks
                    for ($cacheX = 0; $cacheX < $blocksToUse; $cacheX++) {
                        for ($cacheY = 0; $cacheY < $blocksToUse; $cacheY++) {
                            $coord = implode(',', [$x + $overflow + ($cacheX * 10), $y + $overflow  + ($cacheY * 10), 10]);
                            assert(isset($cache[$coord]), "$coord $size $blocksToUse $overflow");
                            $cacheHits++;
                            $val += $cache[$coord];
                        }
                    }
                }

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

    print "Winner: $winner ($winX, $winY, $winSize), $cacheHits\n";
}


function subgridValue($grid, $startX, $startY, $sizeX = SUBGRID_WIDTH, $sizeY = SUBGRID_HEIGHT)
{
    $sum = 0;

    assert($startX + $sizeX - 1 <= 300, "$startX $sizeX");
    assert($startY + $sizeY - 1 <= 300, "$startY $sizeY");

    // printf("%3d, %3d, %3d, %3d\n", $startX, $startY, $sizeX, $sizeY);

    for ($x = $startX; $x < $startX + $sizeX; $x++) {
        for ($y = $startY; $y < $startY + $sizeY; $y++) {
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
        $powerLevel = intdiv(($powerLevel % 1000), 100);
    }

    return $powerLevel - 5;
}
