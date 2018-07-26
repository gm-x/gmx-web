<?php
namespace GameX\Core\Forms;

use \GameX\Core\Lang\Language;

interface Rule {
    
    /**
     * @param Form $form
     * @param string $key
     * @return bool
     */
    public function validate(Form $form, $key);
    
    /**
     * @param Language $language
     * @return string
     */
    public function getError(Language $language);
}
