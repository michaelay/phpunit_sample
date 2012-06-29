<?php
class Calculator
{
    /**
     *
     * @assert(0, 0) == 0
     * @assert(1, 0) == 1
     * @assert(0, 2) == 2
     * @assert(-1, -2) == -3 
     */
    public function add($a, $b)
    {
        return $a + $b;
    }
}
