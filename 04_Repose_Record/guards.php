<?php

// Copyright 2018 Max Sprauer

$lines = file_get_contents('input.txt');
$lines = explode("\n", $lines);
$times = array();

/*
 * [1518-03-19 00:41] wakes up
 * [1518-04-15 00:10] falls asleep
 * [1518-02-06 23:52] Guard #3109 begins shift
 */

// First sort the rows into time order
foreach ($lines as $line) {
    if (!preg_match('/^\[(.+)\].*/', $line, $matches)) { 
        if (!empty($line)) {
            print "Line does not match: $line\n";
        }
    } else {  
        $dt = $matches[1];
        $ts = getTimestamp($dt);
        assert(!isset($times[$ts]), "Duplicate time: $ts");
        $times[$ts] = $matches[0];
    }
}

ksort($times);
// print_r($times);

// strategyOne();
strategyTwo();

function strategyOne()
{
    global $times;

    // First calc total asleep min for every guard
    $guardAsleepMin = array();
    $curGuard = '';
    $sleepStart = '';

    foreach ($times as $line) {
        if (preg_match('/^\[(.+)\] Guard #(\d+) begins shift/', $line, $matches)) {
            $curGuard = $matches[2];
            $sleepStart = '';
        } else if (preg_match('/^\[(.+)\] falls asleep/', $line, $matches)) {
            $sleepStart = $matches[1];
        } else if (preg_match('/^\[(.+)\] wakes up/', $line, $matches)) {
            $sleepEndDT = new DateTime($matches[1]);
            assert(!empty($sleepStart));
            $sleepStartDT = new Datetime($sleepStart);
            assert(!empty($curGuard));
            if (!isset($guardAsleepMin[$curGuard])) {
                $guardAsleepMin[$curGuard] = 0;
            }
            $interval = date_diff($sleepEndDT, $sleepStartDT);
            $guardAsleepMin[$curGuard] += $interval->format('%i');
        } else {
            print "Unknown line: $time\n";
            exit(1);
        }
    }

    arsort($guardAsleepMin);
    // print_r($guardAsleepMin);

    $guardId = array_keys($guardAsleepMin)[0];
    
    // Then calc most asleep min for our guard
    // [1518-04-15 00:10] falls asleep
    $curGuard = '';
    $sleepStart = '';
    $mins = array_fill(0, 60, 0);
    foreach ($times as $line) {
        if (preg_match('/^\[(.+)\] Guard #(\d+) begins shift/', $line, $matches)) {
            $curGuard = $matches[2];
            $sleepStart = '';
        } else if (preg_match('/^\[.+ 00:(\d+)\] falls asleep/', $line, $matches)) {
            if ($curGuard == $guardId) {
                $sleepStart = $matches[1];
            }
        } else if (preg_match('/^\[.+ 00:(\d+)\] wakes up/', $line, $matches)) {
            if ($curGuard == $guardId) {
                $sleepEnd = $matches[1];
                assert(!empty($sleepStart));

                for ($i = $sleepStart; $i < $sleepEnd; $i++) {
                    $mins[(int) $i]++;
                }
            }
        } else {
            print "Unknown line: $time\n";
            exit(1);
        }
    }

    // print_r($mins);
    arsort($mins);
    print "Guard: $guardId, Minutes: " . array_keys($mins)[0] . "\n";
}

function strategyTwo()
{
    global $times;

    // Calc asleep min for all guards
    // [1518-04-15 00:10] falls asleep
    $curGuard = '';
    $sleepStart = '';
    $minPerGuard = array();
    foreach ($times as $line) {
        if (preg_match('/^\[(.+)\] Guard #(\d+) begins shift/', $line, $matches)) {
            $curGuard = $matches[2];
            $sleepStart = '';
        } else if (preg_match('/^\[.+ 00:(\d+)\] falls asleep/', $line, $matches)) {
            $sleepStart = $matches[1];
        } else if (preg_match('/^\[.+ 00:(\d+)\] wakes up/', $line, $matches)) {
            $sleepEnd = $matches[1];
            assert(!empty($sleepStart));

            if (!isset($minPerGuard[$curGuard])) {
                $minPerGuard[$curGuard] = array_fill(0, 60, 0);
            }

            for ($i = $sleepStart; $i < $sleepEnd; $i++) {
                $minPerGuard[$curGuard][(int) $i]++;
            }
        } else {
            print "Unknown line: $time\n";
            exit(1);
        }
    }

    // print_r($minPerGuard);

    // Find guard with highest frequency asleep on same minute
    $winGuardId = null;
    $winMin = null;
    $highestCount = 0;

    foreach ($minPerGuard as $curGuard => $mins) {
        arsort($mins);
        $highestMin = array_keys($mins)[0];
        $count = $mins[$highestMin];
        if ($count > $highestCount) {
            $highestCount = $count;
            $winGuardId = $curGuard;
            $winMin = $highestMin;
        }
    }
    
    print "Guard: $winGuardId, Minute: $winMin\n";
}

function getTimestamp($str)
{
    // Add 500 years so we can get UNIX time
    $dt = new DateTime($str);
    $dt->add(new DateInterval('P500Y'));   
    return $dt->format('U');
}