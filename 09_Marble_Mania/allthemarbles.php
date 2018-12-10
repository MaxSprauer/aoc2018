<?php

// Copyright 2018 Max Sprauer
// Requires PEAR's Structures_LinkedList_DoubleNode

require_once 'CircularBuffer.class.php';
require_once 'CircularBuffer2.class.php';

set_error_handler('myErrorHandler');
ini_set('memory_limit', '4G');

assert(32 == play2(9, 25));
assert(146373 == play2(13, 7999));
assert(2764 == play2(17, 1104));
assert(54718 == play2(21, 6111));
assert(37305 == play2(30, 5807));

// Part One
// print play(435, 71184);

// Part Two: Still haven't found the trick to this.
print play2(435, 7118400, false);


function play2($players, $lastMarble, $print = false)
{
    $cb = new CircularBuffer2();
    $lowestMarble = 1;
    $scores = array_fill(1, $players, 0);

    for ($p = 1; $p <= $players; $p = ($p == $players) ? 1 : $p + 1) {

        if ($lowestMarble % 23 == 0) {
            // First, the current player keeps the marble they would have placed, adding it to their score. 
            $scores[$p] += $lowestMarble;
            // In addition, the marble 7 marbles counter-clockwise from the current marble is removed from the circle and also added to the current player's score. 
            // The marble located immediately clockwise of the marble that was removed becomes the new current marble.
            $scores[$p] += $cb->remove();

            gc_collect_cycles();
        } else {
            // each Elf takes a turn placing the lowest-numbered remaining marble into the circle between the marbles that are 1 and 2 marbles clockwise of the current marble.
            $cb->insert($lowestMarble);
        }

        if ($print) 
            $cb->print($p);


        if (++$lowestMarble > $lastMarble) {
            break;
        }
    }

    rsort($scores);
    print_r($scores);
    return ($scores[0]);
}

function play($players, $lastMarble, $print = false)
{
    $cb = new CircularBuffer();
    $lowestMarble = 1;
    $scores = array_fill(1, $players, 0);
    $lastHighScore = 0;

    if ($print) 
        $cb->print('-');

    for ($p = 1; $p <= $players; $p = ($p == $players) ? 1 : $p + 1) {

        if ($lowestMarble % 23 == 0) {
            // First, the current player keeps the marble they would have placed, adding it to their score. 
            $scores[$p] += $lowestMarble;
            // In addition, the marble 7 marbles counter-clockwise from the current marble is removed from the circle and also added to the current player's score. 
            // The marble located immediately clockwise of the marble that was removed becomes the new current marble.
            $i = $cb->counterclockwiseIndex(7);
            $marble = $cb->remove($i);
            $scores[$p] += $marble;

            // $diff = $s[0] - $lastHighScore;
            //file_put_contents('points.csv', "$lowestMarble,$p," . implode(',', $scores) . ",{$s[0]}\n", FILE_APPEND);
            //file_put_contents('actions.csv', "$p,$lowestMarble,$marble\n", FILE_APPEND);
            $lastHighScore = $s[0];
        } else {
            // each Elf takes a turn placing the lowest-numbered remaining marble into the circle between the marbles that are 1 and 2 marbles clockwise of the current marble.
            $i = $cb->clockwiseIndex(1);
            $cb->placeAfter($i, $lowestMarble);
        }

        if ($print) 
            $cb->print($p);

        if ($lowestMarble % 71184 == 0) {
            $s = $scores;
            rsort($s);
            print "Winning score for $lowestMarble marbles: {$s[0]}\n";
        }

        if (++$lowestMarble > $lastMarble) {
            break;
        }

        /*
        if ($lowestMarble % 100 == 0) {
            $s = $scores;
            rsort($s);
            file_put_contents('points.csv', "$lowestMarble, {$s[0]}\n", FILE_APPEND);
        }
        */
    }

    rsort($scores);
    // print_r($scores);
    return ($scores[0]);
}

function myErrorHandler($errno, $errstr, $errfile, $errline)
{
    if (!(error_reporting() & $errno)) {
        // This error code is not included in error_reporting, so let it fall
        // through to the standard PHP error handler
        return false;
    }

    switch ($errno) {
    case E_USER_ERROR:
        echo "<b>My ERROR</b> [$errno] $errstr<br />\n";
        echo "  Fatal error on line $errline in file $errfile";
        echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
        echo "Aborting...<br />\n";
        exit(1);
        break;

    case E_USER_WARNING:
        echo "<b>My WARNING</b> [$errno] $errstr<br />\n";
        break;

    case E_USER_NOTICE:
        echo "<b>My NOTICE</b> [$errno] $errstr<br />\n";
        break;

    default:
        echo "Unknown error type: [$errno] $errstr<br />\n";
        break;
    }

    /* Don't execute PHP internal error handler */
    return true;
}
