<?php

use \PHPUnit\Framework\TestCase;
use \GameX\Core\Forms\Elements\Text;

class TextTest extends TestCase
{
    public function testRender() {
        $element = new Text('testName', 'testValue', [
            'id' => 'testId',
            'title' => 'testTitle',
            'description' => 'testDescription',
            'required' => true,
            'classes' => ['testClass'],
            'attributes' => ['test' => 'TestAttribute']
        ]);
        
        $this->assertEquals('text', $element->getType());
        $this->assertEquals('testName', $element->getName());
        $this->assertEquals('testValue', $element->getValue());
        $this->assertEquals('testid', $element->getId());
        $this->assertEquals('testTitle', $element->getTitle());
        $this->assertEquals('testDescription', $element->getDescription());
        $this->assertEquals(true, $element->getIsRequired());
        $this->assertEquals(['testClass'], $element->getClasses());
        $this->assertEquals(['test' => 'TestAttribute'], $element->getAttributes());
    }
}
