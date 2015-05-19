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
     * @param  bool
     * @return string
     */
    public function getPrivateKey($hex = true)
    {
        return ($hex === true) ? $this->keyInfo['private_key_hex'] : $this->keyInfo['private_key_dec'];
    }

    /**
     * Returns the compressed or uncompressed public key value.
     *
     * @param  string
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
     * @return array  $keyInfo The complete keypair array.
     */
    public function GenerateKeypair()
    {
        $point = $this->GenerateNewPoint();

        $point['Rx_hex']        = $this->stripHexPrefix($point['Rx_hex']);
        $point['Ry_hex']        = $this->stripHexPrefix($point['Ry_hex']);
        $point['random_number'] = $this->stripHexPrefix($point['random_number']);

        $comp_prefix = ($this->Modulo($this->addHexPrefix($point['Ry_hex']), '0x02') == '1') ? '03' : '02';

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
        $this->pemInitialDataCheck($keypair);

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

        $this->pemDataLenCheck($dec);

        return '-----BEGIN EC PRIVATE KEY-----' . "\r\n" . chunk_split(base64_encode($this->binConv($dec)), 64) . '-----END EC PRIVATE KEY-----';
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
        $pem_data = $this->pemDataClean($pem_data);
        $decoded  = bin2hex(base64_decode($pem_data));

        $this->pemDataLenCheck($decoded);

        $ecpemstruct = array(
                             'oct_sec_val'  => substr($decoded, 14, 64),
                             'obj_id_val'   => substr($decoded, 86, 10),
                             'bit_str_val'  => substr($decoded, 106),
                            );

        $this->pemOidCheck($ecpemstruct['obj_id_val']);

        $private_key = $ecpemstruct['oct_sec_val'];
        $public_key  = '04' . $ecpemstruct['bit_str_val'];

        $this->pemKeyLenCheck(array($private_key, $public_key));

        return array(
                     'private_key' => $private_key,
                     'public_key'  => $public_key
                    );
    }

    /**
     * Ensures the data we want to PEM encode is acceptable.
     *
     * @param  array     $keypair The values to check.
     * @throws \Exception
     */
    private function pemInitialDataCheck($keypair)
    {
        if (true === empty($keypair) || false === is_array($keypair) || strlen($keypair[0]) < 62 || strlen($keypair[1]) < 126) {
            throw new \Exception('Invalid or corrupt secp256k1 keypair provided.  Cannot encode the keys to PEM format.  Value checked was "' . var_export($keypair, true) . '".');
        }
    }

    /**
     * Ensures the decoded PEM data length is acceptable.
     *
     * @param  string     $value The value to check.
     * @throws \Exception
     */
    private function pemDataLenCheck($value)
    {
        if (strlen($value) < 220) {
            throw new \Exception('Invalid or corrupt secp256k1 key provided. Cannot decode the supplied PEM data. Length < 220.  Value received was "' . var_export($value, true) . '".');
        }
    }

    /**
     * Ensures the decoded PEM key lengths are acceptable.
     *
     * @param  array     $values The key values to check.
     * @throws \Exception
     */
    private function pemKeyLenCheck(array $values)
    {
        if (strlen($values[0]) < 62 || strlen($values[1]) < 126) {
            throw new \Exception('Invalid or corrupt secp256k1 key provided. Cannot decode the supplied PEM data. Key lengths too short.  Values checked were "' . var_export($values[0], true) . '" and "' . var_export($values[1], true) . '".');
        }
    }

    /**
     * Ensures the decoded PEM data is for an EC Key.
     *
     * @param  string     $value The value to check.
     * @throws \Exception
     */
    private function pemOidCheck($value)
    {
        if ($value != '2b8104000a') {
            throw new \Exception('Invalid or corrupt secp256k1 key provided. Cannot decode the supplied PEM data. OID is not for EC key.  Value checked was "' . var_export($value, true) . '".');
        }
    }

    /**
     * Cleans the PEM data of unwanted data.
     *
     * @param  string  $value The value to clean.
     * @return string  $value The cleaned value.
     */
    private function pemDataClean($value)
    {
        $value = str_ireplace('-----BEGIN EC PRIVATE KEY-----', '', $value);
        $value = str_ireplace('-----END EC PRIVATE KEY-----', '', $value);
        $value = str_ireplace("\r", '', trim($value));
        $value = str_ireplace("\n", '', trim($value));
        $value = str_ireplace(' ', '', trim($value));

        return $value;
    }

    /**
     * Returns the value, if exists.
     *
     * @param  string  $value The value to check.
     * @return string  $value The value, if set.
     */
    private function keyValueCheck($value)
    {
        return (true === isset($value)) ? $value : '';
    }
}
