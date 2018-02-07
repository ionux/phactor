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
 * This is the class encapsulating Bitcoin wallet properties and methods.
 *
 * @author Rich Morgan <rich@richmorgan.me>
 */
final class Wallet
{
    use Math;

    /**
     * @var string
     */
    private $WIF_address;

    /**
     * @var string
     */
    private $private_key;

    /**
     * @var string
     */
    private $network_type;

    /**
     * @var string
     */
    private $compressed_pubkey_format;

    /**
     * @var string
     */
    private $checksum;

    /**
     * Public constructor method.
     *
     * @param  string $private_key
     */
    public function __construct($private_key = null)
    {
        if (empty($private_key) === false) {
            $this->generateWIFFromConstructor($private_key);
        }
    }

    /**
     * Returns the WIF-encoded private key information array in JSON.
     *
     * @return string
     */
    public function __toString()
    {
        return json_encode(array(
                                 'WIF_address'       => $this->WIF_address,
                                 'private_key'       => $this->private_key,
                                 'network_type'      => $this->network_type,
                                 'compressed_pubkey' => $this->compressed_pubkey_format,
                                 'checksum'          => $this->checksum)
                                 );
    }

    /**
     * Retrieves or generates a WIF-encoded private key from hex.
     *
     * @param  string  $private_key        The hex-formatted private key.
     * @param  string  $network            Network type (test or main).
     * @param  string  $public_key_format  Format of the corresponding public key.
     * @return string  $WIF_address        The Base58-encoded private key.
     */
    public function getWIF($private_key = null, $network = 'main', $public_key_format = 'compressed')
    {
        if (empty($private_key) === true) {
            return ($this->WIF_address != '') ? $this->WIF_address : '';
        } else {
            return $this->encodeWIF($private_key, $network, $public_key_format);
        }
    }

    /**
     * Retrieves or generates a hex encoded private key from WIF.
     *
     * @param  string  $WIF_address The WIF-encoded private key.
     * @return string               The private key in hex format.
     */
    public function getWIFPrivateKey($WIF_address = null)
    {
        if (empty($WIF_address) === true) {
            return ($this->private_key != '') ? $this->private_key : '';
        } else {
            return $this->decodeWIF($WIF_address);
        }
    }

    /**
     * Retrieves or generates the WIF-encoded private key's checksum in hex.
     * If you also need the raw hex data hashed to generate the checksum,
     * set the $needs_hashed parameter to true. This function can also use
     * the private key in plain hex format.
     *
     * @param  string  $private_key   The private key to analyze.
     * @param  string  $needs_hashed  Whether or not to hash the data.
     * @return string                 The 4-byte checksum in hex format.
     */
    public function getChecksum($private_key = null, $needs_hashed = false)
    {
        $private_key = ($this->testIfWIF($private_key) === true) ? $this->decodeWIF($private_key) : $private_key;

        if (empty($private_key) === true) {
            return ($this->checksum != '') ? $this->checksum : '';
        } else {
            return $this->calculateChecksum($private_key, true);
        }
    }

    /**
     * Retrieves or generates the corresponding public key's format.
     *
     * @param  string  $private_key  The private key to analyze.
     * @return string                The public key's format.
     */
    public function getPubkeyFormat($private_key = null)
    {
        $private_key = ($this->testIfWIF($private_key) === true) ? $this->decodeWIF($private_key) : $private_key;

        if (empty($private_key) === true) {
            return ($this->compressed_pubkey_format != '') ? $this->compressed_pubkey_format : '';
        } else {
            return $this->calculatePubKeyFormat($private_key);
        }
    }

    /**
     * Retrieves or generates the WIF-encoded private key's network type.
     *
     * @param  string  $private_key  The private key to analyze.
     * @return string                The address network type.
     */
    public function getNetworkType($private_key = null)
    {
        $private_key = ($this->testIfWIF($private_key) === true) ? $this->decodeWIF($private_key) : $private_key;
 
        if (empty($private_key) === true) {
            return ($this->network_type != '') ? $this->network_type : '';
        } else {
            return $this->calculateNetworkType($private_key);
        }
    }

