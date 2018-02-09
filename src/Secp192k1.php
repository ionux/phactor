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
 * This trait specifies the recommended 192-bit elliptic curve domain
 * parameters over Fp associated with the Koblitz curve secp192k1.
 * @see http://www.secg.org/sec2-v2.pdf
 *
 * @author Rich Morgan <rich@richmorgan.me>
 */
trait Secp192k1
{
    /*
     * The elliptic curve domain parameters over Fp associated with a Koblitz curve secp192k1 are
     * specified by the sextuple T = (p, a, b, G, n, h) where the finite field Fp is defined by:
     */

    /**
     * The base point G in uncompressed form in hex:
     *
     * @var string
     */
    public $G = '04db4ff10ec057e9ae26b07d0280b7f4341da5d1b1eae06c7d9b2f2f6d9c5628a7844163d015be86344082aa88d95e2f9d';

    /**
     * 2^192 − 2^32 − 2^12 − 2^8 − 2^7 − 2^6 − 2^3 − 1 in hex:
     *
     * @var string
     */
    public $p_hex = '0xfffffffffffffffffffffffffffffffffffffffeffffee37';

    /**
     * 2^192 − 2^32 − 2^12 − 2^8 − 2^7 − 2^6 − 2^3 − 1 in decimal:
     *
     * @var string
     */
    public $p = '6277101735386680763835789423207666416102355444459739541047';

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
    public $b_hex = '0x03';

    /**
     * The curve E: y^2 = x^3 + ax + b over Fp, where b in decimal:
     *
     * @var string
     */
    public $b = '3';

    /**
     * The order n of G in hex:
     *
     * @var string
     */
    public $n_hex = '0xfffffffffffffffffffffffe26f2fc170f69466a74defd8d';

    /**
     * The order n of G in decimal:
     *
     * @var string
     */
    public $n = '6277101735386680763835789423061264271957123915200845512077';

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
    public $Gx_hex = '0xdb4ff10ec057e9ae26b07d0280b7f4341da5d1b1eae06c7d';

    /**
     * X-coordinate of G in decimal:
     *
     * @var string
     */
    public $Gx = '5377521262291226325198505011805525673063229037935769709693';

    /**
     * Y-coordinate of G in hex:
     *
     * @var string
     */
    public $Gy_hex = '0x9b2f2f6d9c5628a7844163d015be86344082aa88d95e2f9d';

    /**
     * Y-coordinate of G in decimal:
     *
     * @var string
     */
    public $Gy = '3805108391982600717572440947423858335415441070543209377693';
}
