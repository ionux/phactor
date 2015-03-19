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
 * Generic math trait used by all other Phactor classes.
 *
 * @author Rich Morgan <rich.l.morgan@gmail.com>
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
     * @param string $a  The first number to multiply.
     * @param string $b  The second number to multiply.
     * @return string    The result of the operation.
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

        return ($this->math->mul($a, $b));
    }

    /**
     * Adds two arbitrary precision numbers.
     *
     * @param string $a  The first number to add.
     * @param string $b  The second number to add.
     * @return string    The result of the operation.
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

        return ($this->math->add($a, $b));
    }

    /**
     * Subtracts two arbitrary precision numbers.
     *
     * @param string $a  The first number to Subtract.
     * @param string $b  The second number to Subtract.
     * @return string    The result of the operation.
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

        return ($this->math->sub($a, $b));
    }

    /**
     * Divides two arbitrary precision numbers.
     *
     * @param string $a  The first number to Divide.
     * @param string $b  The second number to Divide.
     * @return string    The result of the operation.
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

        return ($this->math->div($a, $b));
    }

    /**
     * Performs the modulo 'b' of an arbitrary precision number 'a'.
     *
     * @param string $a  The first number.
     * @param string $b  The second number.
     * @return string    The result of the operation.
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

        return ($this->math->mod($a, $b));
    }

    /**
     * Performs the inverse modulo of two arbitrary precision numbers.
     *
     * @param string $a  The first number to Divide.
     * @param string $b  The second number to Divide.
     * @return string    The result of the operation.
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

        return ($this->math->inv($a, $b));
    }

    /**
     * Compares two arbitrary precision numbers.
     *
     * @param string $a  The first number to compare.
     * @param string $b  The second number to compare.
     * @return string    The result of the comparison.
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

        return ($this->math->comp($a, $b));
    }

    /**
     * Raises an arbitrary precision number to an integer power.
     *
     * @param string $a  The number to raise to the power.
     * @param string $b  The integer power
     * @return string    The result of the operation.
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
     * @param  string $dec
     * @return string
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
     * @param  string $hex
     * @return string
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
     * @param string $base  The base requested.
     * @return array|string The base character info.
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
     * @param  string $num The number to convert.
     * @return string $bin The converted number.
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
     * Converts hex value into octet (byte) string
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
     * @param  string $value The value to check.
     * @return string $value If the value is present.
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
     * @return string
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
     * @param unknown $hex
     * @return string
     * @throws \Exception
     */
    private function encodeBase58($hex)
    {
        if ($this->math == null) {
            $this->MathCheck();
        }

        try {
            if (empty($hex) || strlen($hex) % 2 != 0) {
                $return = 'Error - uneven number of hex characters passed to encodeBase58().';
            } else {
                $chars = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
                $orighex = $hex;
                $return = '';

                if (substr(strtolower($hex), 0, 2) != '0x') {
                    $hex = '0x' . strtolower($hex);
                }

                while (gmp_cmp($hex, '0') > 0) {
                    $dv = gmp_div_q($hex, '58');
                    $rem = gmp_strval(gmp_div_r($hex, '58'));
                    $hex = $dv;
                    $return = $return . $chars[$rem];
                }

                $return=strrev($return);

                for ($i = 0; $i < strlen($orighex) && substr($orighex, $i, 2) == '00'; $i += 2) {
                    $return = '1' . $return;
                }
            }

            return $return;
        } catch (\Exception $e) {
            return 'Error in ECSINgen::encodeBase58(): '.$e->getMessage();
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

        if (empty($this->bytes)) {
            $this->bytes = $this->GenBytes();
        }

        $this->Gx = '0x' . substr(strtolower($this->G), 2, 64);
        $this->Gy = '0x' . substr(strtolower($this->G), 66, 64);
    }
}
