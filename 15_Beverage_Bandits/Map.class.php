<?php

// Copyright 2018 Max Sprauer

function in_array_obj($needle, $haystack)
{
    foreach ($haystack as $hay) {
        if ($hay->equals($needle)) {
            return true;
        }
    }

    return false;
}

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
        $openSq = array_unique($openSq);    // Array of Coord objects
        $this->sortCoordArray($openSq);

        // Reachable squares for character
        $this->getReachableForCoord($char, $reachable);

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

    /**
     * @return array of Coord objects
     */
    function getOpenSquaresInRangeOf(Character $char)
    {
        $open = array();

        foreach ([[0, 1], [0, -1], [1, 0], [-1, 0]] as $diffs) {
            $x = $char->x + $diffs[1];
            $y = $char->y + $diffs[0]; 

            if ($x >= 0 && $x < $this->width && $y >= 0 && $y < $this->height && $this->grid[$y][$x] == '.') {
                $open[] = new Coord($x, $y);
            }
        }

        return $open;
    }

    /**
     * Finds reachable squares for a coordinate that are empty
     */
    function getReachableForCoord(Coord $coord, &$reachable)
    {
        foreach ([[-1, 0], [0, -1], [0, 1], [1, 0]] as $diffs) {
            $x1 = $coord->x + $diffs[1];
            $y1 = $coord->y + $diffs[0];
            $newCoord = new Coord($x1, $y1);

            if ($x1 >= 0 && $x1 < $this->width && $y1 >= 0 && $y1 < $this->height 
                && !in_array_obj($newCoord, $reachable) && $this->grid[$y1][$x1] == '.') {
                    $reachable[] = $newCoord;
                    $this->getReachableForCoord($newCoord, $reachable);
            }
        }
    }

    function BFS(Coord $rootNode, Coord $target)
    {
        static $visited = array(); // Array of Coords
        static $queue = array();   // Array of QueueObjs

        $queue[] = new QueueObj($rootNode, array());  // Adds rootNode to path
         
        do {
            if (empty($queue)) {
                assert(0, "Did not find target.");
                return null;
            }

            $curQueueObj = array_shift($queue);
            $curNode = $curQueueObj->coord; 
   
            if ($target->equals($curNode)) {
                return $curQueueObj->path;
            }

            // Add its children to queue in reading order
            foreach ([[-1, 0], [0, -1], [0, 1], [1, 0]] as $diffs) {
                $x1 = $curNode->x + $diffs[1];
                $y1 = $curNode->y + $diffs[0];
                $childCoord = new Coord($x1, $y1);
                if (($target->equals($childCoord) || $this->grid[$y1][$x1] == '.') && !in_array($childCoord, $visited)) {
                    $childQueueObj = new QueueObj($childCoord, $curQueueObj->path); // Adds child to copy of path
                    array_push($queue, $childQueueObj);
                    $visited[] = $childCoord;
                }
            }
        } while (1);
    }
}


class QueueObj
{
    public $path;
    public $coord;

    public function __construct(Coord $c, Array $p)
    {
        $this->coord = $c;
        $this->path = $p;
        $this->path[] = $c;
    }
}