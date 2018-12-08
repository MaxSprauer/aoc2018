<?php

// Copyright 2018 Max Sprauer

define('WORKERS', 5);

$lines = file('input.txt');
$nodes = array();
$allChildren = array();

// Parse and build list of nodes
foreach ($lines as $line) {
    if (!preg_match('/^Step (\S) must be finished before step (\S) can begin.$/', $line, $matches)) { 
        if (!empty($line)) {
            print "Line does not match: $line\n";
        }
    } else {  
        $name = $matches[1];
        $child = $matches[2];
        
        // Index by both parent and children
        if (!isset($nodes[$name])) {
            $nodes[$name] = new Node($name);
        }
        $nodes[$name]->addChild($child);

        if (!isset($nodes[$child])) {
            $nodes[$child] = new Node($child);
        }
        $nodes[$child]->addParent($name);

        $allChildren[] = $child;
    }
}

// print_r($nodes);

// partOne();
partTwo();

function partOne()
{
    global $allChildren, $nodes;

    // Find head node(s)
    $allChildren = array_unique($allChildren);
    $heads = array_diff(array_keys($nodes), $allChildren);
    sort($heads);
    $path = '';

    // Loop until we're done
    do {
        $curNode = null;

        // Find first node on list that is not visited and has prereqs done
        foreach ($heads as $name) {
            if (false === strpos($path, $name) && prereqComplete($nodes[$name], $path)) {
                $curNode = $name;
                break;        
            }
        }

        if ($curNode != null) {
            // Visit node
            $path .= $curNode;

            // Add its children to list and sort
            $heads = array_merge($heads, $nodes[$curNode]->getChildren());
            $heads = array_unique($heads);
            sort($heads);
            // print_r($heads);
            print "$path\n";
        }
    } while ($curNode != null);
}

function partTwo()
{
    global $allChildren, $nodes;

    // Find head node(s)
    $allChildren = array_unique($allChildren);
    $heads = array_diff(array_keys($nodes), $allChildren);
    sort($heads);
    $path = '';

    // This could done better, huh? 
    $workersDoneTimes = array_fill(0, WORKERS, -1);
    $workersCurrentNode = array_fill(0, WORKERS, null);

    $time = -1;
    print "Time\tW1\tW2\tW3\tW4\tW5\tPath\n";

    // Loop until we're done
    do {
        $time++;

        print "$time\t{$workersCurrentNode[0]}\t{$workersCurrentNode[1]}\t{$workersCurrentNode[2]}\t{$workersCurrentNode[3]}\t{$workersCurrentNode[4]}\t$path\n";

        // Free up any workers that are done
        for ($w = 0; $w < WORKERS; $w++) {
            if ($workersDoneTimes[$w] <= $time) {
                // Visit node
                $curNode = $workersCurrentNode[$w];
                if ($curNode != null) {
                    $path .= $curNode;
                    $workersCurrentNode[$w] = null;

                    // Add its children to list and sort
                    $heads = array_merge($heads, $nodes[$curNode]->getChildren());
                    $heads = array_unique($heads);
                    sort($heads);
                    // print_r($heads);
                    // print "$time: $path\n";
                }
            }
        }

        for ($w = 0; $w < WORKERS; $w++) {
            if ($workersCurrentNode[$w] == null) {
                $curNode = null;
                // Find first node on list that is not visited and has prereqs done and is not being
                // worked on by another node
                foreach ($heads as $name) {
                    if (false === strpos($path, $name) && prereqComplete($nodes[$name], $path)
                        && !in_array($name, $workersCurrentNode)) {
                        $curNode = $name;
                        break;        
                    }
                }

                if ($curNode != null) {
                    $workersCurrentNode[$w] = $curNode;
                    $workersDoneTimes[$w] = $time + getDuration($curNode);
                }
            }
        }

    } while (strlen($path) < 26);

    print "$path\n";
}

function prereqComplete($curNode, $path)
{
    $parents = $curNode->getParents();
    foreach ($parents as $parent) {
        if (strpos($path, $parent) === false) {
            return false;
        }
    }

    return true;
}

function getDuration($name)
{
    return ord($name) - ord('A') + 60 + 1;
}

class Node
{
    private $name;
    private $children = array();
    private $parents = array();

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function addChild($child)
    {
        $this->children[] = $child;
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function addParent($name)
    {
        $this->parents[] = $name;
    }

    public function getParents()
    {
        return $this->parents;
    }
}