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

class KeyTest extends \PHPUnit_Framework_TestCase
{
    protected $a;
    protected $b;

    public function setUp()
    {
        // Two randomly generated numers for our math functions.
        $this->a = '957620976190666461915977492034526193591830013034186215918313385644855166379351262190562120407134214207526691350895955';
        $this->b = '157663433277362577011687738037596860467660792611897958941147717548714000615442450180007858983049321228191510557052';
    }

    public function testKeyCreation()
    {
        // Check to see if we can actually create a keypair.

        $key  = new \Phactor\Key;
        $info = $key->GenerateKeypair();

        $this->assertNotNull($info);

        $this->assertNotNull($info['private_key_hex']);
        $this->assertNotNull($info['private_key_dec']);
        $this->assertNotNull($info['public_key']);
        $this->assertNotNull($info['public_key_compressed']);
        $this->assertNotNull($info['public_key_x']);
        $this->assertNotNull($info['public_key_y']);
    }

    public function testAssignPreviousKeyInfo()
    {
        // Check to see if we can assign/retrieve previously generated key values to the object.

        $previous_key_info = array(
                                  'private_key_hex'       => '7a4fbece43963538cb8f9149b094906168d71be36cfb405e6930fddb42da2c7d',
                                  'private_key_dec'       => '55323065337948610870652254548527896513063178460294714145329611159009536650365',
                                  'public_key'            => '043fbbf44c3da3fec12bf7bac254fd176adc3eaed79470932b574d8d60728eb206fb7ac7ac6959f75a6859a1a8d745db7e825a3c5c826e5b2e4950892b35772313',
                                  'public_key_compressed' => '033fbbf44c3da3fec12bf7bac254fd176adc3eaed79470932b574d8d60728eb206',
                                  'public_key_x'          => '3fbbf44c3da3fec12bf7bac254fd176adc3eaed79470932b574d8d60728eb206',
                                  'public_key_y'          => 'fb7ac7ac6959f75a6859a1a8d745db7e825a3c5c826e5b2e4950892b35772313',
                                  );

        $key  = new \Phactor\Key($previous_key_info);
        $info = $key->getKeypairInfo();

        $this->assertNotNull($key);
        $this->assertNotNull($info);

        $this->assertEquals($info['private_key_hex'], $previous_key_info['private_key_hex']);
        $this->assertEquals($info['private_key_dec'], $previous_key_info['private_key_dec']);
        $this->assertEquals($info['public_key'], $previous_key_info['public_key']);
        $this->assertEquals($info['public_key_compressed'], $previous_key_info['public_key_compressed']);
        $this->assertEquals($info['public_key_x'], $previous_key_info['public_key_x']);
        $this->assertEquals($info['public_key_y'], $previous_key_info['public_key_y']);
    }

    public function testKeypairEncodingToPEM()
    {
        // Verify the encodePEM function returns expected value.
        $expected = '-----BEGIN EC PRIVATE KEY-----MHQCAQEEIIL0KMbmnO4ldiZFIq9C67AHP/MgGGYeuKYlOQqpi1BMoAcGBSuBBAAKoUQDQgDCoVua6L+/l6Ss1sUilghRSy6+HyFHxyTxdAL86s5q2p/N+RWKVtg1ItlgytA16iEWr8PleZ59Yw5yHXtzI7KR-----END EC PRIVATE KEY-----';

        $priv_key = '82f428c6e69cee2576264522af42ebb0073ff32018661eb8a625390aa98b504c';
        $pub_key  = 'c2a15b9ae8bfbf97a4acd6c5229608514b2ebe1f2147c724f17402fceace6ada9fcdf9158a56d83522d960cad035ea2116afc3e5799e7d630e721d7b7323b291';

        $key  = new \Phactor\Key;
        $pem_data = $key->encodePEM(array($priv_key, $pub_key));

        $this->assertNotFalse(stripos($pem_data, 'MHQCAQEEIIL0KMbmnO4ldiZFIq9C67AHP'));
        $this->assertNotFalse(stripos($pem_data, 'HyFHxyTxdAL86s5q2p'));
        $this->assertNotFalse(stripos($pem_data, 'ytA16iEWr8PleZ59Yw5yHXtzI7KR'));
        $this->assertNotFalse(stripos($pem_data, '-----BEGIN EC PRIVATE KEY-----'));
    }

    public function testKeypairDecodingFromPEM()
    {
        // Verify the decodePEM function returns expected value.

        $pem_data = '-----BEGIN EC PRIVATE KEY-----MHQCAQEEIIL0KMbmnO4ldiZFIq9C67AHP/MgGGYeuKYlOQqpi1BMoAcGBSuBBAAKoUQDQgDCoVua6L+/l6Ss1sUilghRSy6+HyFHxyTxdAL86s5q2p/N+RWKVtg1ItlgytA16iEWr8PleZ59Yw5yHXtzI7KR-----END EC PRIVATE KEY-----';

        $priv_key = '82f428c6e69cee2576264522af42ebb0073ff32018661eb8a625390aa98b504c';
        $pub_key  = '04c2a15b9ae8bfbf97a4acd6c5229608514b2ebe1f2147c724f17402fceace6ada9fcdf9158a56d83522d960cad035ea2116afc3e5799e7d630e721d7b7323b291';

        $key  = new \Phactor\Key;
        $keypair = $key->decodePEM($pem_data);

        $this->assertEquals($keypair['private_key'], $priv_key);
        $this->assertEquals($keypair['public_key'], $pub_key);
    }

    public function testGetKeypairInfo()
    {
        // Test that the getKeypairInfo function returns the same values we got originally.

        $key  = new Key;
        $info = $key->GenerateKeypair();
        $retrieved = $key->getKeypairInfo();

        $this->assertEquals($info['private_key_hex'], $retrieved['private_key_hex']);
    }

    public function testGetPublicKey()
    {
        // Verify the getPublicKey() function returns the same compressed & uncompressed values.

        $key  = new \Phactor\Key;
        $info = $key->GenerateKeypair();
        $compressed = $key->getPublicKey();
        $uncompressed = $key->getPublicKey('uncompressed');

        $this->assertEquals($info['public_key_compressed'], $compressed);
        $this->assertEquals($info['public_key'], $uncompressed);
    }

    public function testGetPrivateKey()
    {
        // Verify the getPrivateKey() function returns the same compressed & uncompressed values.

        $key  = new \Phactor\Key;
        $info = $key->GenerateKeypair();
        $priv_hex = $key->getPrivateKey();
        $priv_dec = $key->getPrivateKey(false);

        $this->assertEquals($info['private_key_hex'], $priv_hex);
        $this->assertEquals($info['private_key_dec'], $priv_dec);
    }
}
