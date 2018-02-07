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
 * This class implements the elliptic curve math functions required to generate
 * an ECDSA signature based on a previously generated private key.
 *
 * @author Rich Morgan <rich@richmorgan.me>
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
     * @var \Phactor\Key
     */
    private $keyUtil;

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

        $this->keyUtil = new \Phactor\Key;

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
                $e = $this->decodeHex('0x' . hash('sha256', $message));
                $k = $this->SecureRandomNumber();

                /* Calculate a new curve point from R=k*G (x1,y1) */
                $R      = $this->DoubleAndAdd($this->P, $k);
                $R['x'] = $this->addHexPrefix(str_pad($this->encodeHex($R['x'], false), 64, "0", STR_PAD_LEFT));

                /* r = x1 mod n */
                $r = $this->Modulo($this->decodeHex($R['x']), $this->n);

                /* s = k^-1 * (e+d*r) mod n */
                $dr  = $this->Multiply($this->decodeHex($private_key), $r);
                $edr = $this->Add($e, $dr);
                $s   = $this->Modulo($this->Multiply($this->Invert($k, $this->n), $edr), $this->n);
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

        $pubkey = (substr($pubkey, 0, 2) == '04') ? $this->keyUtil->parseUncompressedPublicKey($pubkey) : $this->keyUtil->parseCompressedPublicKey($pubkey);

        /* Parse the x,y coordinates */
        $Q = $this->keyUtil->parseCoordinatePairFromPublicKey($pubkey);

        list($Q['x'], $Q['y']) = array($this->decodeHex($Q['x']), $this->decodeHex($Q['y']));

        $this->coordsRangeCheck($Q['x'], $Q['y']);

        try {
            /* Calculate w = s^-1 (mod n) */
            $w = $this->Invert($s_dec, $n_dec);

            /* Calculate u1 = e*w (mod n) */
            $u1 = $this->Modulo($this->Multiply($e, $w), $n_dec);

            /* Calculate u2 = r*w (mod n) */
            $u2 = $this->Modulo($this->Multiply($r_dec, $w), $n_dec);

            /* Get new point Z(x1,y1) = (u1 * G) + (u2 * Q) */
            $Z  = $this->pointAddW($this->DoubleAndAdd($this->P, $u1), $this->DoubleAndAdd($Q, $u2));

            /*
             * A signature is valid if r is congruent to x1 (mod n)
             * or in other words, if r - x1 is an integer multiple of n.
             */
            $congruent = $this->congruencyCheck($this->decodeHex($r), $this->decodeHex($Z['x']));

            return $congruent;

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * ASN.1 DER encodes the signature using the form:
     * 0x30 + size(all) + 0x02 + size(r) + r + 0x02 + size(s) + s
     * and if the msb is set add 0x00
     *
     * @param  string $r  The r coordinate in hex.
     * @param  string $s  The s coordinate in hex.
     * @return string     The DER encoded signature info.
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
     * Parses a ECDSA signature to retrieve the r and s coordinate pair. Used to verify a point.
     *
     * @param  string $signature The ECDSA signature to parse.
     * @return array             The r and s coordinates.
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
     * Called to generate a signature if values are passed to the constructor.
     *
     * @param  string $message     The message to sign.
     * @param  string $private_key The signer's private key.
     */
    private function generateFromConstructor($message, $private_key)
    {
        if (empty($message) === false && empty($private_key) === false) {
            $this->Generate($message, $private_key);
        }
    }
}
