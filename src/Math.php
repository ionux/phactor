<?php
/******************************************************************************
 * This file is part of the Phactor PHP project. You can always find the latest
 * version of this class and project at: https://github.com/ionux/phactor
 *
 * Copyright (c) 2015 Rich Morgan, rich@bitpay.com
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
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 * FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 * IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 * CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 ******************************************************************************/

namespace Phactor;

/**
 * Generic math trait used by all other Phactor classes.
 *
 * @author Rich Morgan <rich@bitpay.com>
 */
trait Math
{
    /*
     * Elliptic curve parameters for secp256k1
     * http://www.secg.org/collateral/sec2_final.pdf
     */
    public $Inf = 'infinity';
    public $G   = '0479BE667EF9DCBBAC55A06295CE870B07029BFCDB2DCE28D959F2815B16F81798483ADA7726A3C4655DA4FBFC0E1108A8FD17B448A68554199C47D08FFB10D4B8';
    public $p   = '0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEFFFFFC2F';
    public $a   = '0x00';
    public $b   = '0x07';
    public $n   = '0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEBAAEDCE6AF48A03BBFD25E8CD0364141';
    public $h   = '0x01';

    public $Gx  = '0x79BE667EF9DCBBAC55A06295CE870B07029BFCDB2DCE28D959F2815B16F81798';
    public $Gy  = '0x483ADA7726A3C4655DA4FBFC0E1108A8FD17B448A68554199C47D08FFB10D4B8';

    private $bytes     = '';
    private $math      = null;
    private $b58_chars = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
    private $dec_chars = '0123456789';
    private $hex_chars = '0123456789abcdef';
    private $bin_chars = '01';

    private $param_checking = true;

    /**
     * Multiplies two arbitrary precision numbers.
     *
     * @param  string $a  The first number to multiply.
     * @param  string $b  The second number to multiply.
     * @return string     The result of the operation.
     * @throws \Exception
     */
    public function Multiply($a, $b)
    {
        if ($this->param_checking == true) {
            if (false === isset($a) || false === isset($b) || false === is_string($a) || false === is_string($b)) {
                throw new \Exception('Empty or invalid parameters passed to Multiply() function.');
            }
        }

        if ($this->math == null) {
            $this->MathCheck();
        }

        return $this->math->mul($a, $b);
    }

    /**
     * Adds two arbitrary precision numbers.
     *
     * @param  string $a  The first number to add.
     * @param  string $b  The second number to add.
     * @return string     The result of the operation.
     * @throws \Exception
     */
    public function Add($a, $b)
    {
        if ($this->param_checking == true) {
            if (false === isset($a) || false === isset($b) || false === is_string($a) || false === is_string($b)) {
                throw new \Exception('Empty or invalid parameters passed to Add() function.');
            }
        }

        if ($this->math == null) {
            $this->MathCheck();
        }

        return $this->math->add($a, $b);
    }

    /**
     * Subtracts two arbitrary precision numbers.
     *
     * @param  string $a  The first number to Subtract.
     * @param  string $b  The second number to Subtract.
     * @return string     The result of the operation.
     * @throws \Exception
     */
    public function Subtract($a, $b)
    {
        if ($this->param_checking == true) {
            if (false === isset($a) || false === isset($b) || false === is_string($a) || false === is_string($b)) {
                throw new \Exception('Empty or invalid parameters passed to Subtract() function.');
            }
        }

        if ($this->math == null) {
            $this->MathCheck();
        }

        return $this->math->sub($a, $b);
    }

    /**
     * Divides two arbitrary precision numbers.
     *
     * @param  string $a  The first number to Divide.
     * @param  string $b  The second number to Divide.
     * @return string     The result of the operation.
     * @throws \Exception
     */
    public function Divide($a, $b)
    {
        if ($this->param_checking == true) {
            if (false === isset($a) || false === isset($b) || false === is_string($a) || false === is_string($b)) {
                throw new \Exception('Empty or invalid parameters passed to Divide() function.');
            }
        }

        if ($this->math == null) {
            $this->MathCheck();
        }

        return $this->math->div($a, $b);
    }

    /**
     * Performs the modulo 'b' of an arbitrary precision number 'a'.
     *
     * @param  string $a  The first number.
     * @param  string $b  The second number.
     * @return string     The result of the operation.
     * @throws \Exception
     */
    public function Modulo($a, $b)
    {
        if ($this->param_checking == true) {
            if (false === isset($a) || false === isset($b) || false === is_string($a) || false === is_string($b)) {
                throw new \Exception('Empty or invalid parameters passed to Modulo() function.');
            }
        }

        if ($this->math == null) {
            $this->MathCheck();
        }

        return $this->math->mod($a, $b);
    }

    /**
     * Performs the inverse modulo of two arbitrary precision numbers.
     *
     * @param  string $a  The first number to Divide.
     * @param  string $b  The second number to Divide.
     * @return string     The result of the operation.
     * @throws \Exception
     */
    public function Invert($a, $b)
    {
        if ($this->param_checking == true) {
            if (false === isset($a) || false === isset($b) || false === is_string($a) || false === is_string($b)) {
                throw new \Exception('Empty or invalid parameters passed to Invert() function.');
            }
        }

        if ($this->math == null) {
            $this->MathCheck();
        }

        return $this->math->inv($a, $b);
    }

