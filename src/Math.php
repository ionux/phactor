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
 * Generic math trait used by all other Phactor classes and proxy for math objects.
 *
 * @author Rich Morgan <rich@richmorgan.me>
 */
trait Math
{
    use Number;

    /**
     * @var object
     */
    private $math = null;

    /**
     * Multiplies two arbitrary precision numbers.
     *
     * @param  string $a  The first number to multiply.
     * @param  string $b  The second number to multiply.
     * @return string     The result of the operation.
     */
    public function Multiply($a, $b)
    {
        $this->preOpMethodParamsCheck(array($a, $b));

        return $this->math->mul($a, $b);
    }

    /**
     * Adds two arbitrary precision numbers.
     *
     * @param  string $a  The first number to add.
     * @param  string $b  The second number to add.
     * @return string     The result of the operation.
     */
    public function Add($a, $b)
    {
        $this->preOpMethodParamsCheck(array($a, $b));

        return $this->math->add($a, $b);
    }

    /**
     * Subtracts two arbitrary precision numbers.
     *
     * @param  string $a  The first number to Subtract.
     * @param  string $b  The second number to Subtract.
     * @return string     The result of the operation.
     */
    public function Subtract($a, $b)
    {
        $this->preOpMethodParamsCheck(array($a, $b));

        return $this->math->sub($a, $b);
    }

    /**
     * Divides two arbitrary precision numbers.
     *
     * @param  string $a  The first number to Divide.
     * @param  string $b  The second number to Divide.
     * @return string     The result of the operation.
     */
    public function Divide($a, $b)
    {
        $this->preOpMethodParamsCheck(array($a, $b));

        return $this->math->div($a, $b);
    }

