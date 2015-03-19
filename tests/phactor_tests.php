<?php

/**
 * These tests are a work in progress. If you have ideas
 * for additional or improved test cases, please submit
 * a pull request.
 */

namespace Tests;

use \Phactor\Point;
use \Phactor\Key;
use \Phactor\Signature;
use \Phactor\Sin;

class PhactorTest extends \PHPUnit_Framework_TestCase
{

    public function keyCreationTest()
    {
        $key = new Key;

        $info = $key->GenerateKeypair();

        $this->assertNotNull($info);
    }

}
