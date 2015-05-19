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
 * This class implements the elliptic curve math functions required to generate
 * an ECDSA signature based on a previously generated private key.
 *
 * @author Rich Morgan <rich@bitpay.com>
 */
final class Signature
{
    use Point;

    /**
     * @var string
     */
    public $encoded_signature;

    /**
     * @var string
     */
    private $r_coordinate;

    /**
     * @var string
     */
    private $s_coordinate;

    /**
     * @var string
     */
    private $raw_signature;

    /**
     * @var array
     */
    private $P;

    /**
     * Public constructor method.
     *
     * @param  string $message     The message to sign (optional).
     * @param  string $private_key The signer's private key (optional).
     */
    public function __construct($message = '', $private_key = '')
    {
        $this->encoded_signature = '';
        $this->r_coordinate      = '';
        $this->s_coordinate      = '';
        $this->raw_signature     = '';

        $this->P = array(
                         'x' => $this->Gx,
                         'y' => $this->Gy
                        );

        $this->generateFromConstructor($message, $private_key);
    }

    /**
     * Returns the encoded signature.
     *
     * @return string The encoded signature.
     */
    public function __toString()
    {
        return $this->encoded_signature;
    }

    /**
     * Generates an ECDSA signature for a message using the private key
     * parameter in hex format. Returns an associative array of all the
     * signature data including raw point information and the signature.
     *
     * @param  string    $message     The message to be signed.
     * @param  string    $private_key The private key in hex.
     * @return string    $signature   The signature data.
     * @throws \Exception
     */
    public function Generate($message, $private_key)
    {
        $e         = '';
        $k         = '';
        $r         = '';
        $s         = '';

        $R         = array();
        $signature = array();

        $private_key = $this->encodeHex($private_key);

        $this->hexLenCheck($private_key);

        try {

            do {
                /* Get the message hash and a new random number */
                $e = $this->decodeHex(hash('sha256', $message));
                $k = $this->SecureRandomNumber();

                /* Calculate a new curve point from R=k*G (x1,y1) */
                $R      = $this->DoubleAndAdd($k, $this->P);
                $R['x'] = $this->addHexPrefix(str_pad($this->encodeHex($R['x'], false), 64, "0", STR_PAD_LEFT));

                /* r = x1 mod n */
                $r = $this->Modulo($R['x'], $this->n);

                /* s = k^-1 * (e+d*r) mod n */
                $s = $this->Modulo($this->Multiply($this->Invert($k, $this->n), $this->Add($e, $this->Multiply($private_key, $r))), $this->n);

            } while ($this->zeroCompare($r, $s));

        } catch (\Exception $e) {
            throw $e;
        }

        $signature = array(
                           'r' => $this->addHexPrefix(str_pad($this->encodeHex($r, false), 64, "0", STR_PAD_LEFT)),
                           's' => $this->addHexPrefix(str_pad($this->encodeHex($s, false), 64, "0", STR_PAD_LEFT))
                          );

        $this->r_coordinate = $signature['r'];
        $this->s_coordinate = $signature['s'];

        $this->encoded_signature = $this->Encode($this->r_coordinate, $this->s_coordinate);

        return $this->encoded_signature;
    }

