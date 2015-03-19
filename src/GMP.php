<?php
/******************************************************************************
 * This file is part of the Phactor PHP project. You can always find the latest
 * version of this class and project at: https://github.com/ionux/phactor
 *
 * Copyright (c) 2015 Rich Morgan, rich.l.morgan@gmail.com
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, write to the Free Software Foundation, Inc., 51 Franklin
 * Street, Fifth Floor, Boston, MA 02110-1301 USA.
 ******************************************************************************/

namespace Phactor;

/**
 * GMP math class used by the main math class.
 *
 * @author Rich Morgan <rich.l.morgan@gmail.com>
 */
final class GMP
{
    /**
     * Adds two numbers.
     *
     * @param string $a  The first number.
     * @param string $b  The second number.
     * @return string
     */
    public function add($a, $b)
    {
        return gmp_strval(gmp_add($a, $b));
    }
    /**
     * Multiplies two numbers.
     *
     * @param string $a  The first number.
     * @param string $b  The second number.
     * @return string
     */
    public function mul($a, $b)
    {
        return gmp_strval(gmp_mul($a, $b));
    }
    /**
     * Divides two numbers.
     *
     * @param string $a  The first number.
     * @param string $b  The second number.
     * @return string
     */
    public function div($a, $b)
    {
        return gmp_strval(gmp_div($a, $b));
    }
    /**
     * Subtracts two numbers.
     *
     * @param string $a  The first number.
     * @param string $b  The second number.
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
     * @param string $a  The first number.
     * @param string $b  The second number.
     * @return string
     */
    public function mod($a, $b, $correct=false)
    {
        if ($correct === true) {
            if (gmp_cmp($a, '0') < 0) {
                return gmp_strval(gmp_sub(gmp_mod($a, $b), $a));
            }
        }

        return gmp_strval(gmp_mod($a, $b));
    }

    /**
     * Compares two numbers.
     *
     * @param string $a  The first number.
     * @param string $b  The second number.
     * @return integer
     */
    public function comp($a, $b)
    {
        return gmp_cmp($a, $b);
    }

    /**
     * GMP's inverse modulo function, where ax = 1(mod p).
     *
     * @param  string $num The number to inverse modulo.
     * @param  string $mod The modulus.
     * @return string
     */
    public function inv($a, $b)
    {
        return gmp_strval(gmp_invert($a, $b));
    }

    /**
     * Returns the string value of a GMP resource.
     *
     * @param mixed $a
     * @param mixed $b
     * @return string
     */
    public function normalize($a)
    {
        return gmp_strval($a);
    }

    /**
     * Raise base into power exp.
     *
     * @param unknown $a
     * @param unknown $b
     * @return string
     */
    public function power($a, $b)
    {
        return gmp_strval(gmp_pow($a, $b));
    }
}
