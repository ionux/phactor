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
 * This trait implements the elliptic curve math functions required to generate
 * new EC points based on the secp256k1 curve parameters.
 *
 * @author Rich Morgan <rich@richmorgan.me>
 */
trait Point
{
    use Math, Secp256k1;

    /**
     * EC Point addition method P + Q = R where:
     *   s = (yP - yQ) / (xP - xQ) mod p
     *   xR = s2 - xP - xQ mod p
     *   yR = -yP + s(xP - xR) mod p
     *
     * @param  array|string $P The first point to add.
     * @param  array|string $Q The second point to add.
     * @return array        $R The result of the point addition.
     * @throws \Exception
     */
    public function pointAddW($P, $Q)
    {
        if ($this->pointType($P) == 'nul' || $this->pointType($Q) == 'nul') {
            throw new \Exception('You must provide valid point parameters to add.');
        }

        $infCheck = $this->infPointCheck($P, $Q);

        if ($infCheck != null) {
            return $infCheck;
        }

        if ($P == $Q) {
            return $this->pointDoubleW($P);
        }

        $ss = '0';

        $R = array('x' => '0', 'y' => '0');

        try {
            $mm = $this->Subtract($P['y'], $Q['y']);
            $nn = $this->Subtract($P['x'], $Q['x']);
            $oo = $this->Invert($nn, $this->p);
            $st = $this->Multiply($mm, $oo);
            $ss = $this->Modulo($st, $this->p);

            $R['x'] = $this->Modulo($this->Subtract($this->Subtract($this->Multiply($ss, $ss), $P['x']), $Q['x']), $this->p);
            $R['y'] = $this->Modulo($this->Add($this->Subtract('0', $P['y']), $this->Multiply($ss, $this->Subtract($P['x'], $R['x']))), $this->p);

            return $R;

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Point multiplication method 2P = R where
     *   s = (3xP2 + a) / (2yP) mod p
     *   xR = s2 - 2xP mod p
     *   yR = -yP + s(xP - xR) mod p
     *
     * @param  array|string  $P The point to multiply.
     * @return array|string  $R The multiplied point.
     * @throws \Exception
     */
    public function pointDoubleW($P)
    {
        switch ($this->pointType($P)) {
            case 'inf':
                return $this->Inf;
            case 'nul':
                throw new \Exception('You must provide a valid point parameter to double.');
        }

        $ss = '0';

        $R = array('x' => '0', 'y' => '0');

        try {
            $mm   = $this->Add($this->Multiply('3', $this->Multiply($P['x'], $P['x'])), $this->a);
            $oo   = $this->Multiply('2', $P['y']);
            $nn   = $this->Invert($oo, $this->p);
            $st   = $this->Multiply($mm, $nn);
            $ss   = $this->Modulo($st, $this->p);
            $xmul = $this->Multiply('2', $P['x']);
            $smul = $this->Multiply($ss, $ss);
            $xsub = $this->Subtract($smul, $xmul);
            $xmod = $this->Modulo($xsub, $this->p);

            $R['x'] = $xmod;

            $ysub  = $this->Subtract($P['x'], $R['x']);
            $ymul  = $this->Multiply($ss, $ysub);
            $ysub2 = $this->Subtract('0', $P['y']);
            $yadd  = $this->Add($ysub2, $ymul);

            $R['x'] = $R['x'];
            $R['y'] = $this->Modulo($yadd, $this->p);

            return $R;

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Performs a test of an EC point by substituting the new
     * values into the equation for the Weierstrass form of the curve.
     *
     * @param  array|string $P  The generated point to test.
     * @return bool             Whether or not the point is valid.
     * @throws \Exception
     */
    public function pointTestW($P)
    {
        if (is_array($P) === false) {
            throw new \Exception('Point test failed! Cannot test a point without coordinates.');
        }

        /*
         * Weierstrass form of the elliptic curve:
         * y^2 (mod p) = x^3 + ax + b (mod p)
         */
        $y2    = '';
        $x3    = '';
        $ax    = '';
        $left  = '';
        $right = '';

        try {
            /* Left y^2 term */
            $y2 = $this->Multiply($P['y'], $P['y']);

            /* Right, first x^3 term */
            $x3 = $this->Multiply($this->Multiply($P['x'], $P['x']), $P['x']);

            /* Right, second ax term */
            $ax = $this->Multiply($this->a, $P['x']);

            /*
             * If the right side of the equation equals the left,
             * we have a valid point, agebraically speaking.
             */
            $left  = $this->Modulo($y2, $this->p);
            $right = $this->Modulo($this->Add($this->Add($x3, $ax), $this->b), $this->p);

            $test_point = array('x' => $right, 'y' => $left, 'y2' => $y2, 'x3' => $x3, 'ax' => $ax);

            if ($left == $right) {
                return true;
            } else {
                throw new \Exception('Point test failed! Cannot continue. I tested the point: ' . var_export($P, true) . ' but got the point: ' . var_export($test_point, true));
            }

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Pure PHP implementation of the Double-And-Add algorithm, for more info see:
     * http://en.wikipedia.org/wiki/Elliptic_curve_point_multiplication#Double-and-add
     *
     * @param  array        $P Base EC curve point.
     * @param  string       $x Scalar value.
     * @return array|string $S Either 'infinity' or the new coordinates.
     */
    public function doubleAndAdd($P, $x = '1')
    {
        $tmp = $this->D2B($x);
        $n   = strlen($tmp) - 1;
        $S   = $this->Inf;

        while ($n >= 0) {
            $S = $this->pointDoubleW($S);
            $S = ($tmp[$n] == '1') ? $this->pointAddW($S, $P) : $S;

            $n--;
        }

        return $S;
    }

    /**
     * Pure PHP implementation of the Montgomery Ladder algorithm which helps protect
     * us against side-channel attacks.  This performs the same number of operations
     * regardless of the scalar value being used as the multiplier.  It's slower than
     * the traditional double-and-add algorithm because of that fact but safer to use.
     *
     * @param  array        $P Base EC curve point.
     * @param  string       $x Scalar value.
     * @return array|string $S Either 'infinity' or the new coordinates.
     */
    public function mLadder($P, $x = '1')
    {
        $tmp = $this->D2B($x);
        $n   = strlen($tmp) - 1;
        $S0  = $this->Inf;
        $S1  = $P;

        while ($n >= 0) {
            switch ($tmp[$n]) {
                case '0':
                    $S1 = $this->pointAddW($S0, $S1);
                    $S0 = $this->pointDoubleW($S0);
                    break;
                default:
                    $S0 = $this->pointAddW($S0, $S1);
                    $S1 = $this->pointDoubleW($S1);
                    break;
            }

            $n--;
        }

        return $S0;
    }

    /**
     * Creates a new point on the elliptic curve.
     *
     * @param  boolean   $ladder Whether or not to use the mladder method.
     * @return array             The new EC point.
     * @throws \Exception
     */
    public function GenerateNewPoint($ladder = true)
    {
        $P = array(
                   'x' => $this->Gx,
                   'y' => $this->Gy
                  );

        do {
            $random_number = $this->SecureRandomNumber();
        } while ($this->randCompare($random_number));

        $R = ($ladder === true) ? $this->mLadder($P, $random_number) : $this->doubleAndAdd($P, $random_number);

        if ($this->pointTestW($R)) {
            $Rx_hex = str_pad($this->encodeHex($R['x']), 64, "0", STR_PAD_LEFT);
            $Ry_hex = str_pad($this->encodeHex($R['y']), 64, "0", STR_PAD_LEFT);
        } else {
            throw new \Exception('Point test failed! Cannot continue. I got the point: ' . var_export($R, true));
        }

        return array(
                     'random_number' => $random_number,
                     'R'             => $R,
                     'Rx_hex'        => $Rx_hex,
                     'Ry_hex'        => $Ry_hex
                    );
    }

    /**
     * Recalculates the y-coordinate from a compressed public key.
     *
     * @param  string  $x_coord        The x-coordinate.
     * @param  string  $compressed_bit The hex compression value (03 or 02).
     * @return string  $y              The calculated y-coordinate.
     * @throws \Exception $e
     */
    public function calcYfromX($x_coord, $compressed_bit)
    {
        try {
            $x = $this->decodeHex($this->addHexPrefix($x_coord));
            $c = $this->Subtract($this->decodeHex($this->addHexPrefix($compressed_bit)), '2');
            $a = $this->Modulo($this->Add($this->PowMod($x, '3', $this->p), '7'), $this->p);
            $y = $this->PowMod($a, $this->Divide($this->Add($this->p, '1'), '4'), $this->p);
            $y = ($this->Modulo($y, '2') != $c) ? $this->decodeHex($this->Modulo($this->Multiply('-1', $y), $this->p)) : $this->decodeHex($y);

            return $this->encodeHex($y);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Basic range check. Throws exception if coordinate value is out of range.
     *
     * @param  string     $value The coordinate to check.
     * @return boolean           The result of the check.
     */
    public function RangeCheck($value)
    {
        $this->preOpMethodParamsCheck(array($value));

        return true;
    }

    /**
     * Checks the basic type of the point value.
     *
     * @param  mixed $value The point to check.
     * @return string       The result of the check.
     * @codeCoverageIgnore
     */
    private function pointType($value)
    {
        if (true === $this->arrTest($value)) {
            return 'arr';
        }

        if ($this->Inf == $value) {
            return 'inf';
        }

        return 'nul';
    }

    /**
     * Checks the range of a pair of coordinates.
     *
     * @param  string     $x The key to check.
     * @param  string     $y The key to check.
     * @codeCoverageIgnore
     */
    private function coordsRangeCheck($x, $y)
    {
        //$this->RangeCheck($x);
        //$this->RangeCheck($y);
        return true;
    }

    /**
     * Basic coordinate check: verifies $hex is valid
     *
     * @param  string $hex The coordinate to check.
     * @return string $hex The checked coordinate.
     * @codeCoverageIgnore
     */
    private function CoordinateCheck($hex)
    {
        //$hex = $this->encodeHex($hex);

        //$this->hexLenCheck($hex);
        //$this->RangeCheck($hex);

        return $hex;
    }

    /**
     * Checks if a Point is infinity or equal to another point.
     *
     * @param array|string $pointOne The first point to check.
     * @param array|string $pointTwo The second point to check.
     * @return mixed                 The result value to return or null.
     * @codeCoverageIgnore
     */
    private function infPointCheck($pointOne, $pointTwo)
    {
        if ($pointOne == $this->Inf || false === $this->arrTest($pointOne)) {
            return $pointTwo;
        }

        if ($pointTwo == $this->Inf || false === $this->arrTest($pointTwo)) {
            return $pointOne;
        }

        if (($pointOne['x'] == $pointTwo['x']) && ($pointOne['y'] != $pointTwo['y'])) {
            return $this->Inf;
        }
        
        return null;
    }
    
    /**
     * Checks if a number is within a certain range:
     *   0x01 < number < n
     *
     * @param  string  $value  The number to check.
     * @return boolean         The result of the comparison.
     * @codeCoverageIgnore
     */
    private function randCompare($value)
    {
        return ($this->Compare($value, '1') <= 0 || $this->Compare($value, $this->n) >= 0);
    }
}
