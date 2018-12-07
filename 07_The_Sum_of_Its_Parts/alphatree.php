<?php

// Copyright 2018 Max Sprauer

$lines = file('input.txt');
$nodes = array();
$allChildren = array();

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