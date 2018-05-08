<?php
namespace GameX\Core\Forms;

use \Slim\Csrf\Guard;
use Twig\Environment;
use \Twig_Extension;
use \Twig_SimpleFunction;
use \Twig_Environment;
use \Twig_Extension_InitRuntimeInterface;

class FormExtension extends Twig_Extension implements Twig_Extension_InitRuntimeInterface {
// TODO: Fix problems with CSRF. Temporally disabled
//    /**
//     * @var Guard
//     */
//    protected $csrf;


    /**
     * @var Twig_Environment
     */
    protected $environment;

// TODO: Fix problems with CSRF. Temporally disabled
//    /**
//     * FormExtension constructor.
//     * @param Guard $csrf
//     */
//    public function __construct(Guard $csrf) {
//        $this->csrf = $csrf;
//    }

    /**
     * @param Twig_Environment $environment
     */
    public function initRuntime(Twig_Environment $environment) {
        $this->environment = $environment;
    }

    public function getFunctions() {
        return [
            new Twig_SimpleFunction(
                'csrf_token',
                [$this, 'renderCSRFToken'],
                ['is_safe' => ['html']]
            ),
            new Twig_SimpleFunction(
                'form_input',
                [$this, 'renderInput'],
                ['is_safe' => ['html']]
            ),
            new Twig_SimpleFunction(
                'form_label',
                [$this, 'renderLabel'],
                ['is_safe' => ['html']]
            ),
            new Twig_SimpleFunction(
                'form_error',
                [$this, 'renderError'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    public function renderCSRFToken() {
    	return '';
    	// TODO: Fix problems with CSRF. Temporally disabled
//        return sprintf(
//            '<input type="hidden" name="%s" value="%s"><input type="hidden" name="%s" value="%s">',
//            $this->csrf->getTokenNameKey(), $this->csrf->getTokenName(),
//            $this->csrf->getTokenValueKey(), $this->csrf->getTokenValue()
//        );
    }

    public function renderInput(Form $form, $name) {
        $classes = $form->getFieldData($name, 'classes');
        if ($form->getError($name)) {
            $classes[] = 'invalid';
        }

        $attrs = $form->getFieldData($name, 'attributes');
        $attributes = [];
        foreach($attrs as $key => $value) {
            $attributes[] = sprintf('%s="%s"', $this->getSafe($key), $this->getSafe($value));
        }

        return sprintf(
            '<input type="%s" id="%s" name="%s" class="%s" value="%s" %s %s />',
            $this->getSafeData($form, $name, 'type'),
            $this->getSafeData($form, $name, 'id'),
            $this->getSafeData($form, $name, 'name'),
            $this->getSafe(implode(' ', $classes)),
            $this->getSafe($form->getValue($name)),
            $form->getFieldData($name, 'required') ? ' required' : '',
            implode(' ', $attributes)
        );
    }

    public function renderLabel(Form $form, $name) {
        return sprintf(
            '<label for="%s">%s</label>',
            $this->getSafeData($form, $name, 'id'),
            $this->getSafeData($form, $name, 'title')
        );
    }

    public function renderError(Form $form, $name) {
        $error = $form->getError($name);
        return ($error !== null)
            ? '<span class="helper-text red-text">' . $this->getSafe($error)  . '</span>'
            : '';
    }

    protected function getSafeData(Form $form, $name, $key) {
        $value = $form->getFieldData($name, $key);
        return $value !== null ? $this->getSafe($value) : '';
    }

    protected function getSafe($value) {
        return twig_escape_filter($this->environment, $value, 'html_attr');
    }
}
