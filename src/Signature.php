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

    public $encoded_signature;

    private $r_coordinate;
    private $s_coordinate;
    private $raw_signature;
    private $P;
    private $Q;

    /**
     * Public constructor method.
     *
     * @return array
     */
    public function __construct($message = '', $private_key = '')
    {
        $this->encoded_signature = '';
        $this->r_coordinate      = '';
        $this->s_coordinate      = '';
        $this->raw_signature     = '';

        $this->P = array('x' => $this->Gx, 'y' => $this->Gy);
        $this->Q = array('x' => $this->Gx, 'y' => $this->Gy);

        if ($message != '' && $private_key != '') {
            return $this->Generate($message, $private_key);
        }
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
     * @param  string $message     The message to be signed.
     * @param  string $private_key The private key in hex.
     * @return string $signature   The signature data.
     * @throws \Exception
     */
    public function Generate($message, $private_key)
    {
        if (false === isset($private_key) ||
            true  === empty($private_key) ||
            false === isset($message)     ||
            true  === empty($message))
        {
            throw new \Exception('The private key and message parameters are required to generate a signature.');
        }

        $e         = '';
        $d         = '';
        $k         = '';
        $r         = '';
        $s         = '';
        $edr       = '';
        $invk      = '';
        $kedr      = '';
        $k_hex     = '';
        $priv_key  = '';
        $Rx_hex    = '';
        $Rx_hex    = '';
        $key_size  = 0;
        $R         = array();
        $signature = array();

        $priv_key = trim(strtolower($private_key));
        $key_size = strlen($priv_key);

        $e = $this->decodeHex(hash('sha256', $message));

        try {
            do {

                if (substr($private_key, 0, 2) != '0x') {
                    $d = '0x' . $private_key;
                } else {
                    $d = $private_key;
                }

                $k = $this->SecureRandomNumber();

                $k_hex = $this->encodeHex($k);

                // Calculate a new curve point from Q=k*G (x1,y1)
                $R = $this->DoubleAndAdd($k, $this->P);

                $Rx_hex = str_pad($this->encodeHex($R['x']), 64, "0", STR_PAD_LEFT);
                $Ry_hex = str_pad($this->encodeHex($R['y']), 64, "0", STR_PAD_LEFT);

                // r = x1 mod n
                $r = $this->Modulo($Rx_hex, $this->n);

                // s = k^-1 * (e+d*r) mod n
                $edr  = $this->Add($e, $this->Multiply($d, $r));
                $invk = $this->Invert($k_hex, $this->n);
                $kedr = $this->Multiply($invk, $edr);
                $s    = $this->Modulo($kedr, $this->n);

                // The signature is the pair (r,s)
                $signature = array(
                                    'r' => str_pad($this->encodeHex($r), 64, "0", STR_PAD_LEFT),
                                    's' => str_pad($this->encodeHex($s), 64, "0", STR_PAD_LEFT)
                                  );
            } while ($this->Compare($r, '0x00') <= 0 || $this->Compare($s, '0x00') <= 0);
        } catch (\Exception $e) {
            throw $e;
        }

        $this->encoded_signature = $this->Encode($signature['r'], $signature['s']);

        $this->r_coordinate = $signature['r'];
        $this->s_coordinate = $signature['s'];

        if ($this->Verify($this->r_coordinate, $this->s_coordinate, $message, $this->Q)) {
            throw new \Exception('Signature verification failed! Do not use this value!');
        }

        return $this->encoded_signature;
    }

    /**
     * Verifies an ECDSA signature previously generated.
     *
     * @param  string $r   The signature r coordinate in hex.
     * @param  string $s   The signature s coordinate in hex.
     * @param  string $msg The message signed.
     * @param  array  $Q   The base point.
     * @return bool        The result of the verification.
     * @throws \Exception
     */
    public function Verify($r, $s, $msg, $Q)
    {
        if (false === isset($r)   ||
            false === isset($s)   ||
            false === isset($msg) ||
            false === isset($Q)   ||
            true  === empty($r)   ||
            true  === empty($s)   ||
            true  === empty($Q)   ||
            true  === empty($msg))
        {
            throw new \Exception('The signature coordinates, point and message parameters are required to verify a signature.');
        }

        $e         = '';
        $w         = '';
        $u1        = '';
        $u2        = '';
        $Zx_hex    = '';
        $Zy_hex    = '';
        $rsubx     = '';
        $rsubx_rem = '';
        $Za        = array();
        $Zb        = array();
        $Z         = array();

        $r = $this->CoordinateCheck(trim(strtolower($r)));
        $s = $this->CoordinateCheck(trim(strtolower($s)));

        /* Convert the hash of the hex message to decimal */
        $e = $this->decodeHex(hash('sha256', $msg));

        try {
            /* Calculate w = s^-1 (mod n) */
            $w = $this->Invert($s, $this->n);

            /* Calculate u1 = e*w (mod n) */
            $u1 = $this->Modulo($this->Multiply($e, $w), $this->n);

            /* Calculate u2 = r*w (mod n) */
            $u2 = $this->Modulo($this->Multiply($r, $w), $this->n);

            /* Get new point Z(x1,y1) = (u1 * G) + (u2 * Q) */
            $Za = $this->DoubleAndAdd($u1, $this->P);
            $Zb = $this->DoubleAndAdd($u2, $this->Q);
            $Z  = $this->PointAdd($Za, $Zb);

            $Zx_hex = str_pad($this->encodeHex($Z['x']), 64, "0", STR_PAD_LEFT);
            $Zy_hex = str_pad($this->encodeHex($Z['y']), 64, "0", STR_PAD_LEFT);

            /*
             * A signature is valid if r is congruent to x1 (mod n)
             * or in other words, if r - x1 is an integer multiple of n.
             */
            $rsubx     = $this->Subtract($r, $Zx_hex);
            $rsubx_rem = $this->Modulo($rsubx, $this->n);

            return (bool)($this->Compare($rsubx_rem, '0') == 0);
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
        if (false === isset($r)   ||
            false === isset($s)   ||
            true  === empty($r)   ||
            true  === empty($s))
        {
            throw new \Exception('The signature coordinate parameters are required.');
        }

        $byte   = '';
        $seq    = '';
        $dec    = '';
        $retval = array();

        $r = $this->CoordinateCheck(trim(strtolower($r)));
        $s = $this->CoordinateCheck(trim(strtolower($s)));

        $byte = $this->binConv($r);

        if ($this->Compare('0x' . bin2hex($byte[0]), '0x80') >= 0) {
            $byte = chr(0x00) . $byte;
        }

        $retval['bin_r'] = bin2hex($byte);

        $seq  = chr(0x02) . chr(strlen($byte)) . $byte;
        $dec  = $this->decodeHex($s);
        $byte = $this->binConv($s);

        if ($this->Compare('0x' . bin2hex($byte[0]), '0x80') >= 0) {
            $byte = chr(0x00) . $byte;
        }

        $retval['bin_s'] = bin2hex($byte);

        $seq = $seq . chr(0x02) . chr(strlen($byte)) . $byte;
        $seq = chr(0x30) . chr(strlen($seq)) . $seq;

        $retval['seq'] = bin2hex($seq);

        return $retval['seq'];
    }

    /**
     * Decodes PEM data to retrieve the keypair.
     *
     * @param  string $pem_data The data to decode.
     * @return array            The keypair info.
     * @throws \Exception
     */
    public function Decode($pem_data)
    {
        $beg_ec_text = '-----BEGIN EC PRIVATE KEY-----';
        $end_ec_text = '-----END EC PRIVATE KEY-----';

        $decoded = '';

        $ecpemstruct = array();

        $pem_data = str_ireplace($beg_ec_text, '', $pem_data);
        $pem_data = str_ireplace($end_ec_text, '', $pem_data);
        $pem_data = str_ireplace("\r", '', trim($pem_data));
        $pem_data = str_ireplace("\n", '', trim($pem_data));
        $pem_data = str_ireplace(' ',  '', trim($pem_data));

        $decoded = bin2hex(base64_decode($pem_data));

        if (strlen($decoded) < 230) {
            throw new \Exception('Invalid or corrupt secp256k1 key provided. Cannot decode the supplied PEM data.');
        }

        $ecpemstruct = array(
            'oct_sec_val'  => substr($decoded,14,64),
            'obj_id_val'   => substr($decoded,86,10),
            'bit_str_val'  => substr($decoded,106),
        );

        if ($ecpemstruct['obj_id_val'] != '2b8104000a') {
            throw new \Exception('Invalid or corrupt secp256k1 key provided. Cannot decode the supplied PEM data.');
        }

        $private_key = $ecpemstruct['oct_sec_val'];
        $public_key  = $ecpemstruct['bit_str_val'];

        if (strlen($private_key) < 64 || strlen($public_key) < 128) {
            throw new \Exception('Invalid or corrupt secp256k1 key provided. Cannot decode the supplied PEM data.');
        }

        return array('private_key' => $private_key, 'public_key' => $public_key);
    }

    /**
     * Basic coordinate check.
     *
     * @param  string $hex The coordinate to check.
     * @return string $hex The checked coordinate.
     * @throws \Exception
     */
    private function CoordinateCheck($hex)
    {
        if (false === isset($hex) || true === empty($hex)) {
            throw new \Exception('You must provide a valid hex parameter.');
        }

        if (substr($hex, 0, 2) != '0x') {
            $hex = '0x' . $hex;

            if (strlen($hex) < 64) {
                throw new \Exception('The r parameter is invalid. Expected hex string of 64 characters (32-bytes).');
            }
        }

        return $hex;
    }
}
