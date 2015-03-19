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
 * This class generates Service Identification Numbers (SINs) based on the
 * Identity Protocol v1 spec, see: https://en.bitcoin.it/wiki/Identity_protocol_v1
 *
 * @author Rich Morgan <rich.l.morgan@gmail.com>
 */
final class Sin
{
    use Math;

    public $encoded;

    private $math;
    private $SINtype;
    private $rawHashes;
    private $SINversion;

    /**
     * Constructor.
     */
    public function __construct($pubkey = '')
    {
        $this->rawHashes = array(
                                 'step1' => null,
                                 'step2' => null,
                                 'step3' => null,
                                 'step4' => null,
                                 'step5' => null,
                                 'step6' => null,
                                );

        $this->encoded      = '';

        /*
         * Type-2 (ephemeral) SINs may be generated at any
         * time, without network activity, much like bitcoin
         * addresses.
         */
        $this->SINtype      = '02';
        $this->SINversion   = '0F';

        if (false === empty($pubkey)) {
            return $this->Generate($pubkey);
        }
    }

    /**
     * Returns the generated SIN.
     *
     * @return string The generated SIN.
     */
    public function __toString()
    {
        return $this->encoded;
    }

    /**
     * Checks to see if a SIN exists or not.
     *
     * @return bool The existence of a SIN.
     */
    public function Exists()
    {
        return !($this->encoded == '');
    }

    /**
     * Returns the encoded SIN value, if exists.
     *
     * @return string|false
     */
    public function getEncoded()
    {
        if ($this->Exists()) {
            return $this->encoded;
        }

        return false;
    }

    /**
     * Returns the raw hash value array, if exists.
     *
     * @return string|false
     */
    public function GetRawHashes()
    {
        if ($this->Exists()) {
            return $this->rawHashes;
        }

        return false;
    }

    /**
     * Generates the SIN from the given public key.
     *
     * @param string $pubkey The public key to encode.
     * @return string        The encoded SIN string.
     * @throws \Exception
     */
    public function Generate($pubkey)
    {

        if (false === isset($pubkey) || true === empty($pubkey)) {
            throw new \Exception('Missing or invalid public key parameter.');
        }

        /* take the sha256 hash of the public key in binary form and returning binary */
        $this->rawHashes['step1'] = hash('sha256', $this->binConv($pubkey), true);

        /* take the ripemd160 hash of the sha256 hash in binary form returning hex */
        $this->rawHashes['step2'] = hash('ripemd160', $this->rawHashes['step1']);

        /* prepend the hex SINversion and hex SINtype to the hex form of the ripemd160 hash */
        $this->rawHashes['step3'] = $this->SINversion . $this->SINtype . $this->rawHashes['step2'];

        /*
         * convert the appended hex string back to binary and double sha256 hash it leaving
         * it in binary both times
         */
        $this->rawHashes['step4'] = hash('sha256', hash('sha256', $this->binConv($this->rawHashes['step3']), true), true);

        /* convert it back to hex and take the first 4 hex bytes */
        $this->rawHashes['step5'] = substr(bin2hex($this->rawHashes['step4']), 0, 8);

        /* append the first 4 bytes to the fully appended string in step 3 */
        $this->rawHashes['step6'] = $this->rawHashes['step3'] . $this->rawHashes['step5'];

        /* finally base58 encode it */
        $this->encoded = $this->encodeBase58($this->rawHashes['step6']);

        if (true === empty($this->encoded)) {
            throw new \Exception('Failed to generate valid SIN value. Empty or NULL value was obtained.');
        }

        return $this->encoded;
    }
}
