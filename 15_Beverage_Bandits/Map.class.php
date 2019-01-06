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
    
    function sortCharArray(&$chars)
    {
        uasort($chars, function($a, $b) {
            return $a->ordinal($this->width) - $b->ordinal($this->width);
        });

        $chars = array_values($chars);  // Renumber
    }

    function sortCoordArray(&$coord)
    {
        uasort($coord, function($a, $b) {
            list($xa, $ya) = explode(',', $a);
            list($xb, $yb) = explode(',', $b);

            return ($ya * $this->width + $xa) - ($yb * $this->width + $xb);
        });

        $coords = array_values($coord);  // Renumber
    }

    public function doRound()
    {
        $this->sortCharArray($this->chars);

        foreach ($this->chars as $char) {
            $this->takeTurnForChar($char);
        }
    }

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
        $inRangeOfTargets = array();
        foreach ($targets as $target) {
            if ($char->inRangeOf($target)) {
                $inRangeOfTargets[] = $target;
            }
        }
        $this->sortCharArray($inRangeOfTargets);

        // 2b Open squares in range of target
        $openSq = array();
        foreach ($targets as $target) {
            $openSq = array_merge($openSq, $this->getOpenSquaresInRangeOf($target));
        }
        $openSq = array_unique($openSq);    // Array of "x,y" strings
        $this->sortCoordArray($openSq);

        // Reachable squares for character
        $this->getReachableForCoord($char->x, $char->y, $reachable);

        // Remove unreachable from open squares in range of target
        $openAndReachable = array_intersect($openSq, $reachable);
        $openAndReachable = array_values($openAndReachable);    // Renumber

        // Nowhere to go, end the turn.
        if (empty($inRangeOfTargets) && empty($openAndReachable)) {
            return null;
        }

        // If in range, attack


        // Move

        // Reachable


        // Nearest
        $nearest = array();
        foreach ($openAndReachable as $oar) {
            list($x, $y) = explode(',', $oar);
            $nearest[$oar] = $char->getMovesToCoord($x, $y);
        }
        
        // Sort by fewest moves and get the best number
        sort($nearest);
        $best = current($nearest);
        
        // Filter array by lowest number of moves
        $nearest = array_filter($nearest, function($v, $k) use ($best) {
            return ($v == $best);
        }, ARRAY_FILTER_USE_BOTH);

        // Sort array in reading order
        $this->sortCoordArray($nearest);

        // Get the chosen coordinate
        $chosen = current($nearest);


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

    /**
     * Finds reachable squares for a coordinate that are empty
     */
    function getReachableForCoord($x0, $y0, &$reachable)
    {
        foreach ([[0, 1], [0, -1], [1, 0], [-1, 0]] as $diffs) {
            $x1 = $x0 + $diffs[1];
            $y1 = $y0 + $diffs[0];
            
            if ($x1 >= 0 && $x1 < $this->width && $y1 >= 0 && $y1 < $this->height 
                && !isset($reachable["$x1,$y1"]) && $this->grid[$y1][$x1] == '.') {
                    $reachable["$x1,$y1"] = 1;
                    $this->getReachableForCoord($x1, $y1, $reachable);
            }
        }
    }

    function BFS(Character $root, Coord $target)
    {
        $path = array();
        $visited = array(); // Array of Coords
        $nextLevel = array();   // Array of Coords

        $queue[] = $root;
        $visited[] = $root;

        while (!empty($queue)) {
            // Get current Node
            $node = array_shift($queue);

            // Add its children to queue in reading order



        }



    }

    function BFSrecurse($path, &$visited, )
}
