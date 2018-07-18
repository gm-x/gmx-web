<?php
namespace GameX\Core\Forms\Rules;

use \GameX\Core\Forms\Form;
use \GameX\Core\Forms\Element;
use \Psr\Http\Message\UploadedFileInterface;

class File extends BaseRule {
    
    /**
     * @param Form $form
     * @param Element $element
     * @return bool
     */
    protected function isValid(Form $form, Element $element) {
        /** @var UploadedFileInterface|null $file */
        $file = $element->getValue();
        if ($file === null) {
            return true;
        }
        
        return true;
    }
    
    /**
     * @return array
     */
    public function getMessageKey() {
        return ['file'];
    }
}
