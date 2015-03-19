<?php

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
