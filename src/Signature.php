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

    /**
     * Public constructor method.
     *
     * @param  string $message     The message to sign (optional).
     * @param  string $private_key The signer's private key (optional).
     * @return array
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

        if ($key_size < 64) {
            throw new \Exception('Invalid public key format!  Must be a 32-byte (64 character) hex number.');
        }

        $message = trim($message);
        $msg_len = strlen($message);

        if ($msg_len <= 0) {
            throw new \Exception('Cannot sign an empty message!');
        }

        $e = $this->decodeHex(hash('sha256', $message));

        try {
            do {

                if (substr($priv_key, 0, 2) != '0x') {
                    $d = '0x' . $priv_key;
                } else {
                    $d = $priv_key;
                }

                $k     = $this->SecureRandomNumber();
                $k_hex = $this->encodeHex($k);

                /* Calculate a new curve point from Q=k*G (x1,y1) */
                $R = $this->DoubleAndAdd($k_hex, $this->P);

                $Rx_hex = str_pad($this->encodeHex($R['x']), 64, "0", STR_PAD_LEFT);
                $Ry_hex = str_pad($this->encodeHex($R['y']), 64, "0", STR_PAD_LEFT);

                /* r = x1 mod n */
                $r = $this->Modulo($Rx_hex, $this->n);

                /* s = k^-1 * (e+d*r) mod n */
                $edr  = $this->Add($e, $this->Multiply($d, $r));
                $invk = $this->Invert($k_hex, $this->n);
                $kedr = $this->Multiply($invk, $edr);
                $s    = $this->Modulo($kedr, $this->n);

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

        return $this->encoded_signature;
    }

    /**
     * Verifies an ECDSA signature previously generated.
     *
     * @param  string $r   The signature r coordinate in hex.
     * @param  string $s   The signature s coordinate in hex.
     * @param  string $msg The message signed.
     * @param  string $Q   The uncompressed public key of the signer.
     * @return bool        The result of the verification.
     * @throws \Exception
     */
    public function Verify($sig, $msg, $pubkey)
    {
        if (false === isset($sig)     ||
            true  === empty($sig)     ||
            false === isset($msg)     ||
            true  === empty($msg)     ||
            false === isset($pubkey)  ||
            true  === empty($pubkey))
        {
            throw new \Exception('The signature, public key and message parameters are required to verify a signature.');
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

        $coords = $this->parseSig($sig);

        $r = $coords['r'];
        $s = $coords['s'];

        $r_dec = $this->decodeHex($r);
        $s_dec = $this->decodeHex($s);

        $r = $this->CoordinateCheck(trim(strtolower($r)));
        $s = $this->CoordinateCheck(trim(strtolower($s)));

        /* Convert the hash of the hex message to decimal */
        $e = $this->decodeHex(hash('sha256', $msg));

        $n_dec = $this->decodeHex($this->n);
        $p_dec = $this->decodeHex($this->p);

        $pubkey = trim($pubkey);

        if (strlen($pubkey) < 128) {
            throw new \Exception('Unknown public key format - provided value was too short. The uncompressed public key is expected.');
        }

        if (substr($pubkey, 0, 2) == '04') {
            $pubkey = substr($pubkey, 2);
        }

        /* Parse the x,y coordinates */
        $Q = array(
                   'x' => substr($pubkey, 0, 64),
                   'y' => substr($pubkey, 64)
                  );

        if (strlen($Q['x']) < 64 || strlen($Q['y']) < 64 ) {
            throw new \Exception('Unknown public key format - could not parse the x,y coordinates. The uncompressed public key is expected.');
        }

        try {
            /* Calculate w = s^-1 (mod n) */
            $w = $this->Invert($s_dec, $n_dec);

            /* Calculate u1 = e*w (mod n) */
            $u1 = $this->Modulo($this->Multiply($e, $w), $n_dec);

            /* Calculate u2 = r*w (mod n) */
            $u2 = $this->Modulo($this->Multiply($r_dec, $w), $n_dec);

            /* Get new point Z(x1,y1) = (u1 * G) + (u2 * Q) */
            $Za = $this->DoubleAndAdd($u1, $this->P);
            $Zb = $this->DoubleAndAdd($u2, $Q);
            $Z  = $this->PointAdd($Za, $Zb);

            $Zx_hex = str_pad($this->encodeHex($Z['x']), 64, "0", STR_PAD_LEFT);
            $Zy_hex = str_pad($this->encodeHex($Z['y']), 64, "0", STR_PAD_LEFT);

            /*
             * A signature is valid if r is congruent to x1 (mod n)
             * or in other words, if r - x1 is an integer multiple of n.
             */
            return (bool)($this->Compare($r, $Z['x']) == 0);
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
     * Parses a ECDSA signature to retrieve the
     * r and s coordinate pair. Used to verify.
     *
     * @param  string $signature The ECDSA signature to parse.
     * @return array             The r and s coordinates.
     * @throws \Exception
     */
    private function parseSig($signature)
    {
        if (false === isset($signature) || true === empty($signature)) {
            throw new \Exception('You must provide a valid hex parameter.');
        }

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

        if ($ecdsa_struct['totallen'] != '140' && $ecdsa_struct['totallen'] != '142' && $ecdsa_struct['totallen'] != '144') {
            throw new \Exception('Invalid ECDSA signature provided! Length is invalid.');
        }

        $ecdsa_struct['sigstart'] = substr($signature, 0, 2);

        if ($ecdsa_struct['sigstart'] != '30') {
            throw new \Exception('Invalid ECDSA signature provided! Unknown signature format.');
        }

        $signature = substr($signature, 2);
        $ecdsa_struct['siglen'] = substr($signature, 0, 2);

        if ($ecdsa_struct['siglen'] != '44' && $ecdsa_struct['siglen'] != '45' && $ecdsa_struct['siglen'] != '46') {
            throw new \Exception('Invalid ECDSA signature provided!  Total signature length is invalid.');
        }

        $signature = substr($signature, 2);
        $ecdsa_struct['rtype'] = substr($signature, 0, 2);

        if ($ecdsa_struct['rtype'] != '02') {
            throw new \Exception('Invalid ECDSA signature provided!  The r-coordinate data type is invalid.');
        }

        $signature = substr($signature, 2);
        $ecdsa_struct['rlen'] = substr($signature, 0, 2);

        if ($ecdsa_struct['rlen'] != '20' && $ecdsa_struct['rlen'] != '21') {
            throw new \Exception('Invalid ECDSA signature provided!  The r-coordinate length is invalid.');
        } else {
            if ($ecdsa_struct['rlen'] == '21') {
                $ecdsa_struct['roffset'] = 2;
            }
        }

        $signature = substr($signature, 2);
        $ecdsa_struct['r'] = substr($signature, $ecdsa_struct['roffset'], 64);

        if (ctype_xdigit($ecdsa_struct['r']) === false) {
            throw new \Exception('Invalid ECDSA signature provided!  The r-coordinate is not in hex format.');
        }

        $signature = substr($signature, $ecdsa_struct['roffset']+64);
        $ecdsa_struct['stype'] = substr($signature, 0, 2);

        if ($ecdsa_struct['stype'] != '02') {
            throw new \Exception('Invalid ECDSA signature provided!  The s-coordinate data type is invalid.');
        }

        $signature = substr($signature, 2);
        $ecdsa_struct['slen'] = substr($signature, 0, 2);

        if ($ecdsa_struct['slen'] != '20' && $ecdsa_struct['slen'] != '21') {
            throw new \Exception('Invalid ECDSA signature provided!  The s-coordinate length is invalid.');
        } else {
            if ($ecdsa_struct['slen'] == '21') {
                $ecdsa_struct['soffset'] = 2;
            }
        }

        $signature = substr($signature, 2);
        $ecdsa_struct['s'] = substr($signature, $ecdsa_struct['soffset'], 64);

        if (ctype_xdigit($ecdsa_struct['s']) === false) {
            throw new \Exception('Invalid ECDSA signature provided!  The s-coordinate is not in hex format.');
        }

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
        if (false === isset($hex) || true === empty($hex)) {
            throw new \Exception('You must provide a valid coordinate parameter in hex format to check.');
        }

        $tempval = trim(strtolower($hex));
        $prefix  = substr($tempval, 0, 2);

        if ($prefix == '0x') {
            $tempval = substr($tempval, 2);
        } else {
            $prefix  = '0x';
        }

        if (false === ctype_xdigit($tempval) || strlen($tempval) < 64) {
            throw new \Exception('The coordinate value checked was not in hex format or was invalid.');
        }

        $hex = $prefix . $tempval;

        if ($this->Compare($hex, '1') <= 0 || $this->Compare($hex, $this->n) >= 0) {
            throw new \Exception('The coordinate parameter is invalid!  Value is out of range: ' . $hex);
        }

        return $hex;
    }
}
