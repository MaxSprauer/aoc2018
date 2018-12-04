<?php

// Copyright 2018 Max Sprauer

$lines = file_get_contents('input.txt');
$lines = explode("\n", $lines);
$rects = array();

foreach ($lines as $line) {
    if (!preg_match('/^#(\d+) @ (\d+),(\d+): (\d+)x(\d+)/', $line, $matches)) { 
        if (!empty($line)) {
            print "Line does not match: $line\n";
        }
    } else {
        $rects[] = new Rect($matches);
    }
}

// print_r($rects);
// partOne();
// testOverlaps();
partTwo();

// Part One took over 30 minutes to run; this is clearly not the best algorithm
function partOne()
{
    global $rects;
    $twoOrMore = 0;

    // Put the biggest rectangles first
    usort($rects, function($a, $b) {
        $areaA = $a->w * $a->h;
        $areaB = $b->w * $b->h;
        return ($areaB - $areaA);   // This is backwards; I want a reverse sort
    });

    for ($x = 0; $x < 1000; $x++) {
        for ($y = 0; $y < 1000; $y++) {
            $overlaps = 0;

            if (($x * $y) % 10000 == 0) {
                print "x = $x, y = $y\n";
            }

            foreach ($rects as $rect) {
                if ($rect->coversOffset($x, $y)) {
                    $overlaps++;

                    if ($overlaps > 1) {
                        $twoOrMore++;
                        continue 2;
                    }        
                }
            }
        }
    }

    print "Two or more overlaps: $twoOrMore\n";
}

function partTwo()
{
    global $rects;
    $count = count($rects);

     // Put the smallest rectangles first
     usort($rects, function($a, $b) {
        $areaA = $a->w * $a->h;
        $areaB = $b->w * $b->h;
        return ($areaA - $areaB);
    });

    for ($outer = 0; $outer < $count; $outer++) {
        for ($inner = 0; $inner < $count; $inner++) {
            if ($inner == $outer) {
                // Don't compare to self
                continue;
            }
            
            if ($rects[$outer]->overlaps($rects[$inner]) || $rects[$inner]->overlaps($rects[$outer])) {
                // It's not this one
                continue 2;
            }
        }

        // If we got here, the outer rect has no overlaps
        print "Rect ID: " . $rects[$outer]->id . "\n";
        exit(0);
    }
}

function testOverlaps()
{
    global $rects;
    $r1 = new Rect(array(0, 1, 3, 3, 3, 3));
    $r2 = new Rect(array(0, 2, 4, 4, 1, 1));
    assert($r1->overlaps($r2));

    $r3 = new Rect(array(0, 3, 0, 0, 8, 8));
    assert($r3->overlaps($r1));


}

class Rect {
    function __construct($matches) 
    {
        $this->id = $matches[1];
        $this->x = $matches[2];
        $this->y = $matches[3];     // Actually distance from top
        $this->w = $matches[4];
        $this->h = $matches[5];
    }

    function coversOffset($x, $y)
    {
        return ($x >= $this->x) && ($x < $this->x + $this->w)
            && ($y >= $this->y) && ($y < $this->y + $this->h);
    }

    function overlaps(Rect $r)
    {
        // Does not handle $r containing $this
        $xOverlap = (($r->x >= $this->x && $r->x < $this->x + $this->w) || ($r->x + $r->w >= $this->x && $r->x + $r->w < $this->x + $this->w));
        $yOverlap = (($r->y >= $this->y && $r->y < $this->y + $this->h) || ($r->y + $r->h >= $this->y && $r->y + $r->h < $this->y + $this->h));
        return ($xOverlap && $yOverlap);
    }
}