    /**
     * Performs the modulo 'b' of an arbitrary precision
     * number 'a'. There's a slight quirk in GMP's
     * implementation so this returns a mathematically
     * correct answer if you specify the $correct parameter
     * and you're using GMP, of course.
     *
     * @param  string  $a        The first number.
     * @param  string  $b        The second number.
     * @param  boolean $correct  Flag to calculate mathematically correct modulo.
     * @return string            The result of the operation.
     */
    public function Modulo($a, $b, $correct = false)
    {
        $this->preOpMethodParamsCheck(array($a, $b));

        return $this->math->mod($a, $b, $correct);
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
    public function PowMod($a, $b, $c)
    {
        $this->preOpMethodParamsCheck(array($a, $b));

        return $this->math->powmod($a, $b, $c);
    }

    /**
     * Performs the inverse modulo of two arbitrary precision numbers.
     *
     * @param  string $a  The first number to Divide.
     * @param  string $b  The second number to Divide.
     * @return string     The result of the operation.
     */
    public function Invert($a, $b)
    {
        $this->preOpMethodParamsCheck(array($a, $b));

        return $this->math->inv($a, $b);
    }

    /**
     * Compares two arbitrary precision numbers.
     *
     * @param  string $a  The first number to compare.
     * @param  string $b  The second number to compare.
     * @return string     The result of the comparison.
     */
    public function Compare($a, $b)
    {
        $this->preOpMethodParamsCheck(array($a, $b));

        return $this->math->comp($a, $b);
    }

    /**
     * Raises an arbitrary precision number to an integer power.
     *
     * @param  string $a  The number to raise to the power.
     * @param  string $b  The integer power
     * @return string     The result of the operation.
     */
    public function Power($a, $b)
    {
        $this->preOpMethodParamsCheck(array($a, $b));

        return $this->math->power($a, $b);
    }

    /**
     * Calculates & returns the integer portion of the square root.
     *
     * @param  string $a  The first number.
     * @return string     The result of the operation.
     */
    public function SqRoot($a)
    {
        $this->preOpMethodParamsCheck(array($a));

        return $this->math->sqrt($a);
    }

    /**
     * Encodes a decimal value into hexadecimal.
     *
     * @param  string   $dec    The decimal value to convert.
     * @param  boolean  $prefix Whether or not to append the '0x'.
     * @return string   $hex    The result of the conversion.
     */
    public function encodeHex($dec, $prefix = true)
    {
        $this->preOpMethodParamsCheck(array($dec));

        $dec = ($this->Test($dec) != 'hex') ? strrev($this->encodeValue($this->absValue($dec), '16')) : $dec;

        return ($prefix === true) ? $this->addHexPrefix($dec) : $dec;
    }

    /**
     * Decodes a hexadecimal value into decimal.
     *
     * @param  string  $hex
     * @return string  $dec
     */
    public function decodeHex($hex)
    {
        $this->preOpMethodParamsCheck(array($hex));

        $dec = false;

        if ($this->Test($hex) == 'hex') {
            $hex = $this->stripHexPrefix($this->prepAndClean($hex));

            $hex_len = strlen($hex);

            if ($hex_len < 5) {
                $dec = hexdec($hex);
            } else {
                for ($i = 0; $i < $hex_len; $i++) {
                    $current = stripos($this->hex_chars, $hex[$i]);
                    $dec     = $this->math->add($this->math->mul($dec, '16'), $current);
                }
            }
        } 

        return ($dec === false) ? $hex : $dec;
    }

    /**
     * This method returns a binary string representation of
     * the decimal number.  Used for the doubleAndAdd() method.
     *
     * @param  string     $num The number to convert.
     * @return string     $bin The converted number.
     * @throws \Exception
     */
    public function D2B($num)
    {
        $this->preOpMethodParamsCheck(array($num));

        /* Make sure that we're dealing with a decimal number. */
        $num = $this->decodeHex($num);

        try {

            $bin = '';

            while ($this->math->comp($num, '0') > 0) {
                switch ($this->math->mod($num, '2')) {
                    case '1':
                        $bin .= '1';
                        break;
                    default:
                        $bin .= '0';
                        break;
                }

                $num = $this->math->div($num, '2');
            }

            return $bin;

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Converts hex value into octet (byte) string.
     *
     * @param  string     $hex
     * @return string
     * @throws \Exception
     */
    public function binConv($hex)
    {
        $this->preOpMethodParamsCheck(array($hex));

        switch ($this->Test($hex)) {
            case 'dec':
                $hex = $this->encodeHex($hex);
                break;
            case 'hex':
                $hex = $this->addHexPrefix($this->prepAndClean($hex));
                break;
            default:
                throw new \Exception('Unknown data type passed to the binConv() function.  Value received was "' . var_export($hex, true) . '".');
        }

        return strrev($this->encodeValue($hex, '256'));
    }

    /**
     * Converts a hex number to BASE-58 used for Bitcoin-related tasks.
     *
     * @param  string     $hex
     * @return string     $return
     * @throws \Exception
     */
    public function encodeBase58($hex)
    {
        $this->preOpMethodParamsCheck(array($hex));

        try {

            if (strlen($hex) % 2 != 0 || $this->Test($hex) != 'hex') {
                throw new \Exception('Uneven number of hex characters or invalid parameter passed to encodeBase58 function.  Value received was "' . var_export($hex, true) . '".');
            } else {
                $orighex = $hex;
                $hex     = $this->addHexPrefix($this->prepAndClean($hex));
                $return  = strrev($this->encodeValue($hex, '58'));

                for ($i = 0; $i < strlen($orighex) && substr($orighex, $i, 2) == '00'; $i += 2) {
                    $return = '1' . $return;
                }
            }

            return $return;

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Converts a BASE-58 number used for Bitcoin-related tasks to hex.
     *
     * @param  string     $base58
     * @return string     $return
     * @throws \Exception
     */
    public function decodeBase58($base58)
    {
        $this->preOpMethodParamsCheck(array($base58));

        try {
            $origbase58 = $base58;
            $return     = '0';
            $b58_len    = strlen($base58);

            for ($i = 0; $i < $b58_len; $i++) {
                $current = strpos($this->b58_chars, $base58[$i]);
                $return  = $this->math->mul($return, '58');
                $return  = $this->math->add($return, $current);
            }

            $return = $this->encodeHex($return);

            for ($i = 0; $i < strlen($origbase58) && $origbase58[$i] == '1'; $i++) {
                $return = '00' . $return;
            }

            return (strlen($return) % 2 != 0) ? '0' . $return : $return;

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Internal function to make sure we can find an acceptable math extension to use here.
     *
     * @throws \Exception
     */
    private function MathCheck()
    {
        if ($this->math == null || is_object($this->math) === false) {
            if (function_exists('gmp_add')) {
                $this->math = new GMP();
            } else if (function_exists('bcadd')) {
                $this->math = new BC();
            } else {
                throw new \Exception('Both GMP and BC Math extensions are missing on this system!  Please install one to use the Phactor math library.');
            }
        }

        $this->bytes = (empty($this->bytes)) ? $this->GenBytes() : $this->bytes;
    }

    /**
     * Handles the pre-work validation checking for method parameters.
     *
     * @param  array   $params  The array of parameters to check.
     * @return boolean          Will only be true, otherwise throws \Exception
     * @throws \Exception
     */
    private function preOpMethodParamsCheck($params)
    {
        $this->MathCheck();

        foreach ($params as $key => $value) {
            if ($this->numberCheck($value) === false) {
                $caller = debug_backtrace();
                throw new \Exception('Empty or invalid parameters passed to ' . $caller[count($caller) - 1]['function'] . ' function. Argument list received: ' . var_export($caller[count($caller) - 1]['args'], true));
            }
        }
    }

    /**
     * The generic value encoding method.
     *
     * @param  string $val  A number to convert.
     * @param  string $base The base to convert it into.
     * @return string       The same number but in a different base.
     * @throws \Exception
     */
    private function encodeValue($val, $base)
    {
        $this->preOpMethodParamsCheck(array($val, $base));

        $digits = $this->baseCheck($base);

        try {

            $new = '';

            while ($this->math->comp($val, '0') > 0) {
                $qq  = $this->math->div($val, $base);
                $rem = $this->math->mod($val, $base);
                $val = $qq;
                $new = $new . $digits[$rem];
            }

            return $new;

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Congruency check for two values. Note: the actual
     * computation is performed within the calling method.
     * Used in the signature verification function.
     *
     * @param  string $r  The first coordinate to check.
     * @param  string $x  The second coordinate to check.
     * @return boolean    Returns true if values are congruent.
     */
    private function congruencyCheck($r, $x)
    {
        return (bool)($this->math->comp($r, $x) == 0);
    }

    /**
     * Determines if the MSB is set and returns a NULL byte if so.
     *
     * @param  string $value The binary data to check.
     * @return string        A NULL byte character.
     */
    private function msbCheck($value)
    {
        if ($this->math->comp(hexdec(bin2hex($value)), '128') >= 0) {
            return chr(0x00);
        }
    }

    /**
     * Checks if two parameters are less than or equal to zero.
     *
     * @param  string $a  The first parameter to check.
     * @param  string $b  The second parameter to check.
     * @return boolean    Result of the check.
     */
    private function zeroCompare($a, $b)
    {
        return ($this->math->comp($a, '0') <= 0 || $this->math->comp($b, '0') <= 0);
    }
}
