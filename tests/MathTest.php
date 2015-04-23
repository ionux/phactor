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

class MathTest extends \PHPUnit_Framework_TestCase
{
    protected $a;
    protected $b;

    public function setUp()
    {
        // Two randomly generated numers for our math functions.
        $this->a = '957620976190666461915977492034526193591830013034186215918313385644855166379351262190562120407134214207526691350895955';
        $this->b = '157663433277362577011687738037596860467660792611897958941147717548714000615442450180007858983049321228191510557052';
    }

    public function testEncodeHex()
    {
        // Verify the encodeHex function correctly converts a known decimal value to hexadecimal.

        $decimal      = '123456789123456789';
        $expected_hex = '0x1b69b4bacd05f15';

        $mock = $this->getMockForTrait('\Phactor\Math');

        $returned_hex = $mock->encodeHex($decimal);

        $this->assertNotNull($returned_hex);
        $this->assertEquals($returned_hex, $expected_hex);
    }

    public function testDecodeHex()
    {
        // Verify the decodeHex function correctly converts a known hexadecimal value to decimal.

        $expected_dec = '123456789123456789';
        $hexadecimal  = '0x1b69b4bacd05f15';

        $mock = $this->getMockForTrait('\Phactor\Math');

        $returned_dec = $mock->decodeHex($hexadecimal);

        $this->assertNotNull($returned_dec);
        $this->assertEquals($returned_dec, $expected_dec);
    }

    public function testBaseCheck()
    {
        // Ensure the correct base digits are returned for the requested base

        $b58_chars = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
        $dec_chars = '0123456789';
        $hex_chars = '0123456789abcdef';
        $bin_chars = '01';

        $mock = $this->getMockForTrait('\Phactor\Math');

        $returned_b58 = $mock->BaseCheck('58');
        $returned_hex = $mock->BaseCheck('16');
        $returned_dec = $mock->BaseCheck('10');
        $returned_bin = $mock->BaseCheck('2');

        $this->assertEquals($returned_b58, $b58_chars);
        $this->assertEquals($returned_hex, $hex_chars);
        $this->assertEquals($returned_dec, $dec_chars);
        $this->assertEquals($returned_bin, $bin_chars);
    }

    public function testD2B()
    {
        // Verify the D2B function correctly converts a known decimal value to a binary string.

        $expected_bin = '101010001111101000001011001101011101001011011001011011011';
        $decimal  = '123456789123456789';

        $mock = $this->getMockForTrait('\Phactor\Math');

        $returned_bin = $mock->D2B($decimal);

        $this->assertNotNull($returned_bin);
        $this->assertEquals($expected_bin, $returned_bin);
    }

    public function testBinConv()
    {
        // Verify the binConv function correctly converts a known hexadecimal value to a byte string.

        $expected_bytes = chr(0x10) . chr(0x13) . chr(0x00) . chr(0xa3) . chr(0x09) . chr(0xf8) . chr(0x15) . chr(0x32);
        $hexadecimal  = '0x101300a309f81532';

        $mock = $this->getMockForTrait('\Phactor\Math');

        $returned_bytes = $mock->binConv($hexadecimal);

        $this->assertNotNull($returned_bytes);
        $this->assertEquals($returned_bytes, $expected_bytes);
    }

    public function testSecureRandomNumber()
    {
        // Verify the SecureRandomNumber function correctly a random 32-byte hexadecimal value.

        $mock = $this->getMockForTrait('\Phactor\Math');

        $random_number = $mock->SecureRandomNumber();

        $this->assertNotNull($random_number);
        $this->assertEquals(strlen($random_number), 66);
    }
}
