<?php

// Copyright 2018 Max Sprauer
$lines = file('input.txt');

$tracks = array();
$carts = array();
$y = 0;
$width = 0;
$dirMap = array('^' => '|', 'v' => '|', '<' => '-', '>' => '-');

foreach ($lines as $line) {
    if (strlen($line) > $width) {
        $width = strlen($line);
    }
    for ($x = 0; $x < strlen($line); $x++) {
        switch ($line[$x]) {
            case '/':
            case '\\':
            case '|':
            case '-':
            case '+':
                $tracks[$y][$x] = $line[$x];
                break;

            case '^':
            case 'v':
            case '<':
            case '>':
                $tracks[$y][$x] = $dirMap[$line[$x]];
                $carts[] = new Cart($x, $y, $line[$x]);
                break;

            case "\n":
            case "\r":
            case ' ':
                break;

            default:
                assert(0, "Unknown character: {$line[$x]}\n");
                break;
        }

    }

    $y++;
}

// Fake "constants"
$MAZE_HEIGHT = $y;
$MAZE_WIDTH = $width;

// print_r($carts);
// partOne($tracks, $carts, $width);
partTwo($tracks, $carts, $width);

function partOne($tracks, $carts, $width)
{
    $map = new Map($tracks, $carts, $width);
    $col = null;

    do {
        $map->tick();

        //system('cls');
        //$map->print();
        $map->sortCarts();
        $col = $map->checkForCollision();
        
        //sleep(2);
    } while ($col == null);

    print "$col\n";
}

function partTwo($tracks, $carts, $width)
{
    $map = new Map($tracks, $carts, $width);
    $oneLeft = null;
    $ticks = 0;

    do {
//        $map->print();
        $map->tick2();  // Also removes collisions
        $ticks++;

        if ($ticks % 10000 == 0) {
            print "Ticks: $ticks, Carts left: " . count($map->carts);
            foreach ($map->carts as $cart) {
                print " ({$cart->x}, {$cart->y}) ";
            }
            print "\n";
        }
    } while (count($map->carts) > 1);

    $map->print();
    print_r($map->carts);
}

class Map
{
    public $carts;

    public function __construct($tracks, $carts, $width)
    {
        $this->tracks = $tracks;
        $this->carts = $carts;
        $this->width = $width;
    }

    public function print()
    {
        for ($y = 0; $y < count($this->tracks); $y++) {
            $line = '';

            for ($x = 0; $x < $this->width; $x++) {
                $icon = $this->getCartAtLocation($x, $y);
                if ($icon !== null) {
                    $line .= $icon;
                } else if (isset($this->tracks[$y][$x])) {
                    $line .= $this->tracks[$y][$x];
                } else {
                    $line .= ' ';
                }
            }

            print "$line\n";
        }
    }

    public function getCartAtLocation($x, $y)
    {
        foreach ($this->carts as $cart) {
            if ($cart->x == $x && $cart->y == $y) {
                return $cart->dir;
            }
        }

        return null;
    }

    public function tick()
    {
        global $MAZE_WIDTH, $MAZE_HEIGHT;

        $this->sortCarts();

        foreach ($this->carts as $cart) {
            list($nextX, $nextY) = $cart->getNextCoords();
            assert($nextX >= 0 && $nextX < $MAZE_WIDTH, "Bad coords: $nextX, $nextY, maxX: $MAZE_WIDTH");
            assert($nextY >= 0 && $nextY < $MAZE_HEIGHT, "Bad coords: $nextX, $nextY, maxY: $MAZE_HEIGHT");
            assert(isset($this->tracks[$nextY][$nextX]));
            $cart->tick($nextX, $nextY, $this->tracks[$nextY][$nextX]);
        }
    }

