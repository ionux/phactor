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
 * This trait specifies the recommended 256-bit elliptic curve domain
 * parameters over Fp associated with the Koblitz curve secp256k1.
 * @see http://www.secg.org/sec2-v2.pdf
 *
 * @author Rich Morgan <rich@bitpay.com>
 */
trait Secp256k1
{
    /*
     * The elliptic curve domain parameters over Fp associated with a Koblitz curve secp256k1 are
     * specified by the sextuple T = (p, a, b, G, n, h) where the finite field Fp is defined by:
     */

    /**
     * The base point G in uncompressed form.
     *
     * @var string
     */
    public $G = '0479BE667EF9DCBBAC55A06295CE870B07029BFCDB2DCE28D959F2815B16F81798483ADA7726A3C4655DA4FBFC0E1108A8FD17B448A68554199C47D08FFB10D4B8';

    /**
     * 2^256 − 2^32 − 2^9 − 2^8 − 2^7 − 2^6 − 2^4 − 1
     *
     * @var string
     */
    public $p = '0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEFFFFFC2F';

    /**
     * The curve E: y^2 = x^3 + ax + b over Fp, where a:
     *
     * @var string
     */
    public $a = '0x00';

    /**
     * The curve E: y^2 = x^3 + ax + b over Fp, where b:
     *
     * @var string
     */
    public $b = '0x07';

    /**
     * The order n of G
     *
     * @var string
     */
    public $n = '0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEBAAEDCE6AF48A03BBFD25E8CD0364141';

    /**
     * The cofactor
     *
     * @var string
     */
    public $h = '0x01';

    /**
     * X-coordinate of G
     *
     * @var string
     */
    public $Gx = '0x79BE667EF9DCBBAC55A06295CE870B07029BFCDB2DCE28D959F2815B16F81798';

    /**
     * Y-coordinate of G
     *
     * @var string
     */
    public $Gy = '0x483ADA7726A3C4655DA4FBFC0E1108A8FD17B448A68554199C47D08FFB10D4B8';
}
