<?php

// Copyright 2018 Max Sprauer
// Part One

require_once 'Proc.class.php';

$input = file_get_contents('input.txt');

/* Before: [0, 3, 2, 1]
 * 5 0 2 2
 * After:  [0, 3, 0, 1]
*/
preg_match_all('/Before:\s+\[(\d+), (\d+), (\d+), (\d+)\]\s+(\d+) (\d+) (\d+) (\d+)\s+After:\s+(\[\d+, \d+, \d+, \d+\])/', 
    $input, $samples, PREG_SET_ORDER);

// print_r($samples);
print "Samples: " . count($samples) . "\n";

test();
partOne($samples);


function test()
{
    $proc = new Proc();
    $proc->setReg([0, 33, 15, 6]);
    $proc->addr(1, 2, 3);
    assert($proc->getRegStr() == '[0, 33, 15, 48]');
    $proc->addi(1, 66, 0);
    assert($proc->getRegStr() == '[99, 33, 15, 48]');
    $proc->muli(1, 2, 2);
    assert($proc->getRegStr() == '[99, 33, 66, 48]');
}

function partOne($samples)
{
    $proc = new Proc();
    $threeOrMore = 0;

    for ($i = 0; $i < count($samples); $i++) {
        $before = array_slice($samples[$i], 1, 4);
        list($opCode, $a, $b, $c) = array_slice($samples[$i], 5, 4);
        $after = $samples[$i][9];
        $matches = array();

        foreach (['addr', 'addi', 'mulr', 'muli', 'banr', 'bani', 'borr', 'bori', 'setr', 'seti', 'gtir', 'gtri', 'gtrr', 'eqir', 'eqri', 'eqrr'] as $op) {
            $proc->setReg($before);
            $proc->$op($a, $b, $c);
            if ($proc->getRegStr() == $after) {
                $matches[] = $op;
            }
        }

        if (count($matches) >= 3) {
            $threeOrMore++;
        } 
        
        // This is where I figure out the opcodes.  I could have put this in a loop and added known opcodes
        // to the list dynamically as I found them.  But I didn't; I ran it by hand a bunch of times.

        // Look for a match (only one possible) after other known matches are ruled out
        $diff = array_diff($matches, Proc::$OPCODES);
        if (count($diff) == 1) {
            $name = array_shift($diff);
            print "$name: $opCode\n";
        }
    }

    print "Match three or more: $threeOrMore\n";
}