    public function tick2()
    {
        global $MAZE_WIDTH, $MAZE_HEIGHT;

        $this->sortCarts();

        for ($i = 0; $i < count($this->carts); $i++) {
            $cart = $this->carts[$i];
            list($nextX, $nextY) = $cart->getNextCoords();
            assert($nextX >= 0 && $nextX < $MAZE_WIDTH, "Bad coords: $nextX, $nextY, maxX: $MAZE_WIDTH");
            assert($nextY >= 0 && $nextY < $MAZE_HEIGHT, "Bad coords: $nextX, $nextY, maxY: $MAZE_HEIGHT");
            assert(isset($this->tracks[$nextY][$nextX]));

            // Are we crashing?
            $crash = false;            
            for ($j = 0; $j < count($this->carts); $j++) {
                if ($i == $j) {
                    continue;
                }

                $other = $this->carts[$j];
                if ($other->x == $nextX && $other->y == $nextY) {
                    unset($this->carts[$j]);
                    unset($this->carts[$i]);
                    $this->carts = array_values($this->carts);  // Renumber
                    $i -= 3;  // Removed 2 and account for the loop increment
                    if ($i < 0) {
                        $i = -1;
                    }
                    $crash = true;
                    break;
                }
            }

            if (!$crash) {
                $cart->tick($nextX, $nextY, $this->tracks[$nextY][$nextX]);
            }
        }
    }

    // Array must be sorted
    function checkForCollision()
    {
        $prev = null;

        foreach ($this->carts as $cart) {
            if ($prev) {
                if (!$cart->out && !$prev->out && $cart->ordinal($this->width) == $prev->ordinal($this->width)) {
                    return $cart->x . ',' . $cart->y;
                }
            }
            $prev = $cart;
        }

        return null;
    }

    function sortCarts()
    {
        uasort($this->carts, function($a, $b) {
            global $width;
            return $a->ordinal($width) - $b->ordinal($width);
        });

        $this->carts = array_values($this->carts);  // Renumber
    }
}

class Cart
{
    private const TURN_LEFT = 0;
    private const TURN_STRAIGHT = 1;
    private const TURN_RIGHT = 2;
    private const TURN_MAX = 3;

    private $nextTurn = self::TURN_LEFT;
    public $x, $y, $dir, $out;

    public function __construct($x, $y, $direction)
    {
        $this->x = $x;
        $this->y = $y;
        $this->dir = $direction;
        $this->out = false;
    }

    public function ordinal($width)
    {
        return ($this->y * $width) + $this->x;
    }

    public function tick($nextX, $nextY, $nextTrack)
    {
        $turnRight = ['^' => '>', '>' => 'v', 'v' => '<', '<' => '^'];
        $turnLeft = ['^' => '<', '<' => 'v', 'v' => '>', '>' => '^'];

        switch ($nextTrack) {
            case '+':
                switch ($this->nextTurn) {
                    case self::TURN_LEFT:
                        $this->dir = $turnLeft[$this->dir];
                        break;
                    case self::TURN_STRAIGHT:
                        break;
                    case self::TURN_RIGHT:
                        $this->dir = $turnRight[$this->dir];
                        break;
                    default:
                        assert(0, $this->nextTurn);
                        break;
                }

                $this->nextTurn = ($this->nextTurn + 1) % self::TURN_MAX;
                break;

            case '/':
                if ($this->dir == '<') {
                    $this->dir = 'v';
                } else if ($this->dir == '^') {
                    $this->dir = '>';
                } else if ($this->dir == '>') {
                    $this->dir = '^';
                } else if ($this->dir == 'v') {
                    $this->dir = '<';
                } else {
                    assert(0, $this->dir);
                }

                break;

            case '\\':
                if ($this->dir == '>') {
                    $this->dir = 'v';
                } else if ($this->dir == '^') {
                    $this->dir = '<';
                } else if ($this->dir == 'v') {
                    $this->dir = '>';
                } else if ($this->dir == '<') {
                    $this->dir = '^';
                } else {
                    assert(0, $this->dir);
                }

                break;

            case '|':
                assert($this->dir == '^' || $this->dir == 'v', $this->dir);
                break;

            case '-':
                assert($this->dir == '<' || $this->dir == '>', $this->dir);
                break;

            default:
                assert(0, "Next: ($nextTrack)");
                break;
        }

        $this->x = $nextX;
        $this->y = $nextY;
    }

    public function getNextCoords()
    {
        $nextX = $this->x;
        $nextY = $this->y;

        switch ($this->dir) {
            case '^':
                $nextY--;
                break;

            case 'v':
                $nextY++;
                break;

            case '<':
                $nextX--;
                break;

            case '>':
                $nextX++;
                break;

            default:
                assert(0, "{$this->dir}");
                break;
        }

        return [$nextX, $nextY];
    }
}