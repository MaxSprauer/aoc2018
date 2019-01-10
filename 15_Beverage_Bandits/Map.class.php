<?php

// Copyright 2018 Max Sprauer

function in_array_obj($needle, $haystack)
{
    if (!$haystack || empty($haystack)) {
        return false;
    }

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

        $this->createCharacters();
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
                        assert(0, "[$x, $y] = " . $this->grid[$y][$x]);
                        break;
                }
            }
        }
    }

    public function print($i = null)
    {
        if ($i != null) {
            print "Iteration $i:\n";
        }

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

        print "\n";
    }

    public function printPaths($paths)
    {
        foreach ($paths as $path) {
            foreach ($path as $p) {
                print "($p->x, $p->y) ";
            }
            print "\n";
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

    function sortCoordArray(&$coords)
    {
        uasort($coords, function($a, $b) {
            return $a->ordinal($this->width) - $b->ordinal($this->width);
        });

        $coords = array_values($coords);  // Renumber
    }

    public function doRound()
    {
        $done = false;

        $this->sortCoordArray($this->chars);

        foreach ($this->chars as &$char) {
            $done = $done || $this->takeTurnForChar($char);
        }

        return $done;
    }

    /**
     * @return boolean Is this character done?
     */
    public function takeTurnForChar(&$char)
    {
        // 1 Get targets
        $targets = array_filter($this->chars, function($val) use ($char) {
            return (get_class($char) != get_class($val));
        });

        // If no targets left, we're done.
        if (empty($targets)) {
            return true;
        }

        // Already in range of target?
        $inRangeOfTargets = array();
        foreach ($targets as $target) {
            if ($char->inRangeOf($target)) {
                $inRangeOfTargets[] = $target;
            }
        }
        $this->sortCoordArray($inRangeOfTargets);

        // If a target is not range, move
        if (empty($inRangeOfTargets)) {
            // 2b Open squares in range of target
            $openSq = array();
            foreach ($targets as $target) {
                $openSq = array_merge($openSq, $this->getOpenSquaresInRangeOf($target));
            }
            $openSq = array_unique($openSq, SORT_REGULAR);    // Array of Coord objects
            $this->sortCoordArray($openSq);

            // Reachable squares for character
            $reachable = array();
            $this->getReachableForCoord($char, $reachable);

            // Remove unreachable from open squares in range of target
            $openAndReachable = array_intersect($openSq, $reachable);
            $openAndReachable = array_values($openAndReachable);    // Renumber

            // Nowhere to go, end the turn.
            if (empty($openAndReachable)) {
                return false;
            }

            // Nearest
            $nearest = array();
            foreach ($openAndReachable as $oar) {
                $nearest[(string) $oar] = $char->getMovesToCoord($oar);
            }

            // Sort by fewest moves and get the best number
            asort($nearest);
            $best = array_values($nearest)[0];

            // Filter array by lowest number of moves
            $nearestCoords = array();
            foreach ($nearest as $coordStr => $moves) {
                if ($moves == $best) {
                    $nearestCoords[] = Coord::NewFromString($coordStr);
                }
            }

            // Sort array in reading order
            $this->sortCoordArray($nearestCoords);

            // Get the chosen coordinate
            $chosen = $nearestCoords[0];

            // Get shortest path
            $paths = $this->BFS($char, $chosen);

            // See if there are valid paths (length > 1)
            if (empty($paths) || count($paths[0]) < 2) {
                return false;
            }

            $path = $paths[0];
            $newCoord = $path[1];
            $this->moveCharacter($char, $newCoord);

            // Again check in range of targets
            $inRangeOfTargets = array();
            foreach ($targets as $target) {
                if ($char->inRangeOf($target)) {
                    $inRangeOfTargets[] = $target;
                }
            }
            $this->sortCoordArray($inRangeOfTargets);
        }

        // If in range, attack
        if (!empty($inRangeOfTargets)) {
            // Find unit with fewest hit points
            $minHP = INF;
            $target = null;

            // $inRangeOfTargets should be in reading order
            foreach ($inRangeOfTargets as $t) {
                if ($t->hitPoints < $minHP) {
                    $target = $t;
                    $minHP = $t->hitPoints;
                }
            }

            $this->attackCharacter($char, $target);
        }

        return false;
    }

    public function moveCharacter(Character &$char, Coord $new)
    {
        $this->grid[$char->y][$char->x] = '.';
        assert($this->grid[$new->y][$new->x] == '.', "grid[$new->x][$new->y] is " . $this->grid[$new->y][$new->x]);
        $this->grid[$new->y][$new->x] = ($char instanceof Elf) ? 'E' : 'G';
        $char->x = $new->x;
        $char->y = $new->y;
    }

    public function attackCharacter(Character $source, Character &$target)
    {
        $target->hitPoints -= $source->power;

        if ($target->hitPoints <= 0) {
            $this->grid[$target->y][$target->x] = '.';
            $index = array_search($target, $this->chars);
            assert($index !== false);
            unset($this->chars[$index]);
        }
    }

    /**
     * @return array of Coord objects in reading order
     */
    function getOpenSquaresInRangeOf(Character $char)
    {
        $open = array();

        foreach ([[-1, 0], [0, -1], [0, 1], [1, 0]] as $diffs) {
            $x = $char->x + $diffs[1];
            $y = $char->y + $diffs[0]; 

            if ($x >= 0 && $x < $this->width && $y >= 0 && $y < $this->height && $this->grid[$y][$x] == '.') {
                $open[] = Coord::NewFromCoords($x, $y);
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
            $newCoord = Coord::NewFromCoords($x1, $y1);

            if ($x1 >= 0 && $x1 < $this->width && $y1 >= 0 && $y1 < $this->height 
                && !in_array_obj($newCoord, $reachable) && $this->grid[$y1][$x1] == '.') {
                    $reachable[] = $newCoord;
                    $this->getReachableForCoord($newCoord, $reachable);
            }
        }
    }

    /**
     * Perform a breadth-first search based on Dijkstra's algorithm to find all the shortest paths.
     * Each queue entry maintains its own copy of its path and its visited array.  This allows each path
     * to grow without being stunted by nodes visited by other paths.
     * @return Array Array of path arrays
     */
    function BFS(Coord $rootNode, Coord $target)
    {
        $done = false;
        $queue = array();           // Array of QueueObjs
        $shortestPaths = array();   // Array of path arrays (Coords)
        $visited = array();         // Array of Coords

        $queue[] = new QueueObj($rootNode, array());  // Adds rootNode to path
         
        do {
            if ($done || empty($queue)) {
                if (!empty($shortestPaths)) {
                    return $shortestPaths;
                } else {
                    assert(0, "Did not find target.");
                    return null;
                }
            }

            $curQueueObj = array_shift($queue);
            $curNode = $curQueueObj->coord;

            // If the current path is longer than the shortest, stop the search.
            if (!empty($shortestPaths) && (count($curQueueObj->path) > $shortestPaths[0])) {
                $done = true;
                continue;
            }

            if ($target->equals($curNode)) {
                // We found a path.  Add to shortestPaths array.
                if (empty($shortestPaths)) {
                    $shortestPaths[] = $curQueueObj->path;
                } else if (count($curQueueObj->path) == count($shortestPaths[0])) {
                    $shortestPaths[] = $curQueueObj->path;
                }
                continue;
            }

            // Add its children to queue in reading order
            foreach ([[-1, 0], [0, -1], [0, 1], [1, 0]] as $diffs) {
                $x1 = $curNode->x + $diffs[1];
                $y1 = $curNode->y + $diffs[0];
                $childCoord = Coord::NewFromCoords($x1, $y1);
                if (($target->equals($childCoord) || $this->grid[$y1][$x1] == '.') && !in_array($childCoord, $visited)) {
                    if (!$target->equals($childCoord)) {
                        $visited[] = $childCoord;
                    }
                    $childQueueObj = new QueueObj($childCoord, $curQueueObj->path); // Adds child to copy of path
                    array_push($queue, $childQueueObj);
                }
            }
        } while (1);
    }
}


class QueueObj
{
    public $path;
    public $coord;

    public function __construct(Coord &$c, Array $p)
    {
        $this->coord = $c;
        $this->path = $p;
        $this->path[] = $c;
    }
}