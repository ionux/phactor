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
 * Binary Calculator math class used by the main math class.
 *
 * @author Rich Morgan <rich.l.morgan@gmail.com>
 */
final class BC
{
    /**
     * Public constructor method.
     */
    public function __construct()
    {
        bcscale(0);
    }

    /**
     * Adds two numbers.
     * 
     * @param string $a  The first number.
     * @param string $b  The second number.
     * @return string
     */
    private function add($a, $b)
    {
        $a = $this->normalize($a);
        $b = $this->normalize($b);

        return (string)bcadd($a, $b);
    }

    /**
     * Multiplies two numbers.
     * 
     * @param string $a  The first number.
     * @param string $b  The second number.
     * @return string
     */
    private function mul($a, $b)
    {
        $a = $this->normalize($a);
        $b = $this->normalize($b);

        return (string)bcmul($a, $b);
    }

    /**
     * Divides two numbers.
     * 
     * @param string $a  The first number.
     * @param string $b  The second number.
     * @return string
     */
    private function div($a, $b)
    {
        $a = $this->normalize($a);
        $b = $this->normalize($b);

        return (string)bcdiv($a, $b);
    }

    /**
     * Subtracts two numbers.
     * 
     * @param string $a  The first number.
     * @param string $b  The second number.
     * @return string
     */
    private function sub($a, $b)
    {
        $a = $this->normalize($a);
        $b = $this->normalize($b);

        return (string)bcsub($a, $b);
    }

    /**
     * Calculates the modulo of two numbers.
     * 
     * @param string $a  The first number.
     * @param string $b  The second number.
     * @return string
     */
    private function mod($a, $b)
    {
        $a = $this->normalize($a);
        $b = $this->normalize($b);

        return (string)bcmod($a, $b);
    }
    
    /**
     * Compares two numbers.
     * 
     * @param string $a  The first number.
     * @param string $b  The second number.
     * @return string
     */
    private function comp($a, $b)
    {
        $a = $this->normalize($a);
        $b = $this->normalize($b);

        return (string)bccomp($a, $b);
    }

    /**
     * Raises $a to the power of $b.
     *
     * @param string $a
     * @param string $b
     * @return string
     */
    private function power($a, $b)
    {
        $a = $this->normalize($a);
        $b = $this->normalize($b);

        return (string)bcpow($a, $b);
    }

    /**
     * Binary Calculator implementation of GMP's inverse
     * modulo function, where ax = 1(mod p).
     *
     * @param  string $num The number to inverse modulo.
     * @param  string $mod The modulus.
     * @return string $a   The result.
     * @throws \Exception
     */
    private function inv($number, $modulus)
    {
        if (false === isset($number) || true === empty($number)) {
            throw new \Exception('Empty number parameter passed to bc_invert() function.');
        }

        if (false === isset($modulus) || true === empty($modulus)) {
            throw new \Exception('Empty modulus parameter passed to bc_invert() function.');
        }

        if (!$this->coprime($number, $modulus)) {
            return '0';
        }

        $a = '1';
        $b = '0';
        $z = '0';
        $c = '0';

        $modulus = $this->normalize($modulus);
        $number = $this->normalize($number);

        $mod = $modulus;
        $num = $number;

        try {
            do {
                $z = bcmod($num, $mod);
                $c = bcdiv($num, $mod);

                $num = $mod;
                $mod = $z;

                $z = bcsub($a, bcmul($b, $c));

                $a = $b;
                $b = $z;
            } while (bccomp($mod, '0') > 0);

            if (bccomp($a, '0') < 0) {
                $a = bcadd($a, $modulus);
            }
        } catch (\Exception $e) {
            throw $e;
        }

        return (string)$a;
    }

    /**
     * Function to determine if two numbers are
     * co-prime according to the Euclidean algo.
     *
     * @param  string $a  First param to check.
     * @param  string $b  Second param to check.
     * @return bool       Whether the params are cp.
     * @throws \Exception
     */
    private function coprime($a, $b)
    {
        if (false === isset($a) || true === empty($a)) {
            throw new \Exception('Empty first number parameter passed to coprime() function.');
        }

        if (false === isset($b) || true === empty($b)) {
            throw new \Exception('Empty second number parameter passed to coprime() function.');
        }

        $small = 0;
        $diff  = 0;

        $a = $this->normalize($a);
        $b = $this->normalize($b);

        try {
            while (bccomp($a, '0') > 0 && bccomp($b, '0') > 0) {
                if (bccomp($a, $b) == -1) {
                    $small = $a;
                    $diff  = bcmod($b, $a);
                }

                if (bccomp($a, $b) == 1) {
                    $small = $b;
                    $diff  = bcmod($a, $b);
                }

                if (bccomp($a, $b) == 0) {
                    $small = $a;
                    $diff  = bcmod($b, $a);
                }

                $a = $small;
                $b = $diff;
            }

            if (bccomp($a, '1') == 0) {
                return true;
            }
        } catch (\Exception $e) {
            throw $e;
        }

        return false;
    }

    /**
     * BC doesn't like the '0x' hex prefix that GMP prefers.
     *
     * @param unknown $a
     * @return string
     */
    private function normalize($a)
    {
        if (substr($a, 0, 2) == '0x') {
            $a = substr($a, 2);
        }

        return (string)$a;
    }
}
