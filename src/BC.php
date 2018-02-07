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
 * Binary Calculator math class used by the main math class.
 *
 * @author Rich Morgan <rich@richmorgan.me>
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
     * Adds two arbitrary precision numbers.
     *
     * @param  string $a  The first number.
     * @param  string $b  The second number.
     * @return string
     */
    public function add($a, $b)
    {
        return bcadd($this->bcNormalize($a), $this->bcNormalize($b));
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
        return bcmul($this->bcNormalize($a), $this->bcNormalize($b));
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
        return bcdiv($this->bcNormalize($a), $this->bcNormalize($b));
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
        return bcsub($this->bcNormalize($a), $this->bcNormalize($b));
    }

    /**
     * Calculates the modulo 'b' of 'a'.
     *
     * @param  string $a  The first number.
     * @param  string $b  The second number.
     * @return string
     */
    public function mod($a, $b)
    {
        return bcmod($this->bcNormalize($a), $this->bcNormalize($b));
    }

    /**
     * Raises an arbitrary precision number to another,
     * reduced by a specified modulus.
     *
     * @param  string  $a  The first number.
     * @param  string  $b  The exponent.
     * @param  string  $c  The modulus.
     * @return string      The result of the operation.
     */
    public function powmod($a, $b, $c)
    {
        return bcpowmod($this->bcNormalize($a), $this->bcNormalize($b), $this->bcNormalize($c));
    }

    /**
     * Compares two arbitrary precision numbers.
     *
     * @param  string  $a  The first number.
     * @param  string  $b  The second number.
     * @return integer
     */
    public function comp($a, $b)
    {
        return bccomp($this->bcNormalize($a), $this->bcNormalize($b));
    }

    /**
     * Raises $a to the power of $b.
     *
     * @param  string $a  The first number.
     * @param  string $b  The second number.
     * @return string
     */
    public function power($a, $b)
    {
        return bcpow($this->bcNormalize($a), $this->bcNormalize($b));
    }

    /**
     * Calculates & returns the integer portion of the square root.
     *
     * @param  string $a  The first number.
     * @return string
     */
    public function sqrt($a)
    {
        return bcsqrt($this->bcNormalize($a));
    }

    /**
     * Binary Calculator implementation of GMP's inverse
     * modulo function, where ax = 1(mod p).
     *
     * @param  string $number  The number to inverse modulo.
     * @param  string $modulus The modulus.
     * @return string $a       The result.
     * @throws \Exception
     */
    public function inv($number, $modulus)
    {
        if ($this->coprime($number, $modulus) === false) {
            return '0';
        }

        $a = '1';
        $b = '0';
        $z = '0';
        $c = '0';

        list($modulus, $number) = array($this->bcNormalize($modulus), $this->bcNormalize($number));
        list($mod, $num)        = array($modulus, $number);

        try {

            do {
                list($z, $c)     = $this->modDiv($num, $mod);
                list($mod, $num) = array($z, $mod);

                $z = $this->subMul($a, $b, $c);

                list($a, $b) = array($b, $z);
            } while (bccomp($mod, '0') > 0);

            return $this->addMod($a, $modulus);

        } catch (\Exception $e) {
            throw $e;
        }
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
    public function coprime($a, $b)
    {
        list($a, $b) = array($this->bcNormalize($a), $this->bcNormalize($b));

        try {

            while ($this->coCompare($a, $b)) {
                list($a, $b) = $this->coSwitch($a, $b);
            }

        } catch (\Exception $e) {
            throw $e;
        }

        return (bccomp($a, '1') == 0) ? true : false;
    }

    /**
     * BC doesn't like the '0x' hex prefix that GMP prefers.
     *
     * @param  string $a The value to bcNormalize.
     * @return string
     */
    public function bcNormalize($a)
    {
        if (is_string($a)) {
            $a = (substr($a, 0, 2) == '0x') ? substr($a, 2) : $a;
        }

        /** For now...
        switch($this->Test($a)) {
            case 'hex':
                $a = $this->convertToDec($a);
                break;
            //case 'bin':
                // convert to hex, dec
                //break;
            case 'unk':
            throw new \Exception('Unknown number type in BC::bcNormalize().  Cannot process!');
        }
        **/

        return $a;
    }

    /**
     * BC utility for directly converting
     * a hexadecimal number to decimal.
     *
     * @param  string $hex Number to convert to dec.
     * @return array       Dec form of the number.
     */
    public function convertHexToDec($hex)
    {
        if (strlen($hex) < 5) {
            return hexdec($hex);
        }

        list($remain, $last) = array(substr($hex, 0, -1), substr($hex, -1));

        return bcadd(bcmul('16', $this->convertHexToDec($remain)), hexdec($last));
    }

    /**
     * BC utility for directly converting
     * a decimal number to hexadecimal.
     *
     * @param  string $dec Number to convert to hex.
     * @return string      Hex form of the number.
     */
    public function convertDecToHex($dec)
    {
        if (strlen($dec) < 5) {
            return dechex($dec);
        }

        list($remain, $last) = array(bcdiv(bcsub($dec, $last), '16'), bcmod($dec, '16'));

        return ($remain == 0) ? dechex($last) : $this->convertDecToHex($remain) . dechex($last);
    }

    /**
     * Compares two numbers and returns an array
     * consisting of the smaller number & the
     * result of the larger number % smaller.
     *
     * @param  string $a  First param to check.
     * @param  string $b  Second param to check.
     * @return array      Array of smaller, larger % smaller.
     */
    public function coSwitch($a, $b)
    {
        switch (bccomp($a, $b)) {
            case 0:
                // Fall through.
            case -1:
                return array($a, bcmod($b, $a));
            case 1:
                return array($b, bcmod($a, $b));
        }
    }

    /**
     * Checks if both values are greater than zero.
     *
     * @param  string $a  First param to check.
     * @param  string $b  Second param to check.
     * @return bool       Whether the params are both > 0.
     */
    public function coCompare($a, $b)
    {
        return (bccomp($a, '0') > 0 && bccomp($b, '0') > 0);
    }

    /**
     * Calculates a number % modulo, number / modulo
     * and returns an array of the results.
     *
     * @param  string $num  Number parameter.
     * @param  string $mod  Modulo parameter.
     * @return array        Array of num % mod, num / mod.
     */
    public function modDiv($num, $mod)
    {
        return array(bcmod($num, $mod), bcdiv($num, $mod));
    }

    /**
     * Multiplies two numbers and subtracts the
     * results from a third number.
     *
     * @param  string $a  Number to subtract from.
     * @param  string $b  First number to multiply.
     * @param  string $c  Second number to multiply.
     * @return string     Result of a - (b * c).
     */
    public function subMul($a, $b, $c)
    {
        return bcsub($a, bcmul($b, $c));
    }

    /**
     * Checks if a number is negative and adds the modulus
     * to it, if true.
     *
     * @param  string $a    Number to check.
     * @param  string $mod  Modulus parameter.
     * @return string       Either a or (a + mod).
     */
    public function addMod($a, $mod)
    {
        return (bccomp($a, '0') < 0) ? bcadd($a, $mod) : $a;
    }
}
