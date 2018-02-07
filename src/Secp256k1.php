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
 * This trait specifies the recommended 256-bit elliptic curve domain
 * parameters over Fp associated with the Koblitz curve secp256k1.
 * @see http://www.secg.org/sec2-v2.pdf
 *
 * @author Rich Morgan <rich@richmorgan.me>
 */
trait Secp256k1
{
    /*
     * The elliptic curve domain parameters over Fp associated with a Koblitz curve secp256k1 are
     * specified by the sextuple T = (p, a, b, G, n, h) where the finite field Fp is defined by:
     */

    /**
     * The base point G in uncompressed form in hex:
     *
     * @var string
     */
    public $G = '0479BE667EF9DCBBAC55A06295CE870B07029BFCDB2DCE28D959F2815B16F81798483ADA7726A3C4655DA4FBFC0E1108A8FD17B448A68554199C47D08FFB10D4B8';

    /**
     * 2^256 − 2^32 − 2^9 − 2^8 − 2^7 − 2^6 − 2^4 − 1 in hex:
     *
     * @var string
     */
    public $p_hex = '0xfffffffffffffffffffffffffffffffffffffffffffffffffffffffefffffc2f';

    /**
     * 2^256 − 2^32 − 2^9 − 2^8 − 2^7 − 2^6 − 2^4 − 1 in decimal:
     *
     * @var string
     */
    public $p = '115792089237316195423570985008687907853269984665640564039457584007908834671663';

    /**
     * The curve E: y^2 = x^3 + ax + b over Fp, where a in hex:
     *
     * @var string
     */
    public $a_hex = '0x00';

    /**
     * The curve E: y^2 = x^3 + ax + b over Fp, where a in decimal:
     *
     * @var string
     */
    public $a = '0';

    /**
     * The curve E: y^2 = x^3 + ax + b over Fp, where b in hex:
     *
     * @var string
     */
    public $b_hex = '0x07';

    /**
     * The curve E: y^2 = x^3 + ax + b over Fp, where b in decimal:
     *
     * @var string
     */
    public $b = '7';

    /**
     * The order n of G in hex:
     *
     * @var string
     */
    public $n_hex = '0xfffffffffffffffffffffffffffffffebaaedce6af48a03bbfd25e8cd0364141';

    /**
     * The order n of G in decimal:
     *
     * @var string
     */
    public $n = '115792089237316195423570985008687907852837564279074904382605163141518161494337';

    /**
     * The cofactor in hex:
     *
     * @var string
     */
    public $h_hex = '0x01';

    /**
     * The cofactor in decimal:
     *
     * @var string
     */
    public $h = '1';

    /**
     * X-coordinate of G in hex:
     *
     * @var string
     */
    public $Gx_hex = '0x79be667ef9dcbbac55a06295ce870b07029bfcdb2dce28d959f2815b16f81798';

    /**
     * X-coordinate of G in decimal:
     *
     * @var string
     */
    public $Gx = '55066263022277343669578718895168534326250603453777594175500187360389116729240';

    /**
     * Y-coordinate of G in hex:
     *
     * @var string
     */
    public $Gy_hex = '0x483ada7726a3c4655da4fbfc0e1108a8fd17b448a68554199c47d08ffb10d4b8';

    /**
     * Y-coordinate of G in decimal:
     *
     * @var string
     */
    public $Gy = '32670510020758816978083085130507043184471273380659243275938904335757337482424';
}
