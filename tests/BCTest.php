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

class BCTest extends \PHPUnit_Framework_TestCase
{
    protected $a;
    protected $b;

    public function setUp()
    {
        // Two randomly generated numers for our math functions.
        $this->a = '957620976190666461915977492034526193591830013034186215918313385644855166379351262190562120407134214207526691350895955';
        $this->b = '157663433277362577011687738037596860467660792611897958941147717548714000615442450180007858983049321228191510557052';
    }

    public function testBcAdd()
    {
        // Test that our BC calls are returning
        // the correct result for addition.

        $bc = new \Phactor\BC;

        $expected_result = '957778639623943824492989179772563790452297673826798113877254533362403880379966704640742128266117263528754882861453007';
        $a = $this->a;
        $b = $this->b;

        $result = $bc->add($a, $b);

        $this->assertEquals($result, $expected_result);
    }

    public function testBcSub()
    {
        // Test that our BC calls are returning
        // the correct result for subtraction.

        $bc = new \Phactor\BC;

        $expected_result = '957463312757389099338965804296488596731362352241574317959372237927306452378735819740382112548151164886298499840338903';
        $a = $this->a;
        $b = $this->b;

        $result = $bc->sub($a, $b);

        $this->assertEquals($result, $expected_result);
    }

    public function testBcMul()
    {
        // Test that our BC calls are returning
        // the correct result for multiplication.

        $bc = new \Phactor\BC;

        $expected_result = '150981810884639958700328085278405718749810206599288911717839892598416430383239698022998119007526637510937085529989130930737456136347911513955480178694591753112850253972637216399933791443453571052105672003128661792601294741343524660';
        $a = $this->a;
        $b = $this->b;

        $result = $bc->mul($a, $b);

        $this->assertEquals($result, $expected_result);
    }

    public function testBcDiv()
    {
        // Test that our BC calls are returning
        // the correct result for division.

        $bc = new \Phactor\BC;

        $expected_result = '6073';
        $a = $this->a;
        $b = $this->b;

        $result = $bc->div($a, $b);

        $this->assertEquals($result, $expected_result);
    }

    public function testBcMod()
    {
        // Test that our BC calls are returning
        // the correct result for 'a' modulo 'b'.

        $bc = new \Phactor\BC;

        $expected_result = '130945897243531723997858932200459971726019502129911268723296971515040641769262247374392803075686388719647737919159';
        $a = $this->a;
        $b = $this->b;

        $result = $bc->mod($a, $b);

        $this->assertEquals($result, $expected_result);
    }

    public function testBcComp()
    {
        // Test that our BC calls are returning
        // the correct result for comparing two
        // arb precision values.

        $bc = new \Phactor\BC;

        $expected_result = '1';
        $a = $this->a;
        $b = $this->b;

        $result = $bc->comp($a, $b);

        $this->assertEquals($result, $expected_result);
    }

    public function testBcInv()
    {
        // Test that our BC calls are returning
        // the correct result for inverse modulo.

        $bc = new \Phactor\BC;

        $expected_result = '16320031509886753001468114610224267914757061072957414462029557633094547166069133482767770945598158313244622262371';
        $a = $this->a;
        $b = $this->b;

        $result = $bc->inv($a, $b);

        $this->assertEquals($result, $expected_result);
    }

    public function testBcPow()
    {
        // Test that our BC calls are returning
        // the correct result for raising a number
        // to a power.

        $bc = new \Phactor\BC;

        $expected_result = '917037934040364982737244091531827293772718769617550961534802228981137468765677724565272391346532251949140929536931667224208094060917023615329647508125531895055074287250548098811179279399871619095442945851773292458773975997781235362025';
        $a = $this->a;

        $result = $bc->power($a, '2');

        $this->assertEquals($result, $expected_result);
    }
}
