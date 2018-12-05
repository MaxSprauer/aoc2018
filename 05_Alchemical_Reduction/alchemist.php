<?php

// Copyright 2018 Max Sprauer

$str = trim(file_get_contents('input.txt'));

// partOne();
partTwo();

function partOne() 
{
    global $str;

    do { 
        print strlen($str) . "\n"; 
    } while (reduce($str));

    print "$str\n" . strlen($str) . "\n";
}

function partTwo()
{
    global $str;
    $lengths = array();

    foreach (range('a', 'z') as $letter) {
        $upper = chr(ord($letter) - 32);
        $minusLetter = preg_replace("/[$letter|$upper]/", '', $str);

        print "$letter:\n";
        do { 
            print "\t" . strlen($minusLetter) . "\n"; 
        } while (reduce($minusLetter));

        $lengths[$letter] = strlen($minusLetter);
    }

    print_r($lengths);
}

// This could be more efficient in many ways, starting with not using PHP.
function reduce(&$str)
{
    $match = false;

    for ($i = strlen($str) - 2; $i >= 0; $i--) {

        if ($i >= strlen($str) - 2) {
            continue;   // I think this happens when we remove two right at the end
        }

        $a = $str[$i];
        $b = $str[$i + 1];

        // if (0 == strcasecmp($a, $b) && ((ctype_upper($a) && ctype_lower($b)) || (ctype_lower($a) && ctype_upper($b)))) {
        if (abs(ord($a) - ord($b)) == 32) {
            $str = substr($str, 0, $i) . substr($str, $i + 2);
            $match = true;
        }
    }

    return $match;
}