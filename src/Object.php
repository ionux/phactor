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
 * The very base, primary Object trait for the Phactor library.
 *
 * @author Rich Morgan <rich@bitpay.com>
 */
trait Object
{
    /**
     * Set this to false if you want to disable the
     * various parameter checks to improve performance.
     * It could definitely break things if you don't
     * ensure the values are legitimate yourself, though!
     *
     * @var boolean
     */
    private $param_checking = true;

    /**
     * @var array
     */
    private $bytes = array();

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
     * Trims() and strtolowers() the value.
     *
     * @param  mixed $value  The value to clean.
     * @return string        The clean value.
     */
    private function prepAndClean($value)
    {
        return strtolower(trim($value));
    }

    /**
     * Checks if a value is null or not set.
     *
     * @param  mixed $value  The value to be checked.
     * @return boolean       Either true or false.
     */
    private function nullTest($value)
    {
        return (isset($value) || is_null($value));
    }

    /**
     * Checks if a value is an object
     *
     * @param  mixed $value  The value to be checked.
     * @return boolean       Either true or false.
     */
    private function objTest($value)
    {
        return (nullTest($value) && is_object($value));
    }

    /**
     * Checks if a value is an array
     *
     * @param  mixed $value  The value to be checked.
     * @return boolean       Either true or false.
     */
    private function arrTest($value)
    {
        return (nullTest($value) && is_array($value));
    }

    /**
     * Checks if a value is an boolean
     *
     * @param  mixed $value  The value to be checked.
     * @return boolean       Either true or false.
     */
    private function boolTest($value)
    {
        return (nullTest($value) && is_bool($value));
    }

    /**
     * Checks if a value is a resource
     *
     * @param  mixed $value  The value to be checked.
     * @return boolean       Either true or false.
     */
    private function resTest($value)
    {
        return (nullTest($value) && is_resource($value));
    }

    /**
     * Checks if a value is a string
     *
     * @param  mixed $value  The value to be checked.
     * @return boolean       Either true or false.
     */
    private function strTest($value)
    {
        return (nullTest($value) && is_string($value));
    }
}
