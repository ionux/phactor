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
 * Abstract Syntax Notation One (ASN.1) encoding class.
 *
 * @author Rich Morgan <rich@bitpay.com>
 */
trait ASN1
{
    use Math;

    public function decodeOID($data) {
        //$data = array(0x2A,0x86,0x48,0x86,0xF7,0x0D,0x01,0x07,0x07);

        $bin_data = array();
        $index = 0;
        $binary_string = '';
        $oid = array();

        // first two numbers are: (40*x)+y
        // all next chars are 7-bit numbers
        foreach ($data as $key => $value) {
            $bin_data[$index] = bitConv($value);
            $binary_string .= $bin_data[$index];
            $index++;
        }

        $oid[0] = (int)($data[0] / 0x28);
        $oid[1] = $data[0] % 0x28;

        $elements = count($bin_data);

        $temp_number = '00000000';

        for ($x=1; $x<$elements; $x++) {
            $and_temp = $bin_data[$x] & '01111111';

            $temp_number = bitAdd($temp_number, $and_temp);

            if (substr($bin_data[$x], 0, 1) == '0') {
                // This is a final value without
                // a value preceeding it.
                $oid[$x+1]   = decConv($temp_number);
                $temp_number = '';
            } else {
                $temp_number = shiftLeft($temp_number, 7);
            }
        }

        $oid_string = '';

        foreach ($oid as $key => $value) {
            $oid_string .= $value . ".";
        }

        $oid_string = substr($oid_string, 0, -1);

        return $oid_string;
    }
}