    /**
     * Compares two arbitrary precision numbers.
     *
     * @param  string $a  The first number to compare.
     * @param  string $b  The second number to compare.
     * @return string     The result of the comparison.
     * @throws \Exception
     */
    public function Compare($a, $b)
    {
        if ($this->param_checking == true) {
            if (false === isset($a) || false === isset($b) || false === is_string($a) || false === is_string($b)) {
                throw new \Exception('Empty or invalid parameters passed to Compare() function.');
            }
        }

        if ($this->math == null) {
            $this->MathCheck();
        }

        return $this->math->comp($a, $b);
    }

    /**
     * Raises an arbitrary precision number to an integer power.
     *
     * @param  string $a  The number to raise to the power.
     * @param  string $b  The integer power
     * @return string     The result of the operation.
     * @throws \Exception
     */
    public function Power($a, $b)
    {
        if ($this->param_checking == true) {
            if (false === isset($a) || false === isset($b) || false === is_string($a) || false === is_string($b)) {
                throw new \Exception('Empty or invalid parameters passed to Power() function.');
            }
        }

        if ($this->math == null) {
            $this->MathCheck();
        }

        return $this->math->power($a, $b);
    }

    /**
     * Encodes a decimal value into hexadecimal.
     *
     * @param  string     $dec
     * @return string     $hex
     * @throws \Exception
     */
    public function encodeHex($dec)
    {
        if ($this->param_checking == true) {
            if (false === isset($dec) || (false === is_string($dec) && false === ctype_digit($dec))) {
                throw new \Exception('Empty or invalid decimal parameter passed to encodeHex function.');
            }
        }

        if ($this->math == null) {
            $this->MathCheck();
        }

        if (substr($dec, 0, 1) == '-') {
            $dec = substr($dec, 1);
        }

        if (substr($dec, 0, 2) == '0x') {
            return $dec;
        }

        $hex = '';

        $digits = $this->hex_chars;

        while ($this->math->comp($dec, '0') > 0) {
            $qq  = $this->math->div($dec, '16');
            $rem = $this->math->mod($dec, '16');
            $dec = $qq;
            $hex = $hex . $digits[$rem];
        }

        return '0x' . strrev($hex);
    }

    /**
     * Decodes a hexadecimal value into decimal.
     *
     * @param  string     $hex
     * @return string     $dec
     * @throws \Exception
     */
    public function decodeHex($hex)
    {
        if ($this->param_checking == true) {
            if (false === isset($hex) || false === is_string($hex) || (false === ctype_xdigit($hex) && '0x' != substr($hex, 0, 2))) {
                throw new \Exception('Argument must be a string of hex digits.');
            }
        }

        if ($this->math == null) {
            $this->MathCheck();
        }

        $hex = strtolower(trim($hex));

        if (substr($hex, 0, 2) == '0x') {
            $hex = substr($hex, 2);
        }

        $hex_len = strlen($hex);

        for ($dec = '0', $i = 0; $i < $hex_len; $i++) {
            $current = strpos($this->hex_chars, $hex[$i]);
            $dec     = $this->math->add($this->math->mul($dec, '16'), $current);
        }

        return $dec;
    }

    /**
     * Returns the appropriate base digit string/array for the
     * requested base parameter.
     *
     * @param  string       $base  The base requested.
     * @return array|string        The base character info.
     * @throws \Exception
     */
    public function BaseCheck($base)
    {
        if ($this->param_checking == true) {
            if (false === isset($base) || true === empty($base)) {
                throw new \Exception('Empty base parameter passed to BaseCheck() function.');
            }
        }

        switch ($base) {
            case '256':
                return $this->GenBytes();
            case '16':
                return $this->hex_chars;
            case '58':
                return $this->b58_chars;
            case '2':
                return $this->bin_chars;
            case '10':
                return $this->dec_chars;
            default:
                throw new \Exception('Unknown base parameter passed to BaseCheck() function.');
        }
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
        if ($this->param_checking == true) {
            if (false === isset($num) || true === empty($num) || false === is_string($num)) {
                throw new \Exception('Missing or invalid number parameter passed to the D2B() function.');
            }
        }

        if ($this->math == null) {
            $this->MathCheck();
        }

        if (substr($num, 0, 2) == '0x') {
            $num = $this->decodeHex($num);
        }

        $tmp = $num;
        $bin = '';

        try {
            while ($this->math->comp($tmp, '0') > 0) {
                if ($this->math->mod($tmp, '2') == '1') {
                    $bin .= '1';
                } else {
                    $bin .= '0';
                }

                $tmp = $this->math->div($tmp, '2');
            }
        } catch (\Exception $e) {
            throw $e;
        }

        return $bin;
    }

