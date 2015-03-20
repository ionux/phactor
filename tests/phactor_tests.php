<?php

/**
 * These tests are a work in progress. If you have ideas
 * for additional or improved test cases, please submit
 * a pull request.
 *
 * Thanks,
 * Rich <rich@bitpay.com>
 */

namespace Tests;

use \Phactor\Point;
use \Phactor\Key;
use \Phactor\Signature;
use \Phactor\Sin;

class PhactorTest extends \PHPUnit_Framework_TestCase
{

    public function testKeyCreation()
    {
        $key  = new Key;
        $info = $key->GenerateKeypair();

        $this->assertNotNull($info);
    }

    public function testSinCreation()
    {
        $key  = new Key;
        $info = $key->GenerateKeypair();

        $sin   = new \Phactor\Sin;
        $sinfo = $sin->Generate($info['public_key_compressed']);

        $this->assertNotNull($sinfo);
    }

    public function testSignatureCreation()
    {
        $key  = new Key;
        $info = $key->GenerateKeypair();

        $sig   = new \Phactor\Signature;
        $sigfo = $sig->generate('my message to sign...', $info['private_key_hex']);
  
        $this->assertNotNull($sigfo);
    }
}