    /**
     * Tests a data string to determine if it's a WIF-encoded private key.
     *
     * @param  string   $data  The data to analyze.
     * @return boolean         The result of the check.
     */
    public function testIfWIF($data)
    {
        $data = trim($data);

        /* First, check if b58 chars are present */
        if (!$this->b58Test($data)) {
            return false;
        }

        $length = strlen($data);
        $prefix = substr($data, 0, 1);
        
        switch ($prefix) {
            /*
             * Private key (WIF, uncompressed pubkey) = 5 (Base58), 80 (hex), 51 chars long
             * ex: 5K17MHXBDYrSjsU6tDyqT3nsPhnunmpxGwiNQ2RQdZCsa8nTMfM
             */
            case '5':
                return ($length == 51) ? true : false;

            /*
             * Private key (WIF, compressed pubkey) = K or L (Base58), 80 (hex), 52 chars long
             * ex: L2TV2XvC6PbEcDkcy6HrqyKTRD3e12reyNVbSdGjJM9QDDX93TdD
             */
            case 'K':
            case 'L':
                return ($length == 52) ? true : false;

            /*
             * Testnet Private key (WIF, uncompressed pubkey) = 9 (Base58), ef (hex), 51 chars long
             * ex: 92mjw2LiomvahvyPWZskKeLq3N9cwwN9ctaKUemuyHwvMFmGfiJ
             */
            case '9':
                return ($length == 51) ? true : false;

            /*
             * Testnet Private key (WIF, compressed pubkey) = c (Base58), ef (hex), 52 chars long
             * ex: cSpUVSv3XTHVmfDtMW6zDHpX3SM3fUxM3Qe4Z3jEoToQTxbZhmCT
             */
            case 'c':
                return ($length == 52) ? true : false;
            
            default:
                return false;
        }
    }

    /**
     * WIF-encodes a hex-formatted private key from the constructor.
     *
     * @param  string  $private_key  The private key in hex.
     */
    private function generateWIFFromConstructor($private_key)
    {
        $this->encodeWIF($private_key);
    }

    /**
     * Retrieves or generates the WIF-encoded private key's network type.
     *
     * @param  string  $data          The data to analyze.
     * @return string  $network_type  The address network type.
     */
    private function calculateNetworkType($data)
    {
        $temp = strtolower(substr(trim($data), 0, 2));

        switch ($temp) {
            case '80':
                $this->network_type = 'main';
                break;
            case 'ef':
                $this->network_type = 'test';
                break;
            default:
                $this->network_type = 'unknown';
                break;
        }

        return $this->network_type;
    }

    /**
     * Retrieves or generates the corresponding public key's format.
     *
     * @param  string  $data                      The data to analyze.
     * @return string  $compressed_pubkey_format  The public key's format.
     */
    private function calculatePubkeyFormat($data)
    {
        $temp = strtolower(substr(trim($data), -2));

        switch ($temp) {
            case '01':
                $this->compressed_pubkey_format = 'compressed';
                break;
            default:
                $this->compressed_pubkey_format = 'uncompressed';
                break;
        }

        return $this->compressed_pubkey_format;
    }

    /**
     * Retrieves or generates the WIF-encoded private key's checksum in hex.
     * If you also need the raw hex data hashed to generate the checksum,
     * set the $needs_hashed parameter to true.
     *
     * @param  string   $data          The data to checksum.
     * @param  boolean  $needs_hashed  Whether or not to hash the data.
     * @return string   $checksum      The 4-byte checksum in hex format.
     */
    private function calculateChecksum($data, $needs_hashed = false)
    {
        $data = $this->stripHexPrefix(trim($data));

        if ($needs_hashed === false) {
            $this->checksum = substr($data, 0, 8);
        } else {
            $this->checksum = substr(hash('sha256', hash('sha256', $this->binConv($data), true)), 0, 8);
        }

        return $this->checksum;
    }

