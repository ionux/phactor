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

class SinTest extends \PHPUnit_Framework_TestCase
{
    protected $a;
    protected $b;

    public function setUp()
    {
        // Two randomly generated numers for our math functions.
        $this->a = '957620976190666461915977492034526193591830013034186215918313385644855166379351262190562120407134214207526691350895955';
        $this->b = '157663433277362577011687738037596860467660792611897958941147717548714000615442450180007858983049321228191510557052';
    }

    public function testSinGenerate()
    {
        // Check to see if we can actually create a SIN using the generate() function.

        $key  = new \Phactor\Key;
        $info = $key->GenerateKeypair();

        $sin   = new \Phactor\Sin;
        $sinfo = $sin->Generate($info['public_key_compressed']);
        $sinfo_len = strlen($sinfo);
        $sample_sin = 'Tf61EPoJDSjbp6tGoyjbTKq7XLABPVcyUwY';
        $sample_sin_len = strlen($sample_sin);

        $this->assertNotNull($sinfo);
        $this->assertGreaterThan($sample_sin-1, $sinfo_len);
        $this->assertEquals(substr($sinfo,0,1), 'T');
    }

    public function testSinOnObjectCreation()
    {
        // Check to see if we can actually create a SIN when we instantiate the sin object.

        $key  = new \Phactor\Key;
        $info = $key->GenerateKeypair();

        $sin   = new \Phactor\Sin($info['public_key_compressed']);

        $sinfo_len = strlen($sin);
        $sample_sin = 'Tf61EPoJDSjbp6tGoyjbTKq7XLABPVcyUwY';
        $sample_sin_len = strlen($sample_sin);

        $this->assertNotNull($sin);
        $this->assertGreaterThan($sample_sin-1, $sinfo_len);
        $this->assertEquals(substr($sin,0,1), 'T');
    }

    public function testSinGetRawHashes()
    {
        // Check to see if we can actually create a SIN and get the raw hash array.

        $key  = new \Phactor\Key;
        $info = $key->GenerateKeypair();

        $sin   = new \Phactor\Sin;
        $sinfo = $sin->Generate($info['public_key_compressed']);
        $hashes = $sin->getRawHashes();

        $this->assertNotNull($hashes);
        $this->assertEquals(count($hashes), 6);
    }
}
