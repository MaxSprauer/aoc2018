<?php

// Copyright 2018 Max Sprauer

require 'Structures/LinkedList/Double.php';

class MarbleNode extends Structures_LinkedList_DoubleNode 
{
    public $marble;

    function __construct($marble) {
        $this->marble = $marble;
    }
}

class CircularBuffer2
{
    protected $list = null;
    public $length = 0;

    public function __construct()
    {
        $this->list = new Structures_LinkedList_Double(new MarbleNode(0));
        $this->length = 1;
    }

    public function insert($marble)
    {
        $existing = $this->list->next();
        if (false === $existing) {
            $existing = $this->list->rewind();
        }
        $this->list->insertNode(new MarbleNode($marble), $existing);
        $this->list->next();    // I think we need to move pointer up
        $this->length++;
    }

    public function remove()
    {
        for ($i = 0; $i < 7; $i++) {
            $node = $this->list->previous();
            if (false === $node) {
                $node = $this->list->end();
            }
        }

        $this->list->deleteNode($node);
        $this->list->next();    // Delete takes us back one
        $this->length--;

        // Trying to make sure memory is freed here
        $marble = $node->marble;
        unset($node);

        return $marble;
    }

    public function print($player)
    {
/*
        printf(' [%2d] ', $player);

        for ($i = 0; $i < count($this->buffer); $i++) {
            if ($i == $this->currentIndex) {
                printf('(%2d) ', $this->buffer[$i]);
            } else {
                printf(' %2d  ', $this->buffer[$i]);
            }
        }

        print "\n";
        */
    }
}
