<?php
namespace GameX\Core\Forms;

use \Twig_Extension;
use \Twig_SimpleFunction;
use \Twig_Environment;

// TODO: Safe printed vars
class FormExtension extends Twig_Extension {
    public function getFunctions() {
        return [
            new Twig_SimpleFunction(
                'form_input',
                [$this, 'renderInput'],
                ['is_safe' => ['html'], 'needs_environment' => true]
            ),
            new Twig_SimpleFunction(
                'form_label',
                [$this, 'renderLabel'],
                ['is_safe' => ['html'], 'needs_environment' => true]
            ),
            new Twig_SimpleFunction(
                'form_error',
                [$this, 'renderError'],
                ['is_safe' => ['html'], 'needs_environment' => true]
            ),
        ];
    }

    public function renderInput(Twig_Environment $environment, Form $form, $name) {
        $classes = $form->getFieldData($name, 'classes');
        if ($form->getError($name)) {
            $classes[] = 'invalid';
        }

        $attrs = $form->getFieldData($name, 'attributes');
        $attributes = [];
        foreach($attrs as $key => $value) {
            $attributes[] = sprintf('%s="%s"', $key, $value);
        }

        return sprintf(
            '<input type="%s" id="%s" name="%s" class="%s" value="%s" %s %s />',
            $form->getFieldData($name, 'type'),
            $form->getFieldData($name, 'id'),
            $form->getFieldData($name, 'name'),
            implode(' ', $classes),
            $form->getValue($name),
            $form->getFieldData($name, 'required') ? ' required' : '',
            implode(' ', $attributes)
        );
    }

    public function renderLabel(Twig_Environment $environment, Form $form, $name) {
        return sprintf(
            '<label for="%s">%s</label>',
            $form->getFieldData($name, 'id'),
            $form->getFieldData($name, 'title')
        );
    }

    public function renderError(Twig_Environment $environment, Form $form, $name) {
        $error = $form->getError($name);
        return ($error !== null)
            ? '<span class="helper-text red-text">' . $error  . '</span>'
            : '';
    }
}
