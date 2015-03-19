<?php
/******************************************************************************
 * This file is part of the Phactor PHP project. You can always find the latest
 * version of this class and project at: https://github.com/ionux/phactor
 *
 * Copyright (c) 2015 Rich Morgan, rich.l.morgan@gmail.com
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, write to the Free Software Foundation, Inc., 51 Franklin
 * Street, Fifth Floor, Boston, MA 02110-1301 USA.
 ******************************************************************************/

namespace Phactor;

/**
 * Class for working with asymmetric keypairs.
 *
 * @author Rich Morgan <rich.l.morgan@gmail.com>
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
