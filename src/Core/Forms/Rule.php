<?php
namespace GameX\Core\Forms;

use \GameX\Core\Lang\Language;

interface Rule {
    public function __construct(array $options = []);
    public function validate(Form $form, $key);
    public function getMessage(Language $language);
}
