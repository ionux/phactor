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
 * Base number trait for general math methods.
 *
 * @author Rich Morgan <rich@bitpay.com>
 */
trait Number
{
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
     * @var array
     */
    private $bytes = array();

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
                throw new \Exception('Unknown base parameter passed to BaseCheck() function.  Value received was "' . var_export($base, true) . '".');
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
        /* Let's get the non-numeric data types out of the way first... */
        if (false === isset($value) || true === is_null($value)) {
            return 'null';
        }

        /* Special case. */
        if ($value == '0') {
            return 'zer';
        }

        if (true === is_object($value)) {
            return 'obj';
        }

        if (true === is_array($value)) {
            return 'arr';
        }

        if (true === is_resource($value)) {
            return 'res';
        }

        if (true === is_int($value)) {
            return 'int';
        }

        if (true === is_float($value)) {
            return 'flo';
        }

        /* This is what the data should be really. */
        if (true === is_string($value)) {

            /* Remove any negative signs. */
            $value = $this->absValue($value);

            /* Determine if we have a hex prefix to begin with. */
            $value = $this->stripHexPrefix($value);

            /* Both hex and regular decimal numbers will pass this check. */
            $h_digits = (preg_match('/^[a-f0-9]*$/', $value) == 1) ? true : false;

            /* But, if this test is true, it's definitely a pure decimal number. */
            $d_digits = (preg_match('/^[0-9]*$/', $value) == 1) ? true : false;

            /* Finally, if this test is true, it's definitely a pure binary number string. */
            $b_digits = (preg_match('/^[0-1]*$/', $value) == 1) ? true : false;

            /* The first two cases are straightforward... */
            if ($b_digits === true) {
                return 'bin';
            }

            if ($d_digits === true) {
                return 'dec';
            }

            /* Now we're probably dealing with a hex number. */
            if ($h_digits === true) {
                return 'hex';
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
     * Trims() and strtolowers() the value.
     *
     * @param  string $value The value to clean.
     * @return string        The clean value.
     */
    private function prepAndClean($value)
    {
        return strtolower(trim($value));
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

}
