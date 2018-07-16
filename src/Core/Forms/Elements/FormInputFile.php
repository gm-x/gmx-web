<?php
namespace GameX\Core\Forms\Elements;

class FormInputFile extends FormInput {

    /**
     * @inheritdoc
     */
    public function getType() {
        return 'file';
    }
}