    /**
     * Verifies an ECDSA signature previously generated.
     *
     * @param  string $sig     The signature in hex.
     * @param  string $msg     The message signed.
     * @param  string $pubkey  The uncompressed public key of the signer.
     * @return bool            The result of the verification.
     * @throws \Exception
     */
    public function Verify($sig, $msg, $pubkey)
    {
        if (true === empty($sig) || true === empty($msg) || true === empty($pubkey)) {
            throw new \Exception('The signature, public key and message parameters are required to verify a signature.  Value received for first parameter was "' . var_export($sig, true) . '", second parameter was "' . var_export($msg, true) . '" and third parameter was "' . var_export($pubkey, true) . '".');
        }

        $e         = '';
        $w         = '';
        $u1        = '';
        $u2        = '';
        $Z         = array();

        $coords = $this->parseSig($sig);

        $r = $this->CoordinateCheck($this->prepAndClean($coords['r']));
        $s = $this->CoordinateCheck($this->prepAndClean($coords['s']));

        $r_dec = $this->decodeHex($r);
        $s_dec = $this->decodeHex($s);

        /* Convert the hash of the hex message to decimal */
        $e = $this->decodeHex(hash('sha256', $msg));

        $n_dec  = $this->decodeHex($this->n);
        $pubkey = $this->parseUncompressedPublicKey($pubkey);

        /* Parse the x,y coordinates */
        $Q = $this->parseCoordinatePairFromPublicKey($pubkey);

        $this->coordsRangeCheck($Q['x'], $Q['y']);

        try {
            /* Calculate w = s^-1 (mod n) */
            $w = $this->Invert($s_dec, $n_dec);

            /* Calculate u1 = e*w (mod n) */
            $u1 = $this->Modulo($this->Multiply($e, $w), $n_dec);

            /* Calculate u2 = r*w (mod n) */
            $u2 = $this->Modulo($this->Multiply($r_dec, $w), $n_dec);

            /* Get new point Z(x1,y1) = (u1 * G) + (u2 * Q) */
            $Z  = $this->PointAdd($this->DoubleAndAdd($u1, $this->P), $this->DoubleAndAdd($u2, $Q));

            /*
             * A signature is valid if r is congruent to x1 (mod n)
             * or in other words, if r - x1 is an integer multiple of n.
             */
            return $this->congruencyCheck($r, $Z['x']);

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * ASN.1 DER encodes the signature using the form:
     * 0x30 + size(all) + 0x02 + size(r) + r + 0x02 + size(s) + s
     * and if the msb is set add 0x00
     *
     * @param  string $r      The r coordinate in hex.
     * @param  string $s      The s coordinate in hex.
     * @return string $retval The DER encoded signature info.
     * @throws \Exception
     */
    public function Encode($r, $s)
    {
        $r = $this->binConv($this->CoordinateCheck($r));
        $s = $this->binConv($this->CoordinateCheck($s));

        $retval = array(
                        'bin_r' => $this->msbCheck($r[0]) . $r,
                        'bin_s' => $this->msbCheck($s[0]) . $s
                       );

        $seq = chr(0x02) . chr(strlen($retval['bin_r'])) . $retval['bin_r'] .
               chr(0x02) . chr(strlen($retval['bin_s'])) . $retval['bin_s'];

        return bin2hex(chr(0x30) . chr(strlen($seq)) . $seq);
    }

    /**
     * Determines if the msb is set.
     *
     * @param  string $value The binary data to check.
     * @return string
     */
    private function msbCheck($value)
    {
        if ($this->Compare('0x' . bin2hex($value), '0x80') >= 0) {
            return chr(0x00);
        }

        return;
    }

    /**
     * Parses a ECDSA signature to retrieve the
     * r and s coordinate pair. Used to verify.
     *
     * @param  string $signature The ECDSA signature to parse.
     * @return array             The r and s coordinates.
     * @throws \Exception
     */
    private function parseSig($signature)
    {
        $signature = trim($signature);

        /* This is the main structure we'll use for storing our parsed signature. */
        $ecdsa_struct = array(
                              'sigstart' => '',
                              'siglen'   => '',
                              'rtype'    => '',
                              'rlen'     => '',
                              'roffset'  => 0,
                              'r'        => '',
                              'stype'    => '',
                              'slen'     => '',
                              'soffset'  => 0,
                              's'        => '',
                              'original' => '',
                              'totallen' => 0
                             );

        $ecdsa_struct['original'] = $signature;
        $ecdsa_struct['totallen'] = strlen($signature);

        $this->ecdsaSigTotalLenCheck($ecdsa_struct['totallen']);

        $ecdsa_struct['sigstart'] = substr($signature, 0, 2);

        $this->derRecordStartCheck($ecdsa_struct['sigstart']);

        $signature = substr($signature, 2);
        $ecdsa_struct['siglen'] = substr($signature, 0, 2);

        $this->derRecordTotalLenCheck($ecdsa_struct['siglen']);

        $signature = substr($signature, 2);
        $ecdsa_struct['rtype'] = substr($signature, 0, 2);

        $this->derDataTypeCheck($ecdsa_struct['rtype']);

        $signature = substr($signature, 2);
        $ecdsa_struct['rlen'] = substr($signature, 0, 2);

        $this->derDataLenCheck($ecdsa_struct['rlen']);

        $ecdsa_struct['roffset'] = ($ecdsa_struct['rlen'] == '21') ? 2 : 0;

        $signature = substr($signature, 2);
        $ecdsa_struct['r'] = substr($signature, $ecdsa_struct['roffset'], 64);

        $this->RangeCheck($ecdsa_struct['r']);

        $signature = substr($signature, $ecdsa_struct['roffset'] + 64);
        $ecdsa_struct['stype'] = substr($signature, 0, 2);

        $this->derDataTypeCheck($ecdsa_struct['stype']);

        $signature = substr($signature, 2);
        $ecdsa_struct['slen'] = substr($signature, 0, 2);

        $this->derDataLenCheck($ecdsa_struct['slen']);

        $ecdsa_struct['soffset'] = ($ecdsa_struct['slen'] == '21') ? 2 : 0;

        $signature = substr($signature, 2);
        $ecdsa_struct['s'] = substr($signature, $ecdsa_struct['soffset'], 64);

        $this->RangeCheck($ecdsa_struct['r']);

        return array(
                     'r' => $ecdsa_struct['r'],
                     's' => $ecdsa_struct['s']
                    );
    }

    /**
     * Basic coordinate check: verifies 
     *
     * @param  string $hex The coordinate to check.
     * @return string $hex The checked coordinate.
     * @throws \Exception
     */
    private function CoordinateCheck($hex)
    {
        $hex = $this->encodeHex($hex);

        $this->hexLenCheck($hex);
        $this->RangeCheck($hex);

        return $hex;
    }

    /**
     * Ensures the total ECDSA signature length is acceptable.
     *
     * @param  string     $value The signature to check.
     * @throws \Exception
     */
    private function ecdsaSigTotalLenCheck($value)
    {
        if ($value != '140' && $value != '142' && $value != '144') {
            throw new \Exception('Invalid ECDSA signature provided!  Length is out of range for a correct signature.  Value checked was "' . var_export($value, true) . '".');
        }
    }

    /**
     * A DER encoded signature should start with 0x30.
     *
     * @param  string     $value The signature to check.
     * @throws \Exception
     */
    private function derRecordStartCheck($value)
    {
        if ($value != '30') {
            throw new \Exception('Invalid ECDSA signature provided!  Unknown signature format.  Value checked was "' . var_export($value, true) . '".');
        }
    }

    /**
     * Ensures the DER total record length is acceptable.
     *
     * @param  string     $value The record to check.
     * @throws \Exception
     */
    private function derRecordTotalLenCheck($value)
    {
        if ($value != '44' && $value != '45' && $value != '46') {
            throw new \Exception('Invalid ECDSA signature provided!  DER record length is invalid.  Value checked was "' . var_export($value, true) . '".');
        }
    }

    /**
     * Ensures the DER variable data type is acceptable.
     *
     * @param  string     $value The variable to check.
     * @throws \Exception
     */
    private function derDataTypeCheck($value)
    {
        if ($value != '02') {
            throw new \Exception('Invalid ECDSA signature provided!  DER record length is invalid.  Value checked was "' . var_export($value, true) . '".');
        }
    }

    /**
     * Ensures the DER variable data length is acceptable.
     *
     * @param  string     $value The variable to check.
     * @throws \Exception
     */
    private function derDataLenCheck($value)
    {
        if ($value != '20' && $value != '21') {
            throw new \Exception('Invalid ECDSA signature provided!  The coordinate length is invalid.  Value checked was "' . var_export($value, true) . '".');
        }
    }

    /**
     * Checks if the uncompressed public key has the 0x04 prefix.
     *
     * @param  string $pubkey The key to check.
     * @return string         The public key without the prefix.
     */
    private function parseUncompressedPublicKey($pubkey)
    {
        return (substr($pubkey, 0, 2) == '04') ? $this->prepAndClean(substr($pubkey, 2)) : $this->prepAndClean($pubkey);
    }

    /**
     * Checks the range of a pair of coordinates.
     *
     * @param  string     $x The key to check.
     * @param  string     $y The key to check.
     */
    private function coordsRangeCheck($x, $y)
    {
        $this->RangeCheck($x);
        $this->RangeCheck($y);
    }

    /**
     * Parses the x & y coordinates from an uncompressed public key.
     *
     * @param  string     $pubkey The key to parse.
     * @return array              The public key (x,y) coordinates.
     */
    private function parseCoordinatePairFromPublicKey($pubkey)
    {
        return array(
                    'x' => $this->addHexPrefix(substr($pubkey, 0, 64)),
                    'y' => $this->addHexPrefix(substr($pubkey, 64))
                    );
    }

    /**
     * Congruency check for two values.
     *
     * @param  string $r  The first coordinate to check.
     * @param  string $x  The second coordinate to check.
     * @return boolean    Returns true if values are congruent.
     * @throws \Exception
     */
    private function congruencyCheck($r, $x)
    {
        if ($this->Compare($r, $this->encodeHex($x)) == 0) {
            return true;
        } else {
            throw new \Exception('The signature is invalid!  Value used for $r was "' . var_export($r, true) . '" and the calculated $x parameter was "' . var_export($this->encodeHex($x), true) . '".');
        }
    }

    /**
     * Called to generate a signature if values are passed to the constructor.
     *
     * @param  string $message     The message to sign.
     * @param  string $private_key The signer's private key.
     */
    private function generateFromConstructor($message, $private_key)
    {
        if ($message != '' && $private_key != '') {
            $this->Generate($message, $private_key);
        }
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

    /**
     * Checks if two parameters are less than or equal to zero.
     *
     * @param  string $a  The first parameter to check.
     * @param  string $a  The first parameter to check.
     * @return boolean    Result of the check.
     */
    private function zeroCompare($a, $b)
    {
        return ($this->Compare($a, '0x00') <= 0 || $this->Compare($b, '0x00') <= 0);
    }
}
