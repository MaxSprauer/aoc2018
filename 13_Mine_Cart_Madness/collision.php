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
            case ' ':
                break;

            default:
                print "Unknown character: {$line[$x]}\n";
                break;
        }

    }

    $y++;
}

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

    do {
        $map->tick();

        //system('cls');
        //$map->print();
        $map->sortCarts();
        $map->checkForCollision(true);
        
        //sleep(1);
    } while ($map->cartsLeft() > 1);

    // $map->tick();
    $map->print();
    print_r($carts);
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
        $this->sortCarts();

        foreach ($this->carts as $cart) {
            list($nextX, $nextY) = $cart->getNextCoords();
            assert(isset($this->tracks[$nextY][$nextX]));
            $cart->tick($nextX, $nextY, $this->tracks[$nextY][$nextX]);
        }
    }

    // Array must be sorted
    function checkForCollision($partTwo = false)
    {
        $prev = null;

        foreach ($this->carts as $cart) {
            if ($prev) {
                if (!$cart->out && !$prev->out && $cart->ordinal($this->width) == $prev->ordinal($this->width)) {
                    if ($partTwo) {
                        print "Taking two more out.\n";
                        $cart->out = true;
                        $prev->out = true;
                    } else {
                        return $cart->x . ',' . $cart->y;
                    }
                }
            }
            $prev = $cart;
        }

        return null;
    }

    function cartsLeft()
    {
        $count = 0;

        foreach ($this->carts as $cart) {
            if (!$cart->out) {
                $count++;
            }
        }

        return $count;
    }

    function sortCarts()
    {
        uasort($this->carts, function($a, $b) {
            global $width;
            return $a->ordinal($width) - $b->ordinal($width);
        });
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