<?php

// Copyright 2018 Max Sprauer

class CircularBuffer
{
    public $buffer = array(0);
    public $currentIndex = 0;

    public function __construct()
    {
    }

    public function clockwiseIndex($n)
    {
        return (($this->currentIndex + $n) % count($this->buffer));
    }

    public function counterclockwiseIndex($n)
    {
        $index = $this->currentIndex - $n;
        if ($index < 0) {
             $index = count($this->buffer) + $index;
        } 
        return $index;
    }

    public function placeAfter($index, $marble)
    {
        assert($index >= 0 && $index < count($this->buffer), "$index");
        array_splice($this->buffer, $index + 1, 0, array($marble));
        $this->currentIndex = ($index + 1) % count($this->buffer);
    }

    public function remove($index)
    {
        assert($index >= 0 && $index < count($this->buffer), "$index");
        $marble = $this->buffer[$index];
        array_splice($this->buffer, $index, 1);
        $this->currentIndex = $index % count($this->buffer);
        return $marble;
    }

    public function print($player)
    {
        printf(' [%2d] ', $player);

        for ($i = 0; $i < count($this->buffer); $i++) {
            if ($i == $this->currentIndex) {
                printf('(%2d) ', $this->buffer[$i]);
            } else {
                printf(' %2d  ', $this->buffer[$i]);
            }
        }

        print "\n";
    }
}