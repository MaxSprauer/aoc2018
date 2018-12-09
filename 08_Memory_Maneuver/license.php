<?php

// Copyright 2018 Max Sprauer
// Yes, I broke xdebug with my sweet recursion
ini_set('xdebug.max_nesting_level', 10000);

$input = file('input.txt')[0];
// $input = '2 3 0 3 10 11 12 1 1 0 1 99 2 1 1 2';
$nodes = array();

$numChildren = strtok($input, ' ');
$numMetadata = strtok(' ');
$rootNode = new Node($numChildren, $numMetadata);
// print_r($nodes);

//partOne();
partTwo();

function partOne() 
{
    global $nodes;

    // I could have summed this along the way
    print array_reduce($nodes, function($carry, $item) {
        return $carry + $item->metadataSum;
    });
}

function partTwo() 
{
    global $rootNode;

    print $rootNode->value;
}

class Node
{
    public $metadataSum = 0;
    public $value = 0;

    // A recursive constructor... can't say I've done this before.
    public function __construct($numChildren, $numMetadata)
    {
        global $input, $nodes;
        $this->numChildren = $numChildren;
        $this->numMetadata = $numMetadata;
        $this->children = array();
        $this->metadata = array();
        $nodes[] = $this;

        // Create children recursively
        for ($i = 0; $i < $numChildren; $i++) {
            $nc = strtok(' ');
            $nm = strtok(' ');
            if ($nc !== false && $nm !== false) {
                $this->children[] = new Node($nc, $nm);
            }
        }

        // Read and sum metadata byes
        for ($i = 0; $i < $numMetadata; $i++) {
            $this->metadata[$i] = strtok(' ');
            $this->metadataSum += $this->metadata[$i];
        }

        // Calculate value
        if ($numChildren == 0) {
            $this->value = $this->metadataSum;
        } else {
            for ($i = 0; $i < $numMetadata; $i++) {
                $index = $this->metadata[$i] - 1;
                if (isset($this->children[$index])) {
                    $this->value += $this->children[$index]->value;
                }
            }
        }
    }
}
