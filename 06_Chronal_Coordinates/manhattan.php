<?php

// Copyright 2018 Max Sprauer
define('WIDTH', 400);
define('HEIGHT', 400);
define('MAXDIST', 10000);

$lines = file_get_contents('input.txt');
$lines = explode("\n", $lines);

$cities = array();

foreach ($lines as $line) {
    if (!empty($line)) {
        $cities[] = new Coord($line);
    }
}

// print_r($cities);

// partOne();
partTwo();

function partOne()
{
    global $cities;

    $closest = array();

    // Find closest city to each location
    for ($x = 0; $x < WIDTH; $x++) {
        for ($y = 0; $y < HEIGHT; $y++) {

            $minDist = 10000;
            $minDistCityIndex = -1;

            // Find the minimum distance to a city
            foreach ($cities as $index => $city) {
                $dist = $city->getDistance($x, $y);
                if ($dist < $minDist) {
                    $minDist = $dist;
                    $minDistCityIndex = $index;
                } 
            }

            // Now see if two or more cities match the minimum distance
            foreach ($cities as $index => $city) {
                if ($index != $minDistCityIndex) {
                    $dist = $city->getDistance($x, $y);
                    if ($dist == $minDist) {
                        $minDistCityIndex = -1;
                        break; 
                    }
                }
            }

            $closest[$x][$y] = $minDistCityIndex;
        }
    }

    // print_r($closest);

    // Find infinite areas (border an edge)
    $borderCities = array();  
    for ($x = 0; $x < WIDTH; $x++) {
        foreach ([0, HEIGHT - 1] as $y) {
            $borderCities[] = $closest[$x][$y];
        }
    }

    foreach ([0, WIDTH - 1] as $x) {
        for ($y = 0; $y < HEIGHT; $y++) {
            $borderCities[] = $closest[$x][$y];
        }
    }

    $borderCities = array_unique($borderCities);
    // print_r($borderCities);

    // Find sizes of regions of non-border cities
    $regionSizes = array();

    for ($x = 0; $x < WIDTH; $x++) {
        for ($y = 0; $y < HEIGHT; $y++) {
            $cityIndex = $closest[$x][$y];
            if (!in_array($cityIndex, $borderCities)) {
                if (!isset($regionSizes[$cityIndex])) {
                    $regionSizes[$cityIndex] = 0; 
                }
                $regionSizes[$cityIndex]++;
            }
        }
    }

    asort($regionSizes);
    print_r($regionSizes);
}

function partTwo()
{
    global $cities;

    // Find if each location is within the region
    $withinRegionCount = 0;
    for ($x = 0; $x < WIDTH; $x++) {
        for ($y = 0; $y < HEIGHT; $y++) {
            $totalDist = 0;

            foreach ($cities as $city) {
                $totalDist += $city->getDistance($x, $y);
            }
    
            if ($totalDist < MAXDIST) {
                $withinRegionCount++;
            }
        }
    }

    print "Within region: $withinRegionCount\n";
}

class Coord {
    public $x;
    public $y;

    public function __construct($line)
    {
        list($this->x, $this->y) = explode(',', $line);
    }

    public function getDistance($x2, $y2)
    {
        return abs($this->x - $x2) + abs($this->y - $y2);
    }
};