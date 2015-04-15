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

class PhactorTest extends \PHPUnit_Framework_TestCase
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

        $key  = new Key;
        $info = $key->GenerateKeypair();

        $this->assertNotNull($info);

        $this->assertNotNull($info['private_key_hex']);
        $this->assertNotNull($info['private_key_dec']);
        $this->assertNotNull($info['public_key']);
        $this->assertNotNull($info['public_key_compressed']);
        $this->assertNotNull($info['public_key_x']);
        $this->assertNotNull($info['public_key_y']);
    }

    public function testKeypairEncodingToPEM()
    {
        // Verify the encodePEM function returns expected value.
        $expected = '-----BEGIN EC PRIVATE KEY-----MHQCAQEEIIL0KMbmnO4ldiZFIq9C67AHP/MgGGYeuKYlOQqpi1BMoAcGBSuBBAAKoUQDQgDCoVua6L+/l6Ss1sUilghRSy6+HyFHxyTxdAL86s5q2p/N+RWKVtg1ItlgytA16iEWr8PleZ59Yw5yHXtzI7KR-----END EC PRIVATE KEY-----';

        $priv_key = '82f428c6e69cee2576264522af42ebb0073ff32018661eb8a625390aa98b504c';
        $pub_key  = 'c2a15b9ae8bfbf97a4acd6c5229608514b2ebe1f2147c724f17402fceace6ada9fcdf9158a56d83522d960cad035ea2116afc3e5799e7d630e721d7b7323b291';

        $key  = new Key;
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

        $key  = new Key;
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

        $key  = new Key;
        $info = $key->GenerateKeypair();
        $compressed = $key->getPublicKey();
        $uncompressed = $key->getPublicKey('uncompressed');

        $this->assertEquals($info['public_key_compressed'], $compressed);
        $this->assertEquals($info['public_key'], $uncompressed);
    }

    public function testGetPrivateKey()
    {
        // Verify the getPrivateKey() function returns the same compressed & uncompressed values.

        $key  = new Key;
        $info = $key->GenerateKeypair();
        $priv_hex = $key->getPrivateKey();
        $priv_dec = $key->getPrivateKey(false);

        $this->assertEquals($info['private_key_hex'], $priv_hex);
        $this->assertEquals($info['private_key_dec'], $priv_dec);
    }


    public function testSinGenerate()
    {
        // Check to see if we can actually create a SIN using the generate() function.

        $key  = new Key;
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

        $key  = new Key;
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

        $key  = new Key;
        $info = $key->GenerateKeypair();

        $sin   = new \Phactor\Sin;
        $sinfo = $sin->Generate($info['public_key_compressed']);
        $hashes = $sin->getRawHashes();

        $this->assertNotNull($hashes);
        $this->assertEquals(count($hashes), 6);
    }

    public function testSignatureGenerate()
    {
        // Check to see if we can actually create a signature with the generate method.

        $key  = new Key;
        $info = $key->GenerateKeypair();

        $sig   = new \Phactor\Signature;
        $sigfo = $sig->generate('my message to sign...', $info['private_key_hex']);

        $this->assertNotNull($sigfo);
    }

    public function testSignatureOnObjectCreation()
    {
        // Check to see if we can actually create a signature when we instantiate the signature object.

        $key  = new Key;
        $info = $key->GenerateKeypair();

        $sig   = new \Phactor\Signature('my message to sign...', $info['private_key_hex']);
        $sigfo = $sig->encoded_signature;

        $this->assertNotNull($sigfo);
    }

    public function testSignatureVerification()
    {
        // Check to see if a signature we generate is valid.

        $key  = new Key;
        $info = $key->GenerateKeypair();

        $sig   = new \Phactor\Signature;
        $sigfo = $sig->generate('my message to sign...', '0x' . $info['private_key_hex']);

        $result = $sig->Verify($sigfo, 'my message to sign...', array('x' => '0x' . $info['public_key_x'], 'y' => '0x' . $info['public_key_y']));

        $this->assertTrue($result);
    }

    public function testSignatureLength()
    {
        // Check to see if a signature we generate has the correct length.

        $key  = new Key;
        $info = $key->GenerateKeypair();

        $sig   = new \Phactor\Signature;
        $sigfo = $sig->generate('my message to sign...', $info['private_key_hex']);

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

    public function testGmpAdd()
    {
        // Test that our GMP calls are returning
        // the correct result for addition.

        $gmp = new GMP;

        $expected_result = '957778639623943824492989179772563790452297673826798113877254533362403880379966704640742128266117263528754882861453007';
        $a = $this->a;
        $b = $this->b;

        $result = $gmp->add($a, $b);

        $this->assertEquals($result, $expected_result);
    }

    public function testGmpSub()
    {
        // Test that our GMP calls are returning
        // the correct result for subtraction.

        $gmp = new GMP;

        $expected_result = '957463312757389099338965804296488596731362352241574317959372237927306452378735819740382112548151164886298499840338903';
        $a = $this->a;
        $b = $this->b;

        $result = $gmp->sub($a, $b);

        $this->assertEquals($result, $expected_result);
    }

    public function testGmpMul()
    {
        // Test that our GMP calls are returning
        // the correct result for multiplication.

        $gmp = new GMP;

        $expected_result = '150981810884639958700328085278405718749810206599288911717839892598416430383239698022998119007526637510937085529989130930737456136347911513955480178694591753112850253972637216399933791443453571052105672003128661792601294741343524660';
        $a = $this->a;
        $b = $this->b;

        $result = $gmp->mul($a, $b);

        $this->assertEquals($result, $expected_result);
    }

    public function testGmpDiv()
    {
        // Test that our GMP calls are returning
        // the correct result for division.

        $gmp = new GMP;

        $expected_result = '6073';
        $a = $this->a;
        $b = $this->b;

        $result = $gmp->div($a, $b);

        $this->assertEquals($result, $expected_result);
    }

    public function testGmpMod()
    {
        // Test that our GMP calls are returning
        // the correct result for 'a' modulo 'b'.

        $gmp = new GMP;

        $expected_result = '130945897243531723997858932200459971726019502129911268723296971515040641769262247374392803075686388719647737919159';
        $a = $this->a;
        $b = $this->b;

        $result = $gmp->mod($a, $b, false);

        $this->assertEquals($result, $expected_result);
    }

    public function testGmpComp()
    {
        // Test that our GMP calls are returning
        // the correct result for comparing two
        // arb precision values.

        $gmp = new GMP;

        $expected_result = '1';
        $a = $this->a;
        $b = $this->b;

        $result = $gmp->comp($a, $b);

        $this->assertEquals($result, $expected_result);
    }

    public function testGmpInv()
    {
        // Test that our GMP calls are returning
        // the correct result for inverse modulo.

        $gmp = new GMP;

        $expected_result = '16320031509886753001468114610224267914757061072957414462029557633094547166069133482767770945598158313244622262371';
        $a = $this->a;
        $b = $this->b;

        $result = $gmp->inv($a, $b);

        $this->assertEquals($result, $expected_result);
    }

    public function testGmpPow()
    {
        // Test that our GMP calls are returning
        // the correct result for raising a number
        // to a power.

        $gmp = new GMP;

        $expected_result = '917037934040364982737244091531827293772718769617550961534802228981137468765677724565272391346532251949140929536931667224208094060917023615329647508125531895055074287250548098811179279399871619095442945851773292458773975997781235362025';
        $a = $this->a;

        $result = $gmp->power($a, '2');

        $this->assertEquals($result, $expected_result);
    }

    public function testGmpNormalize()
    {
        // Test that our GMP calls are returning
        // the correct result for normalizing a
        // value into a string.

        $gmp = new GMP;

        $expected_result = 'string';
        $a = $this->a;

        $result = gettype($gmp->normalize($a));

        $this->assertEquals($result, $expected_result);
    }

    public function testBcAdd()
    {
        // Test that our BC calls are returning
        // the correct result for addition.

        $bc = new BC;

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

        $bc = new BC;

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

        $bc = new BC;

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

        $bc = new BC;

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

        $bc = new BC;

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

        $bc = new BC;

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

        $bc = new BC;

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

        $bc = new BC;

        $expected_result = '917037934040364982737244091531827293772718769617550961534802228981137468765677724565272391346532251949140929536931667224208094060917023615329647508125531895055074287250548098811179279399871619095442945851773292458773975997781235362025';
        $a = $this->a;

        $result = $bc->power($a, '2');

        $this->assertEquals($result, $expected_result);
    }
}