    /**
     * Encodes a hex-formatted private key into Wallet Import Format (WIF).
     * @see: https://bitcoin.org/en/developer-guide#wallet-import-format-wif
     *
     * @param  string  $private_key        The hex-formatted private key.
     * @param  string  $network            Network type (test or main).
     * @param  string  $public_key_format  Format of the corresponding public key.
     * @return string  $WIF_address        The Base58-encoded private key.
     */
    private function encodeWIF($private_key, $network = 'main', $public_key_format = 'compressed')
    {
        /*
         * WIF uses base58Check encoding on an private key much like standard Bitcoin addresses.
         *
         * 1. Take a private key.
         */
        $step1 = $this->stripHexPrefix(trim($private_key));
        
        /*
         * 2. Add a 0x80 byte in front of it for mainnet addresses or 0xef for testnet addresses.
         */
        $step2 = ($network == 'main') ? '80' . $step1 : 'ef' . $step1;

        /*
         * 3. Append a 0x01 byte after it if it should be used with compressed public keys. Nothing is appended
         *    if it is used with uncompressed public keys.
         */
        $step3 = ($public_key_format == 'compressed') ? $step2 . '01' : $step2;

        /*
         * 4. Perform a SHA-256 hash on the extended key.
         */
        $step4 = hash('sha256', $this->binConv($step3), true);

        /*
         * 5. Perform a SHA-256 hash on result of SHA-256 hash.
         */
        $step5 = hash('sha256', $step4);

        /*
         * 6. Take the first four bytes of the second SHA-256 hash; this is the checksum.
         */
        $this->checksum = substr($step5, 0, 8);

        /*
         * 7. Add the four checksum bytes from step 6 at the end of the extended key from step 3.
         */
        $step7 = $step3 . $this->checksum;

        /*
         * 8. Convert the result from a byte string into a Base58 string using Base58Check encoding.
         */
        $this->WIF_address = $this->encodeBase58($step7);

        /*
         *  The process is easily reversible, using the Base58 decoding function, and removing the padding.
         */
        return $this->WIF_address;

    }

    /**
     * Decodes a WIF-encoded private key using Base58 decoding and removes the padding.
     *
     * @param  string  $WIF_encoded_key The wallet address to decode.
     * @return string  $private_key     The decoded private key in hex format.
     * @throws \Exception
     */
    private function decodeWIF($WIF_encoded_key)
    {
        /* Using the Base58 decoding function and remove the padding. */
        $decoded_key = $this->stripHexPrefix($this->decodeBase58(trim($WIF_encoded_key)));

        list($private_key, $checksum_provided) = array(
                                                       substr($decoded_key, 0, -8), 
                                                       substr($decoded_key, strlen($decoded_key) - 8)
                                                       );

        $private_key_type = substr($private_key, 0, 2);

        if ($private_key_type != '80' && $private_key_type != 'ef') {
            throw new \Exception('Invalid WIF encoded private key! Network type was not present in value provided. Checked ' . $private_key . ' and found ' . $private_key_type);
        }

        $private_key           = substr($private_key, 2);
        $compressed_public_key = (substr($private_key, strlen($private_key) - 2) == '01') ? '01' : '';
        $private_key           = ($compressed_public_key == '01') ? substr($private_key, 0, -2) : $private_key;

        /* Now let's check our private key against the checksum provided. */
        $new_checksum = substr(hash('sha256', hash('sha256', $this->binConv($private_key_type . $private_key . $compressed_public_key), true)), 0, 8);

        if ($new_checksum != $checksum_provided) {
            throw new \Exception('Invalid WIF encoded private key! Checksum is incorrect! Value encoded with key was: ' . $checksum_provided . ' but this does not match the recalculated value of: ' . $new_checksum . ' from the decoded provided value of: ' . $decoded_key);
        }

        return $private_key;
    }
}
