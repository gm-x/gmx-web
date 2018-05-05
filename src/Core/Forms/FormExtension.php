<?php

namespace GameX\Core\Forms;

use Twig_Extension;
use Twig_SimpleFunction;

class FormExtension extends Twig_Extension {
    public function getFunctions() {
        return [
            new Twig_SimpleFunction('form_input', [$this, 'renderInput'], ['is_safe' => ['html']]),
            new Twig_SimpleFunction('form_label', [$this, 'renderLabel'], ['is_safe' => ['html']]),
            new Twig_SimpleFunction('form_error', [$this, 'renderError'], ['is_safe' => ['html']]),
        ];
    }

    public function renderInput(FormHelper $form, $name) {
        return $form->renderInput($name);
    }

    public function renderLabel(FormHelper $form, $name) {
        return $form->renderLabel($name);
    }

    public function renderError(FormHelper $form, $name) {
        return $form->renderError($name);
    }
}
