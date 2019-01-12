<?php

use \PHPUnit\Framework\TestCase;
use \GameX\Core\Forms\Rules\Boolean;

class BooleanTest extends TestCase
{
    public function testValidateSuccess() {
        $rule = new Boolean();
        $this->assertEquals(true, $rule->validate('1', []));
        $this->assertEquals(true, $rule->validate('true', []));
        $this->assertEquals(true, $rule->validate(1, []));
        $this->assertEquals(true, $rule->validate(true, []));
    }
    
    public function testValidateFail() {
        $rule = new Boolean();
        $this->assertEquals(false, $rule->validate('0', []));
        $this->assertEquals(false, $rule->validate('false', []));
        $this->assertEquals(false, $rule->validate(0, []));
        $this->assertEquals(false, $rule->validate(false, []));
    }
    
    public function testValidateEmpty() {
        $rule = new Boolean();
        $this->assertEquals(null, $rule->validate('', []));
    }
}
