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
use \Phactor\GMP;
use \Phactor\BC;

class SignatureTest extends \PHPUnit_Framework_TestCase
{
    protected $a;
    protected $b;

    public function setUp()
    {
        // Two randomly generated numers for our math functions.
        $this->a = '957620976190666461915977492034526193591830013034186215918313385644855166379351262190562120407134214207526691350895955';
        $this->b = '157663433277362577011687738037596860467660792611897958941147717548714000615442450180007858983049321228191510557052';
    }

    public function testSignatureGenerate()
    {
        // Check to see if we can actually create a signature with the generate method.

        $key  = new \Phactor\Key;
        $info = $key->GenerateKeypair();

        $sig   = new \Phactor\Signature;
        $sigfo = $sig->generate('my message to sign...', $info['private_key_hex']);

        $this->assertNotNull($sigfo);
    }

    public function testSignatureOnObjectCreation()
    {
        // Check to see if we can actually create a signature when we instantiate the signature object.

        $key  = new \Phactor\Key;
        $info = $key->GenerateKeypair();

        $sig   = new \Phactor\Signature('my message to sign...', '0x' . $info['private_key_hex']);
        $sigfo = $sig->encoded_signature;

        $this->assertNotNull($sigfo);
    }

    public function testSignatureVerification()
    {
        // Check to see if a signature we generate is valid.

        $key  = new \Phactor\Key;
        $info = $key->GenerateKeypair();

        $sig   = new \Phactor\Signature;
        $sigfo = $sig->generate('my message to sign...', '0x' . $info['private_key_hex']);

        $result = $sig->Verify($sigfo, 'my message to sign...', $info['public_key']);

        $this->assertTrue($result);
    }

    public function testSignatureLength()
    {
        // Check to see if a signature we generate has the correct length.

        $key  = new \Phactor\Key;
        $info = $key->GenerateKeypair();

        $sig   = new \Phactor\Signature;
        $sigfo = $sig->generate('my message to sign...', '0x' . $info['private_key_hex']);

        $this->assertGreaterThan(139, strlen($sigfo));
    }

    public function testSignatureEncode()
    {
        // Test the encoding method for the expected output.
        $expected = '304502203870f3c946c177b03745571aa71fa487639d0008289d5f43e04b3a71aa7db454022100fb53e6212026736118e311a11862683dedfcde2ef3d44b090eae23048e3e2b8a';

        $r_coord = '3870f3c946c177b03745571aa71fa487639d0008289d5f43e04b3a71aa7db454';
        $s_coord = 'fb53e6212026736118e311a11862683dedfcde2ef3d44b090eae23048e3e2b8a';

        $sig = new \Phactor\Signature;
        $result = $sig->Encode($r_coord, $s_coord);

        $this->assertEquals($expected, $result);
    }
}
