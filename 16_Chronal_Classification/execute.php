<?php

// Copyright 2018 Max Sprauer
// Part Two

require_once 'Proc.class.php';

$proc = new Proc();
$proc->setReg([0, 0, 0, 0]);

$input = file('opcodes.txt');

foreach ($input as $line) {
    list($opCode, $a, $b, $c) = explode(' ', trim($line));
    $cmd = $proc::$OPCODES[$opCode];
    $proc->$cmd($a, $b, $c);
}

print $proc->getRegStr();
