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
 * Class for working with asymmetric keypairs in both hex and decimal forms.
 *
 * @author Rich Morgan <rich@bitpay.com>
 */
final class Key
{
    use Point;

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
                $this->keyInfo['public_key_x'] = $params['public_key_x'];
            }

            if (true === isset($params['public_key_y'])) {
                $this->keyInfo['public_key_y'] = $params['public_key_y'];
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
     * Returns the private key value in hex or decimal.
     *
     * @param  bool
     * @return string
     */
    public function getPrivateKey($hex = true)
    {
        if ($hex === true) {
            return $this->keyInfo['private_key_hex'];
        } else {
            return $this->keyInfo['private_key_dec'];
        }
    }

    /**
     * Returns the compressed or uncompressed public key value.
     *
     * @param  string
     * @return string
     */
    public function getPublicKey($format = 'compressed')
    {
        if ($format == 'compressed') {
            return $this->keyInfo['public_key_compressed'];
        } else {
            return $this->keyInfo['public_key'];
        }
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
     * @return array  $keyInfo The complete keypair array.
     */
    public function GenerateKeypair()
    {
        $comp_prefix = '';

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

        if ($this->Modulo('0x' . $point['Ry_hex'], '0x02') == '1') {
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

        return $this->keyInfo;
    }

    /**
     * Encodes keypair data to PEM format.
     *
     * @param  array  $keypair The keypair info.
     * @return string          The data to decode.
     * @throws \Exception
     */
    public function encodePEM($keypair)
    {
        if (false === isset($keypair)    ||
            false === is_array($keypair) ||
            strlen($keypair[0]) < 64     ||
            strlen($keypair[1]) < 128)
        {
            throw new \Exception('Invalid or corrupt secp256k1 keypair provided.  Cannot encode the keys to PEM format.  Value checked was "' . var_export($keypair, true) . '".');
        }

        $beg_ec_text = '-----BEGIN EC PRIVATE KEY-----';
        $end_ec_text = '-----END EC PRIVATE KEY-----';

        $digits = $this->GenBytes();

        $ecpemstruct = array(
                             'sequence_beg' => '30',
                             'total_len'    => '74',
                             'int_sec_beg'  => '02',
                             'int_sec_len'  => '01',
                             'int_sec_val'  => '01',
                             'oct_sec_beg'  => '04',
                             'oct_sec_len'  => '20',
                             'oct_sec_val'  => $keypair[0],
                             'a0_ele_beg'   => 'a0',
                             'a0_ele_len'   => '07',
                             'obj_id_beg'   => '06',
                             'obj_id_len'   => '05',
                             'obj_id_val'   => '2b8104000a',
                             'a1_ele_beg'   => 'a1',
                             'a1_ele_len'   => '44',
                             'bit_str_beg'  => '03',
                             'bit_str_len'  => '42',
                             'bit_str_val'  => '00' . $keypair[1],
                             );

        $dec = trim(implode($ecpemstruct));

        if (strlen($dec) < 220) {
            throw new \Exception('Invalid or corrupt secp256k1 keypair provided.  Cannot encode the supplied data.  Value checked was "' . var_export($dec, true) . '".');
        }

        $dec = $this->decodeHex('0x' . $dec);

        $byte = '';

        while ($this->Compare($dec, '0') > 0) {
            $dv   = $this->Divide($dec, '256');
            $rem  = $this->Modulo($dec, '256');
            $dec  = $dv;
            $byte = $byte . $digits[$rem];
        }

        $byte = $beg_ec_text . "\r\n" . chunk_split(base64_encode(strrev($byte)), 64) . $end_ec_text;

        return $byte;
    }

    /**
     * Decodes PEM data to retrieve the keypair.
     *
     * @param  string $pem_data The data to decode.
     * @return array            The keypair info.
     * @throws \Exception
     */
    public function decodePEM($pem_data)
    {
        $beg_ec_text = '-----BEGIN EC PRIVATE KEY-----';
        $end_ec_text = '-----END EC PRIVATE KEY-----';

        $pem_data = str_ireplace($beg_ec_text, '', $pem_data);
        $pem_data = str_ireplace($end_ec_text, '', $pem_data);
        $pem_data = str_ireplace("\r", '', trim($pem_data));
        $pem_data = str_ireplace("\n", '', trim($pem_data));
        $pem_data = str_ireplace(' ', '', trim($pem_data));

        $decoded = bin2hex(base64_decode($pem_data));

        if (strlen($decoded) < 220) {
            throw new \Exception('Invalid or corrupt secp256k1 key provided. Cannot decode the supplied PEM data. Length < 230.  Value received was "' . var_export($pem_data, true) . '" which decoded into "' . var_export($decoded, true) . '".');
        }

        $ecpemstruct = array(
                             'oct_sec_val'  => substr($decoded, 14, 64),
                             'obj_id_val'   => substr($decoded, 86, 10),
                             'bit_str_val'  => substr($decoded, 106),
                            );

        if ($ecpemstruct['obj_id_val'] != '2b8104000a') {
            throw new \Exception('Invalid or corrupt secp256k1 key provided. Cannot decode the supplied PEM data. OID is not for EC key.  Value checked was "' . var_export($ecpemstruct['obj_id_val'], true) . '".');
        }

        $private_key = $ecpemstruct['oct_sec_val'];
        $public_key  = '04' . $ecpemstruct['bit_str_val'];

        if (strlen($private_key) < 64 || strlen($public_key) < 128) {
            throw new \Exception('Invalid or corrupt secp256k1 key provided. Cannot decode the supplied PEM data. Key lengths too short.  Values checked were "' . var_export($private_key, true) . '" and "' . var_export($public_key, true) . '".');
        }

        return array(
                     'private_key' => $private_key,
                     'public_key'  => $public_key
                    );
    }
}