    /**
     * Converts hex value into octet (byte) string.
     *
     * @param  string
     * @return string
     * @throws \Exception
     */
    public function binConv($hex)
    {
        if ($this->param_checking == true) {
            if (false === isset($hex) || true === empty($hex) || false === is_string($hex)) {
                throw new \Exception('Missing or invalid number parameter passed to the binConv() function.');
            }
        }

        $rem    = '';
        $dv     = '';
        $byte   = '';

        $digits = array();

        if ($this->math == null) {
            $this->MathCheck();
        }

        $digits = $this->BaseCheck('256');

        if (substr(strtolower($hex), 0, 2) != '0x') {
            $hex = '0x' . strtolower($hex);
        }

        while ($this->math->comp($hex, '0') > 0) {
            $dv   = $this->math->div($hex, '256');
            $rem  = $this->math->mod($hex, '256');
            $hex  = $dv;
            $byte = $byte . $digits[$rem];
        }

        return strrev($byte);
    }

    /**
     * Determines if the hex value needs '0x'.
     *
     * @param  string     $value The value to check.
     * @return string     $value If the value is present.
     * @throws \Exception
     */
    public function Test($value)
    {
        if (false === isset($value) || true === empty($value) || false === is_string($value)) {
            throw new \Exception('Empty or non-string value parameter passed to Test() function.');
        }

        $value = strtolower(trim($value));

        if (substr($value, 0, 2) != '0x' && ctype_xdigit($value) === false) {
            throw new \Exception('Invalid value parameter passed to Test() function.');
        }

        return $value;
    }

    /**
     * Generates a secure random number using the OpenSSL extension.
     *
     * @param  int        Number of bytes to return.
     * @return string     Random data in hex form.
     * @throws \Exception
     */
    public function SecureRandomNumber($length = 32)
    {
        $cstrong = false;

        if (!function_exists('openssl_random_pseudo_bytes')) {
            throw new \Exception('This class requires the OpenSSL extension for PHP. Please install this extension.');
        }

        $secure_random_number = openssl_random_pseudo_bytes($length, $cstrong);

        if (false === $secure_random_number || false === $cstrong) {
            throw new \Exception('Could not generate a cryptographically-strong random number. Your OpenSSL extension might be old or broken.');
        }

        return '0x' . strtolower(bin2hex($secure_random_number));
    }

    /**
     * Converts a hex number to BASE-58 used for Bitcoin-related tasks.
     *
     * @param  string     $hex
     * @return string     $return
     * @throws \Exception
     */
    private function encodeBase58($hex = '')
    {
        if ($this->math == null) {
            $this->MathCheck();
        }

        try {
            if (true === empty($hex) || strlen($hex) % 2 != 0) {
                $return = 'Error - uneven number of hex characters passed to encodeBase58().';
            } else {
                $chars   = $this->b58_chars;
                $orighex = $hex;
                $return  = '';

                if (substr(strtolower($hex), 0, 2) != '0x') {
                    $hex = '0x' . strtolower($hex);
                }

                while ($this->math->comp($hex, '0') > 0) {
                    $dv     = $this->math->div($hex, '58');
                    $rem    = $this->math->mod($hex, '58');
                    $hex    = $dv;
                    $return = $return . $chars[$rem];
                }

                $return = strrev($return);

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
     * Generates an array of byte values.
     *
     * @return array $tempvals An array of bytes.
     */
    private function GenBytes()
    {
        $tempvals = array();

        for ($x = 0; $x < 256; $x++) {
            $tempvals[$x] = chr($x);
        }

        return $tempvals;
    }

    /**
     * Internal function to make sure we can find
     * an acceptable math extension to use here.
     *
     * @throws \Exception
     */
    private function MathCheck()
    {
        if ($this->math == null) {
            if (function_exists('gmp_add')) {
                $this->math = new GMP();
                return;
            } else if (function_exists('bcadd')) {
                $this->math = new BC();
                return;
            } else {
                throw new \Exception('Both GMP and BC Math extensions are missing on this system. Please install one to use this class.');
            }
        }

        if (true === empty($this->bytes)) {
            $this->bytes = $this->GenBytes();
        }

        $this->Gx = '0x' . substr(strtolower($this->G), 2, 64);
        $this->Gy = '0x' . substr(strtolower($this->G), 66, 64);
    }

    /**
     * Basic range check. Throws exception if
     * coordinate value is out of range.
     *
     * @param  string     $value The coordinate to check.
     * @return boolean           The result of the check.
     * @throws \Exception
     */
    public function RangeCheck($value)
    {
        if (false === isset($value) || true === empty($value)) {
            throw new \Exception('Empty value parameter passed to RangeCheck() function.');
        }

        try {
            /* Check to see if $value is in the range [1, n-1] */
            if ($this->math->comp($value, '1') <= 0 && $this->math->comp($value, $this->n) > 0) {
                throw new \Exception('The coordinate value is out of range. Should be 1 < r < n-1.');
            }
        } catch (\Exception $e) {
            throw $e;
        }

        return true;
    }

}
