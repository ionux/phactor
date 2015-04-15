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

class PointTest extends \PHPUnit_Framework_TestCase
{
    protected $a;
    protected $b;

    public function setUp()
    {
        // Two randomly generated numers for our math functions.
        $this->a = '957620976190666461915977492034526193591830013034186215918313385644855166379351262190562120407134214207526691350895955';
        $this->b = '157663433277362577011687738037596860467660792611897958941147717548714000615442450180007858983049321228191510557052';
    }

    public function testGenerateNewPointUsingMladder()
    {
        // Verify the default new point generation using the mladder function returns a valid point.
        // array('random_number' => $random_number, 'R' => $R, 'Rx_hex' => $Rx_hex, 'Ry_hex' => $Ry_hex);

        $mock = $this->getMockForTrait('\Phactor\Point');
        $newpoint = $mock->GenerateNewPoint();

        $this->assertNotNull($newpoint);
        $this->assertEquals(count($newpoint), 4);
    }

    public function testGenerateNewPointUsingDaA()
    {
        // Verify the default new point generation using the double-and-add function returns a valid point.
        // array('random_number' => $random_number, 'R' => $R, 'Rx_hex' => $Rx_hex, 'Ry_hex' => $Ry_hex);

        $mock = $this->getMockForTrait('\Phactor\Point');
        $newpoint = $mock->GenerateNewPoint(false);

        $this->assertNotNull($newpoint);
        $this->assertEquals(count($newpoint), 4);
    }

    public function testSamePointsAreGenerated()
    {
        // Check that the same points are created when calling the mladder and double-and-add methods using the same params.

        $mock = $this->getMockForTrait('\Phactor\Point');
        $P = array('x' => strtolower(trim($mock->Gx)), 'y' => strtolower(trim($mock->Gy)));
        $random = '12345678';

        $ladder_point = $mock->mLadder($random, $P);
        $dandadd_point = $mock->doubleAndAdd($random, $P);

        $this->assertEquals($ladder_point, $dandadd_point);
    }

    public function testPointAdd()
    {
        // Verify we can add two different EC points.

        $mock = $this->getMockForTrait('\Phactor\Point');
        $P = array('x' => '1234', 'y' => '5678');
        $Q = array('x' => strtolower(trim($mock->Gx)), 'y' => strtolower(trim($mock->Gy)));

        $result = $mock->pointAdd($P, $Q);

        $this->assertNotNull($result);
        $this->assertGreaterThan(63, strlen($result['x']));
        $this->assertGreaterThan(63, strlen($result['y']));
    }

    public function testPointDouble()
    {
        // Verify we can double an EC point.

        $mock = $this->getMockForTrait('\Phactor\Point');
        $Q = array('x' => strtolower(trim($mock->Gx)), 'y' => strtolower(trim($mock->Gy)));

        $result = $mock->pointDouble($Q);

        $this->assertNotNull($result);
        $this->assertGreaterThan(63, strlen($result['x']));
        $this->assertGreaterThan(63, strlen($result['y']));
    }

    public function testInfPointReturnsInf()
    {
        // Verify an infinite point returns infinity when attempting to double.

        $mock = $this->getMockForTrait('\Phactor\Point');
        $Q = $mock->Inf;

        $result = $mock->pointDouble($Q);

        $this->assertNotNull($result);
        $this->assertEquals($result, $mock->Inf);
    }

    public function testInfPointReturnsQ()
    {
        // Check that the second 'Q' point is returned with the first 'P' point is infinite.

        $mock = $this->getMockForTrait('\Phactor\Point');
        $P = $mock->Inf;
        $Q = array('x' => strtolower(trim($mock->Gx)), 'y' => strtolower(trim($mock->Gy)));

        $result = $mock->pointAdd($P, $Q);

        $this->assertEquals($Q, $result);
    }

    public function testInfPointReturnsP()
    {
        // Check that the first 'P' point is returned with the second 'Q' point is infinite.

        $mock = $this->getMockForTrait('\Phactor\Point');
        $Q = $mock->Inf;
        $P = array('x' => strtolower(trim($mock->Gx)), 'y' => strtolower(trim($mock->Gy)));

        $result = $mock->pointAdd($P, $Q);

        $this->assertEquals($P, $result);
    }

    public function testSamePointsReturnDouble()
    {
        // Verify the points are doubled when the same two points are passed to pointAdd().

        $mock = $this->getMockForTrait('\Phactor\Point');
        $Q = array('x' => strtolower(trim($mock->Gx)), 'y' => strtolower(trim($mock->Gy)));
        $P = array('x' => strtolower(trim($mock->Gx)), 'y' => strtolower(trim($mock->Gy)));

        $add_result = $mock->pointAdd($P, $Q);
        $double_result = $mock->pointDouble($P);

        $this->assertEquals($add_result, $double_result);
    }

    public function testPointTest()
    {
        // Check out pointTest() function returns true for a good point.

        $mock = $this->getMockForTrait('\Phactor\Point');
        $Q = array('x' => strtolower(trim($mock->Gx)), 'y' => strtolower(trim($mock->Gy)));

        $result = $mock->pointTest($Q);

        $this->assertNotNull($result);
        $this->assertEquals(true, $result);
    }
}
