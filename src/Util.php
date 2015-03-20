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
 * Utility class which provides some assorted functionality that I can't
 * classify (no pun intended) into other categories.
 *
 * @author Rich Morgan <rich@bitpay.com>
 */
final class Util
{
    /**
     * Encodes a hex value into BASE-58 format.
     *
     * @param  string $hex The value to encode.
     * @return string $ret The encoded value.
     * @throws \Exception
     */
    public function EncodeBase58($hex)
    {
        if (false === isset($hex) || true === empty($hex) || strlen($hex) % 2 != 0) {
            throw new \Exception('Empty or odd number of hex characters passed to encodeBase58() function.');
        }

        $ret        = '';
        $hex_len    = 0;
        $orighex    = '';
        $hex_substr = '';

        $orighex = $hex;
        $hex_len = strlen($orighex);

        if ($this->TestOx($hex) != $hex) {
            $hex = '0x' . strtolower($hex);
        }

        $ret = strrev($this->BaseConvert($hex, '58'));

        $hex_substr = substr($orighex, 0, 2);

        for ($i = 0; $i < $hex_len && $hex_substr == '00'; $i += 2) {
            $ret = '1' . $ret;
            $hex_substr = substr($orighex, $i, 2);
        }

        return $ret;
    }

    /**
     * Consistent string padding workaround.
     *
     * @param  string $value The value to pad.
     * @param  int    $amt   The amount to pad.
     * @return string $value The padded value.
     * @throws \Exception
     */
    private function ZeroPad($value, $amt)
    {
        if (false === isset($value) || true === empty($value)) {
            throw new \Exception('Empty value parameter passed to zeroPad() function.');
        }

        if (false === isset($amt) || true === empty($amt)) {
            throw new \Exception('Empty amt parameter passed to zeroPad() function.');
        }

        $val_len = strlen($value);

        while ($val_len < $amt) {
            $value = '0' . $value;
            $val_len++;
        }

        return (string)$value;
    }
    
    /**
     * MSB check, BC version.
     *
     * @param  string $byte The byte to check.
     * @return string $byte The validated byte.
     * @throws \Exception
     */
    public function MsbCheck($byte)
    {
        if (false === isset($byte) || true === empty($byte)) {
            throw new \Exception('Empty byte parameter passed to bcMsbCheck() function.');
        }

        try {
            if (bccomp('0x' . bin2hex($byte[0]), '0x80') >= 0) {
                $byte = chr(0x00) . $byte;
            }
        } catch (\Exception $e) {
            throw $e;
        }

        return $byte;
    }

    /**
     * Basic range check. Throws exception if out of range.
     *
     * @param  string  $value The coordinate to check.
     * @return boolean        The result of the check.
     * @throws \Exception
     */
    public function RangeCheck($value)
    {
        if (false === isset($value) || true === empty($value)) {
            throw new \Exception('Empty value parameter passed to bcRangeCheck() function.');
        }

        try {
            /* Check to see if $value is in the range [1, n-1] */
            if (bccomp($value, '1') <= 0 && bccomp($value, $this->n) > 0) {
                throw new \Exception('The parameter is out of range. Should be 1 < r < n-1.');
            }
        } catch (\Exception $e) {
            throw $e;
        }

        return true;
    }
}
