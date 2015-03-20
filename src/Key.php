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
 * Class for working with asymmetric keypairs.
 *
 * @author Rich Morgan <rich@bitpay.com>
 */
final class Key
{
    use Point;

    private $publicKey;
    private $privateKey;
    private $keyInfo;

    /**
     * Public constructor class.
     */
    public function __construct(array $parameters = null)
    {
        $this->publicKey  = '';
        $this->privateKey = '';

        $this->keyInfo = array(
                               'private_key_hex'       => '',
                               'private_key_dec'       => '',
                               'public_key'            => '',
                               'public_key_compressed' => '',
                               'public_key_x'          => '',
                               'public_key_y'          => '',
                               'generation_time'       => '',
                              );

        if (true === isset($params) && true === is_array($params)) {
            if (true === isset($params['private_key_hex'])) {
            	$this->keyInfo['private_key_hex'] = $params['private_key_hex'];
            }

            if (true === isset($params['private_key_dec'])) {
            	$this->keyInfo['private_key_dec'] = $params['private_key_dec'];
            }

            if (true === isset($params['public_key'])) {
            	$this->keyInfo['public_key'] = $params['public_key'];
            }

            if (true === isset($params['public_key_compressed'])) {
            	$this->keyInfo['public_key_compressed'] = $params['public_key_compressed'];
            }

            if (true === isset($params['public_key_x'])) {
            	$this->keyInfo['private_key_hex'] = $params['public_key_x'];
            }

            if (true === isset($params['public_key_x'])) {
                $this->keyInfo['private_key_hex'] = $params['public_key_x'];
            }
    	}
    }

    /**
     * Returns the key information array JSON encoded.
     *
     * @return string
     */
    public function __toString()
    {
        return json_encode($this->keyInfo);
    }

    /**
     * Checks to see if a public key exists or not.
     *
     * @return bool The existence of a public key.
     */
    public function PublicKeyExists()
    {
        return !($this->publicKey == '');
    }

    /**
     * Checks to see if a private key exists or not.
     *
     * @return bool The existence of a private key.
     */
    public function PrivateKeyExists()
    {
        return !($this->privateKey == '');
    }

    /**
     * Returns the private key value or false on failure.
     *
     * @return string|bool
     */
    public function GetPrivateKey()
    {
        if ($this->PrivateKeyExists()) {
            return $this->privateKey;
        }

        return false;
    }

    /**
     * Returns the public key value or false on failure.
     *
     * @return string|false
     */
    public function GetPublicKey($format = 'compressed')
    {
        if ($this->PublicKeyExists()) {
            if ($format == 'compressed') {
                return $this->keyInfo['public_key_compressed'];
            }
        } else {
            return $this->publicKey;
        }

        return false;
    }

    /**
     * Returns the complete keypair info array or false on failure.
     *
     * @return string|false
     */
    public function KeypairInfo()
    {
        if ($this->PrivateKeyExists()) {
            return $this->keyInfo;
        }

        return false;
    }

    /**
     * This is the main function for generating the new keypair.
     *
     * @param  bool   $verbose Shows extra debugging output.
     * @return array  $keyInfo The complete keypair array.
     */
    public function GenerateKeypair()
    {
        $comp_prefix  = '';

        $point = $this->GenerateNewPoint();


        if (substr($point['Rx_hex'], 0, 2) == '0x') {
            $point['Rx_hex'] = substr($point['Rx_hex'], 2);
        }

        if (substr($point['Ry_hex'], 0, 2) == '0x') {
            $point['Ry_hex'] = substr($point['Ry_hex'], 2);
        }

        if (substr($point['random_number'], 0, 2) == '0x') {
            $point['random_number'] = substr($point['random_number'], 2);
        }

        if ($this->Modulo('0x' . $point['Ry_hex'] , '0x02') == '1') {
            $comp_prefix = '03';
        } else {
            $comp_prefix = '02';
        }

        $this->keyInfo = array(
                               'private_key_hex'       => $point['random_number'],
                               'private_key_dec'       => $this->decodeHex($point['random_number']),
                               'public_key'            => '04' . $point['Rx_hex'] . $point['Ry_hex'],
                               'public_key_compressed' => $comp_prefix . $point['Rx_hex'],
                               'public_key_x'          => $point['Rx_hex'],
                               'public_key_y'          => $point['Ry_hex'],
                              );

        $this->publicKey  = '04' . $point['Rx_hex'] . $point['Ry_hex'];
        $this->privateKey = $point['random_number'];

        return $this->keyInfo;
    }
}
