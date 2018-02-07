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
 * This class generates Service Identification Numbers (SINs) based on the
 * Identity Protocol v1 spec, @see: https://en.bitcoin.it/wiki/Identity_protocol_v1
 *
 * @author Rich Morgan <rich@richmorgan.me>
 */
final class Sin
{
    use Math;

    /**
     * @var string
     */
    public $encoded;

    /**
     * @var array
     */
    private $rawHashes;

    /**
     * @var string
     */
    private $SINtype;

    /**
     * @var string
     */
    private $SINversion;

    /**
     * Public constructor method.
     *
     * @param  string $pubkey
     * @param  string $type
     * @param  string $version
     */
    public function __construct($pubkey = '', $type = '02', $version = '0F')
    {
        $this->rawHashes = array(
                                 'step1' => null,
                                 'step2' => null,
                                 'step3' => null,
                                 'step4' => null,
                                 'step5' => null,
                                 'step6' => null,
                                );

        $this->encoded = '';

        /*
         * Type-2 (ephemeral) SINs may be generated at any
         * time, without network activity, much like bitcoin
         * addresses.
         */
        $this->SINtype    = $type;
        $this->SINversion = $version;

        if (empty($pubkey) === false) {
            $this->Generate($pubkey);
        }
    }

    /**
     * Returns the generated SIN, if exists.
     *
     * @return string The generated SIN.
     */
    public function __toString()
    {
        return $this->encoded;
    }

    /**
     * Returns the raw hash value array, if exists.
     *
     * @return array
     */
    public function getRawHashes()
    {
        return $this->rawHashes;
    }

    /**
     * Generates the SIN from the given public key.
     *
     * @param  string $pubkey The public key to encode.
     * @return string         The encoded SIN string.
     * @throws \Exception
     */
    public function Generate($pubkey)
    {
        if (empty($pubkey) === true) {
            throw new \Exception('Missing or invalid public key parameter for Sin::Generate.');
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

        if (empty($this->encoded) === true) {
            throw new \Exception('Failed to generate valid SIN value. Empty or NULL value was obtained.');
        }

        return $this->encoded;
    }
}
