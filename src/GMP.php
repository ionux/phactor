<?php
/******************************************************************************
 * This file is part of the Phactor PHP project. You can always find the latest
 * version of this class and project at: https://github.com/ionux/phactor
 *
 * Copyright (c) 2015-2018 Rich Morgan, rich@richmorgan.me
 *
 * The MIT License (MIT)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
 * the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 * FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 * IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 * CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 ******************************************************************************/

namespace Phactor;

/**
 * GMP math class used by the main math class.
 *
 * @author Rich Morgan <rich@richmorgan.me>
 */
final class GMP
{
    /**
     * Adds two arbitrary precision numbers.
     *
     * @param  string $a  The first number.
     * @param  string $b  The second number.
     * @return string
     */
    public function add($a, $b)
    {
        return gmp_strval(gmp_add($a, $b));
    }
    
    /**
     * Multiplies two arbitrary precision numbers.
     *
     * @param  string $a  The first number.
     * @param  string $b  The second number.
     * @return string
     */
    public function mul($a, $b)
    {
        return gmp_strval(gmp_mul($a, $b));
    }
    
    /**
     * Divides two arbitrary precision numbers.
     *
     * @param  string $a  The first number.
     * @param  string $b  The second number.
     * @return string
     */
    public function div($a, $b)
    {
        return gmp_strval(gmp_div($a, $b));
    }
    
    /**
     * Subtracts two arbitrary precision numbers.
     *
     * @param  string $a  The first number.
     * @param  string $b  The second number.
     * @return string
     */
    public function sub($a, $b)
    {
        return gmp_strval(gmp_sub($a, $b));
    }
    
    /**
     * Calculates the modulo of two numbers.
     * There's a slight quirk in GMP's implementation
     * so this returns a mathematically correct answer
     * if you specify the $correct parameter.
     *
     * @param  string  $a        The first number.
     * @param  string  $b        The second number.
     * @param  boolean $correct  Flag to calculate mathematically correct modulo.
     * @return string
     */
    public function mod($a, $b, $correct = false)
    {
        if ($correct === true) {
            if (gmp_cmp($a, '0') < 0) {
                return gmp_strval(gmp_sub(gmp_mod($a, $b), $a));
            }
        }

        return gmp_strval(gmp_mod($a, $b));
    }

    /**
     * Raises an arbitrary precision number to another,
     * reduced by a specified modulus.
     *
     * @param  string  $a        The first number.
     * @param  string  $b        The exponent.
     * @param  string  $c        The modulus.
     * @return string            The result of the operation.
     */
    public function powmod($a, $b, $c)
    {
        return gmp_strval(gmp_powm($a, $b, $c));
    }

    /**
     * Compares two arbitrary precision numbers.
     *
     * @param  string $a  The first number.
     * @param  string $b  The second number.
     * @return integer
     */
    public function comp($a, $b)
    {
        return gmp_cmp($a, $b);
    }

    /**
     * GMP's inverse modulo function, where ax = 1(mod p).
     *
     * @param  string $a  The number to inverse modulo.
     * @param  string $b  The modulus.
     * @return string
     */
    public function inv($a, $b)
    {
        return gmp_strval(gmp_invert($a, $b));
    }

    /**
     * Returns the string value of a GMP resource.
     *
     * @param  mixed  $a  Number to normalize.
     * @return string
     */
    public function normalize($a)
    {
        return gmp_strval($a);
    }

    /**
     * Raises 'a' to the power 'b'.
     *
     * @param  string $a  The first number.
     * @param  string $b  The second number.
     * @return string
     */
    public function power($a, $b)
    {
        return gmp_strval(gmp_pow($a, $b));
    }

    /**
     * Calculates & returns the integer portion of the square root.
     *
     * @param  string $a  The first number.
     * @return string
     */
    public function sqrt($a)
    {
        return gmp_strval(gmp_sqrt($a));
    }
}
