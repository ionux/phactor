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
 * Base number trait for general math methods.
 *
 * @author Rich Morgan <rich@richmorgan.me>
 */
trait Number
{
    use BaseObject;

    /**
     * @var string
     */
    public $Inf = 'infinity';

    /**
     * @var string
     */
    private $b58_chars = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';

    /**
     * @var string
     */
    private $dec_chars = '0123456789';

    /**
     * @var string
     */
    private $hex_chars = '0123456789abcdef';

    /**
     * @var string
     */
    private $bin_chars = '01';

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
        switch ($base) {
            case '256':
                return $this->genBytes();
            case '16':
                return $this->hex_chars;
            case '58':
                return $this->b58_chars;
            case '2':
                return $this->bin_chars;
            case '10':
                return $this->dec_chars;
            default:
                throw new \Exception('Unknown base parameter passed to Number::BaseCheck() function.  Value received was "' . var_export($base, true) . '".');
        }
    }

    /**
     * Determines the type of number passed to function.
     *
     * @param  mixed      $value The value to check.
     * @return string     $value The data type of the value.
     * @throws \Exception
     */
    public function Test($value)
    {
        /* The order of checks in this array is specific. */
        $checks = array('nullTest', 'zeroTest', 'objTest', 'arrTest', 'resTest', 'intTest', 'floTest');

        foreach ($checks as $key => $checkType) {
            if ($this->$checkType($value) === true) {
                return substr($checkType, 0, 3);
            }
        }

        /* This is what the data should be really. */
        if ($this->strTest($value) === true) {

            /* Remove any negative signs & strip any prefix. */
            $value = $this->stripHexPrefix($this->absValue($value));

            if ($this->bdTest($value) === true) {
                return 'bin';
            }

            if ($this->ddTest($value) === true) {
                return 'dec';
            }

            /* Now we're probably dealing with a proper hex number. */
            if ($this->hdTest($value) === true) {
                return 'hex';
            }

            if ($this->b58Test($value) === true) {
                return 'b58';
            }
        }

        /* Otherwise, this is either binary or garbage... */
        return 'unk';
    }

    /**
     * Check to ensure we're working with a number or numeric string.
     *
     * @param  mixed   $value The value to check.
     * @return boolean        Whether or not this is a number.
     */
    public function numberCheck($value)
    {
        /* We are only concerned with these types... */
        switch ($this->Test($value)) {
            case 'b58':
            case 'hex':
            case 'dec':
            case 'bin':
            case 'int':
            case 'zer':
                return true;
            default:
                return false;
        }
    }

    /**
     * Returns the absolute value |$val| of the number.
     *
     * @param  string $value The value to be abs'd.
     * @return string        The absolute value of the number.
     */
    public function absValue($value)
    {
        /* Remove any negative signs. */
        return ($value[0] == '-') ? substr($this->prepAndClean($value), 1) : $this->prepAndClean($value);
    }

    /**
     * Generates a secure random number using the OpenSSL
     * openssl_random_pseudo_bytes extension, see:
     * http://php.net/manual/en/function.openssl-random-pseudo-bytes.php
     *
     * @return string     $secure_random_number Random data in hex form.
     * @throws \Exception
     */
    public function SecureRandomNumber()
    {
        $this->openSSLCheck();

        $cstrong = false;
        $secure_random_number = '';

        while (strlen($secure_random_number) < 78) {
            $secure_random_number = $secure_random_number . hexdec(bin2hex(openssl_random_pseudo_bytes(4, $cstrong)));
        }

        if ($secure_random_number === false || $cstrong === false) {
            throw new \Exception('The Phactor math library could not generate a cryptographically-strong random number. Your OpenSSL extension might be old or broken. Please contact your web hosting provider with this error message.');
        }

        return $secure_random_number;
    }

    /**
     * Checks if a hex value has the '0x' prefix
     * and removes it, if present. Otherwise it
     * just returns the original value unchanged.
     *
     * @param  string $hex The value to check.
     * @return string      The value minus '0x'.
     */
    private function stripHexPrefix($hex)
    {
        return (substr($hex, 0, 2) == '0x') ? substr($hex, 2) : $hex;
    }

    /**
     * Checks if a hex value is missing the '0x'
     * prefix and adds it, if needed. Otherwise it
     * just returns the original value unchanged.
     *
     * @param  string $hex The value to check.
     * @return string      The value plus '0x'.
     */
    private function addHexPrefix($hex)
    {
        return (substr($hex, 0, 2) != '0x') ? '0x' . $hex : $hex;
    }

    /**
     * Checks if a number is zero.
     *
     * @param  mixed   $value  The value to be checked.
     * @return boolean         Either true or false.
     */
    private function zeroTest($value)
    {
        /* Special case. */
        return (($this->nullTest($value) === false) && ($this->arrTest($value) === false) && ($value == '0' || $value == '0x0' || $value == '0x00'));
    }

    /**
     * Checks if a number is a float
     *
     * @param  mixed   $value The value to be checked.
     * @return boolean        Either true or false.
     */
    private function floTest($value)
    {
        return (($this->nullTest($value) === false) && is_float($value));
    }

    /**
     * Checks if a number is an integer
     *
     * @param  mixed   $value The value to be checked.
     * @return boolean        Either true or false.
     */
    private function intTest($value)
    {
        return (($this->nullTest($value) === false) && is_int($value));
    }

    /**
     * Checks if a number contains only hex digits.
     *
     * @param  mixed   $value The value to be checked.
     * @return boolean        Either true or false.
     */
    private function hdTest($value)
    {
        /* Both hex and regular decimal numbers will pass this check. */
        return (preg_match('/^[a-f0-9]*$/', $value) === 1);
    }

    /**
     * Checks if a number contains only decimal digits.
     *
     * @param  mixed   $value The value to be checked.
     * @return boolean        Either true or false.
     */
    private function ddTest($value)
    {
        /* But, if this test is true, it's definitely a pure decimal number. */
        return (preg_match('/^[0-9]*$/', $value) === 1);
    }

    /**
     * Checks if a number contains only binary digits.
     *
     * @param  mixed   $value The value to be checked.
     * @return boolean        Either true or false.
     */
    private function bdTest($value)
    {
        /* Finally, if this test is true, it's definitely a pure binary number string. */
        return (preg_match('/^[0-1]*$/', $value) === 1);
    }

    /**
     * Checks if a number contains only base58 digits.
     *
     * @param  mixed   $value The value to be checked.
     * @return boolean        Either true or false.
     */
    private function b58Test($value)
    {
        return (preg_match('/^[a-km-zA-HJ-NP-Z1-9]*$/', $value) === 1);
    }

    /**
     * Checks if a specific hex value is < 62 characters long.
     *
     * @param  string     $hex  The value to check.
     * @throws \Exception
     */
    private function hexLenCheck($hex)
    {
        if (strlen($hex) < 62) {
            throw new \Exception('The coordinate value checked was not in hex format or was invalid.  Value checked was "' . var_export($hex, true) . '".');
        }
    }
}
