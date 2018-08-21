<?php
namespace GameX\Core\Forms;

use \GameX\Core\Lang\Language;

interface Rule {
    
    /**
     * @param mixed $value
     * @param array $values
     * @return mixed|null
     */
    public function validate($value, array $values);
    
    /**
     * @param Language $language
     * @return string
     */
    public function getError(Language $language);
}
