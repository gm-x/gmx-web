<?php
use \PHPUnit\Framework\TestCase;
use \GameX\Core\Utils;

class UtilsTest extends TestCase
{
    public function testGenerateTokenLength()
    {
        $token = Utils::generateToken(32);
        $this->assertEquals(32, strlen($token));
    }
}
