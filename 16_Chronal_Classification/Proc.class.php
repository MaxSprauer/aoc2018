<?php

// Copyright 2018 Max Sprauer

class Proc {
    public $reg = array();

    static $OPCODES = array(
        3 => 'eqir',
        9 => 'gtrr',
        11 => 'gtri',
        12 => 'eqri',
        1 => 'eqrr',
        8 => 'gtir',
        0 => 'banr',
        2 => 'setr',
        6 => 'bani',
        15 => 'seti',
        14 => 'mulr',
        10 => 'addi',
        5 => 'muli',
        13 => 'addr',
        7 => 'borr',
        4 => 'bori',
    );

    function getRegStr()
    {
        return '[' . implode(', ', $this->reg) . ']';
    }

    function setReg($reg)
    {
        $this->reg = $reg;
    }

    // addr (add register) stores into register C the result of adding register A and register B.
    function addr($regA, $regB, $regC)
    {   
        $this->reg[$regC] = (int) $this->reg[$regA] + (int) $this->reg[$regB];
    }

    // addi (add immediate) stores into register C the result of adding register A and value B.
    function addi($regA, $valB, $regC)
    {
        $this->reg[$regC] = (int) $this->reg[$regA] + (int) $valB;
    }

    // mulr (multiply register) stores into register C the result of multiplying register A and register B.
    function mulr($regA, $regB, $regC)
    {   
        $this->reg[$regC] = (int) $this->reg[$regA] * (int) $this->reg[$regB];
    }

    // muli (multiply immediate) stores into register C the result of multiplying register A and value B.
    function muli($regA, $valB, $regC)
    {
        $this->reg[$regC] = (int) $this->reg[$regA] * $valB;
    }

    // banr (bitwise AND register) stores into register C the result of the bitwise AND of register A and register B.
    function banr($regA, $regB, $regC)
    {
        $this->reg[$regC] = ((int) $this->reg[$regA] & (int) $this->reg[$regB]);      
    }
   
    // bani (bitwise AND immediate) stores into register C the result of the bitwise AND of register A and value B.
    function bani($regA, $valB, $regC)
    {
        $this->reg[$regC] = ((int) $this->reg[$regA] & $valB);
    }

    // borr (bitwise OR register) stores into register C the result of the bitwise OR of register A and register B.
    function borr($regA, $regB, $regC)
    {
        $this->reg[$regC] = ((int) $this->reg[$regA] | (int) $this->reg[$regB]);      
    }

    // bori (bitwise OR immediate) stores into register C the result of the bitwise OR of register A and value B.
    function bori($regA, $valB, $regC)
    {
        $this->reg[$regC] = ((int) $this->reg[$regA] | $valB);
    }

    // setr (set register) copies the contents of register A into register C. (Input B is ignored.)
    function setr($regA, $notUsed, $regC)
    {
        $this->reg[$regC] = $this->reg[$regA];
    }

    // seti (set immediate) stores value A into register C. (Input B is ignored.)
    function seti($valA, $notUsed, $regC)
    {
        $this->reg[$regC] = $valA;
    }

    // gtir (greater-than immediate/register) sets register C to 1 if value A is greater than register B. Otherwise, register C is set to 0.
    function gtir($valA, $regB, $regC)
    {
        $this->reg[$regC] = ($valA > $this->reg[$regB]) ? 1 : 0;
    }

    // gtri (greater-than register/immediate) sets register C to 1 if register A is greater than value B. Otherwise, register C is set to 0.
    function gtri($regA, $valB, $regC)
    {
        $this->reg[$regC] = ($this->reg[$regA] > $valB) ? 1 : 0;
    }

    // gtrr (greater-than register/register) sets register C to 1 if register A is greater than register B. Otherwise, register C is set to 0.
    function gtrr($regA, $regB, $regC)
    {
        $this->reg[$regC] = ($this->reg[$regA] > $this->reg[$regB]) ? 1 : 0;
    }

    // eqir (equal immediate/register) sets register C to 1 if value A is equal to register B. Otherwise, register C is set to 0.
    function eqir($valA, $regB, $regC)
    {
        $this->reg[$regC] = ($valA == $this->reg[$regB]) ? 1 : 0;    
    }

    // eqri (equal register/immediate) sets register C to 1 if register A is equal to value B. Otherwise, register C is set to 0.
    function eqri($regA, $valB, $regC)
    {
        $this->reg[$regC] = ($this->reg[$regA] == $valB) ? 1 : 0;
    }

    // eqrr (equal register/register) sets register C to 1 if register A is equal to register B. Otherwise, register C is set to 0.
    function eqrr($regA, $regB, $regC)
    {
        $this->reg[$regC] = ($this->reg[$regA] == $this->reg[$regB]) ? 1 : 0;
    }
}