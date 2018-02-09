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
 * Class for working with asymmetric keypairs in both hex and decimal forms.
 *
 * @author Rich Morgan <rich@richmorgan.me>
 */
final class Key
{
    use Point, ASN1;

    /**
     * @var array
     */
    private $keyInfo;

    /**
     * Public constructor class.
     *
     * @param array $params Precalculated key values to load into this object.
     */
    public function __construct(array $params = null)
    {
        $this->keyInfo = array(
                               'private_key_hex'       => $this->keyValueCheck($params['private_key_hex']),
                               'private_key_dec'       => $this->keyValueCheck($params['private_key_dec']),
                               'public_key'            => $this->keyValueCheck($params['public_key']),
                               'public_key_compressed' => $this->keyValueCheck($params['public_key_compressed']),
                               'public_key_x'          => $this->keyValueCheck($params['public_key_x']),
                               'public_key_y'          => $this->keyValueCheck($params['public_key_y']),
                               'generation_time'       => '',
                              );
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
     * Returns the private key value in hex or decimal.
     *
     * @param  bool   $hex
     * @return string
     */
    public function getPrivateKey($hex = true)
    {
        return ($hex === true) ? $this->keyInfo['private_key_hex'] : $this->keyInfo['private_key_dec'];
    }

    /**
     * Returns the compressed or uncompressed public key value.
     *
     * @param  string $format
     * @return string
     */
    public function getPublicKey($format = 'compressed')
    {
        return ($format == 'compressed') ? $this->keyInfo['public_key_compressed'] : $this->keyInfo['public_key'];
    }

    /**
     * Returns the complete keypair info array.
     *
     * @return array
     */
    public function getKeypairInfo()
    {
        return $this->keyInfo;
    }

    /**
     * This is the main function for generating a new keypair.
     *
     * @return array $keyInfo The complete keypair array.
     */
    public function GenerateKeypair()
    {
        $point = $this->GenerateNewPoint();

        $point['Rx_hex']        = $this->stripHexPrefix($point['Rx_hex']);
        $point['Ry_hex']        = $this->stripHexPrefix($point['Ry_hex']);
        $point['random_number'] = $this->stripHexPrefix($point['random_number']);

        $comp_prefix = ($this->Modulo($point['R']['y'], '2') == '1') ? '03' : '02';

        $this->keyInfo = array(
                               'private_key_hex'       => $this->encodeHex($point['random_number']),
                               'private_key_dec'       => $point['random_number'],
                               'public_key'            => '04' . $point['Rx_hex'] . $point['Ry_hex'],
                               'public_key_compressed' => $comp_prefix . $point['Rx_hex'],
                               'public_key_x'          => $point['Rx_hex'],
                               'public_key_y'          => $point['Ry_hex'],
                              );

        return $this->keyInfo;
    }

    /**
     * Checks if the uncompressed public key has the 0x04 prefix.
     *
     * @param  string $pubkey The key to check.
     * @return string         The public key without the prefix.
     */
    public function parseUncompressedPublicKey($pubkey)
    {
        return (substr($pubkey, 0, 2) == '04') ? $this->prepAndClean(substr($pubkey, 2)) : $this->prepAndClean($pubkey);
    }

    /**
     * Parses a compressed public key with the 0x02 or 0x03 prefix.
     *
     * @param  string $pubkey     The key to check.
     * @param  bool   $returnHex  Whether or not to return the value in decimal or hex.
     * @return string             The (x,y) coordinate pair.
     */
    public function parseCompressedPublicKey($pubkey, $returnHex = false)
    {
        $prefix = substr($pubkey, 0, 2);

        if ($prefix !== '02' && $prefix !== '03') {
            return $this->prepAndClean($pubkey);
        }

        $pointX = substr($pubkey, 2);
        $pointY = substr($this->calcYfromX($pointX, $prefix), 2);

        $parsedValue = $this->prepAndClean($pointX . $pointY);

        return ($returnHex === false) ? $parsedValue : $this->encodeHex($parsedValue);
    }

    /**
     * Parses the x & y coordinates from an uncompressed public key.
     *
     * @param  string $pubkey The key to parse.
     * @return array          The public key (x,y) coordinates.
     */
    public function parseCoordinatePairFromPublicKey($pubkey)
    {
        return array(
                    'x' => $this->addHexPrefix(substr($pubkey, 0, 64)),
                    'y' => $this->addHexPrefix(substr($pubkey, 64))
                    );
    }

    /**
     * Returns the value, if exists.
     *
     * @param  string $value The value to check.
     * @return string $value The value, if set.
     */
    public function keyValueCheck($value)
    {
        return (isset($value)) ? $value : '';
    }
}
