<?php

// Copyright 2018 Max Sprauer

class Map
{
    public $chars = array(); 

    public function __construct($filename)
    {
        $this->grid = file($filename);
        $this->width = strlen(trim($this->grid[0]));
        $this->height = count($this->grid);
    }

    public function createCharacters()
    {
        for ($y = 0; $y < $this->height; $y++) {
            for ($x = 0; $x < $this->width; $x++) {
                switch ($this->grid[$y][$x]) {
                    case '#':
                    case '.':
                        break;

                    case 'G':
                        $this->chars[] = new Goblin($x, $y);
                        break;

                    case 'E':
                        $this->chars[] = new Elf($x, $y);
                        break;

                    default:
                        assert(0, $this->grid[$y][$x]);
                        break;
                }
            }
        }
    }

    public function print()
    {
        for ($y = 0; $y < $this->height; $y++) {
            $rowNotes = '';
            for ($x = 0; $x < $this->width; $x++) {        
                if ($this->grid[$y][$x] == 'E' || $this->grid[$y][$x] == 'G') {
                    $char = $this->getCharAt($x, $y);
                    $rowNotes .= ' ' . $char->getDescription();  
                }
            }

            print(trim($this->grid[$y]));
            print "  $rowNotes\n";
        }
    }

    public function getCharAt($x, $y)
    {
        foreach ($this->chars as $char) {
            if ($char->x == $x && $char->y == $y) {
                return $char;
            }
        }

        return null;
    }

    /*
    public function ordinal($width)
    {
        return ($this->y * $width) + $this->x;
    }
    
    function sortCarts()
    {
        uasort($this->carts, function($a, $b) {
            global $width;
            return $a->ordinal($width) - $b->ordinal($width);
        });

        $this->carts = array_values($this->carts);  // Renumber
    }
*/
    public function takeTurnForChar($char)
    {
        // 1 Get targets
        $targets = array_filter($this->chars, function($val) use ($char) {
            return (get_class($char) != get_class($val));
        });
        
        if (empty($targets)) {
            return null;
        }

        // 2 In range

        // 2a Already in range of target?
        $inRangeOfTargets = array();;
        foreach ($targets as $target) {
            if ($char->inRangeOf($target)) {
                $inRangeOfTargets[] = $target;
            }
        }

        // 2b Open squares in range of target
        $openSq = array();
        foreach ($targets as $target) {
            $openSq = array_merge($openSq, $this->getOpenSquaresInRangeOf($target));
        }
        $openSq = array_unique($openSq);

        // Nowhere to go, end the turn.
        if (empty($inRangeOfTargets) && empty($openSq)) {
            return null;
        }

        // If in range, attack

        // Move

        // Reachable

        // Nearest

        // Chosen



    }

    function getOpenSquaresInRangeOf($char)
    {
        $open = array();

        foreach ([[0, 1], [0, -1], [1, 0], [-1, 0]] as $diffs) {
            $x = $char->x + $diffs[1];
            $y = $char->y + $diffs[0]; 

            if ($x >= 0 && $x < $this->width && $y >= 0 && $y < $this->height && $this->grid[$y][$x] == '.') {
                $open[] = "$x,$y";
            }
        }

        return $open;
    }

    function getReachableFor($x0, $y0, &$reachable)
    {
        foreach ([[0, 1], [0, -1], [1, 0], [-1, 0]] as $diffs) {
            $x1 = $x0 + $diffs[1];
            $y1 = $y0 + $diffs[0];
            
            if ($x1 >= 0 && $x1 < $this->width && $y1 >= 0 && $y1 < $this->height 
                && !isset($reachable["$x1,$y1"]) && $this->grid[$y1][$x1] == '.') {
                    $reachable["$x1,$y1"] = 1;
                    $this->getReachableFor($x1, $y1, $reachable);
            }
        }
    }
}
