<?php
namespace GameX\Core\Forms;

use \Twig_Extension;
use \Twig_SimpleFunction;
use \Twig_Environment;
use \Twig_Extension_InitRuntimeInterface;

class ViewExtension extends Twig_Extension implements Twig_Extension_InitRuntimeInterface {
    /**
     * @var Twig_Environment
     */
    protected $environment;

    /**
     * @param Twig_Environment $environment
     */
    public function initRuntime(Twig_Environment $environment) {
        $this->environment = $environment;
    }

    /**
     * @return array
     */
    public function getFunctions() {
        return [
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
            new Twig_SimpleFunction(
                'form_action',
                [$this, 'renderAction']
            ),
        ];
    }

    public function renderInput(Form $form, $name) {
		switch ($form->getType($name)) {
			case 'select': {
				return $this->getSelectHTML($form, $name);
			} break;

            case 'checkbox': {
                return $this->getCheckboxHTML($form, $name);
            } break;

			default: {
				return $this->getInputHTML($form, $name);
			}
		}
    }

    public function renderLabel(Form $form, $name) {
    	if ($form->getType($name) === 'checkbox') {
    		$class = 'custom-control-label';
		} else {
    		$class = '';
		}

        return sprintf(
            '<label for="%s" class="%s">%s</label>',
			$this->getSafeId($form, $name),
			$class,
            $this->getSafeData($form, $name, 'title')
        );
    }

    public function renderError(Form $form, $name) {
        $error = $form->getError($name);
        return ($error !== null)
            ? '<span class="invalid-feedback">' . $this->getSafe($error)  . '</span>'
            : '';
    }

    public function renderAction(Form $form) {
        return $form->getAction();
    }

    protected function getInputHTML(Form $form, $name) {
		return sprintf(
			'<input type="%s" id="%s" name="%s" class="form-control %s" value="%s" %s %s />',
			$this->getSafeData($form, $name, 'type'),
			$this->getSafeId($form, $name),
			$this->getSafeData($form, $name, 'name'),
			$this->getSafe(implode(' ', $this->getInputClasses($form, $name))),
			$this->getSafe($form->getValue($name)),
			$form->getFieldData($name, 'required') ? ' required' : '',
			implode(' ', $this->getInputAttributes($form, $name))
		);
	}


    protected function getCheckboxHTML(Form $form, $name) {
		return sprintf(
			'<input type="checkbox" id="%s" name="%s" value="1" class="custom-control-input %s" %s %s %s />',
			$this->getSafeId($form, $name),
			$this->getSafeData($form, $name, 'name'),
			$this->getSafe(implode(' ', $this->getInputClasses($form, $name))),
            $form->getValue($name) ? 'checked' : '',
			$form->getFieldData($name, 'required') ? 'required' : '',
			implode(' ', $this->getInputAttributes($form, $name))
		);
	}

	protected function getSelectHTML(Form $form, $name) {
		$result = sprintf(
			'<select id="%s" name="%s" class="form-control %s" %s %s >',
			$this->getSafeId($form, $name),
			$this->getSafeData($form, $name, 'name'),
			$this->getSafe(implode(' ', $this->getInputClasses($form, $name))),
			$form->getFieldData($name, 'required') ? ' required' : '',
			implode(' ', $this->getInputAttributes($form, $name))
		);

		$options = $form->getFieldValues($name);
		$selected = $form->getValue($name);
		foreach ($options as $key => $value) {
			$result .= sprintf(
				'<option value="%s" %s>%s</option>',
				$this->getSafe($key),
				($key === $selected ? 'selected' : ''),
				$this->getSafe($value)
			);
		}

		$result .= '</select>';

		return $result;
	}

	protected function getInputClasses(Form $form, $name) {
		$classes = $form->getFieldData($name, 'classes');
		if ($form->getError($name)) {
			$classes[] = 'is-invalid';
		}

		return $classes;
	}

	protected function getInputAttributes(Form $form, $name) {
		$attributes = $form->getFieldData($name, 'attributes');
		$result = [];
		foreach($attributes as $key => $value) {
			$result[] = sprintf('%s="%s"', $this->getSafe($key), $this->getSafe($value));
		}

		return $result;
	}

	/**
	 * @param Form $form
	 * @param string $name
	 * @param string $key
	 * @return string
	 */
	protected function getSafeData(Form $form, $name, $key) {
		$value = $form->getFieldData($name, $key);
		return $value !== null ? $this->getSafe($value) : '';
	}

	/**
	 * @param string $value
	 * @return string
	 */
	protected function getSafe($value) {
		return twig_escape_filter($this->environment, $value, 'html_attr');
	}

	protected function getSafeId(Form $form, $name) {
		$value = $form->getFieldData($name, 'id');
		if ($value === null) {
			return '';
		}

		$value = strtolower(htmlentities($value));
		$value = str_replace(get_html_translation_table(), '-', $value);
		$value = str_replace(' ', '-', $value);
		$value = preg_replace('/[-]+/i', '-', $value);

		return $this->getSafe($value);
	}

	/**
	 * @param string $text
	 * @return string
	 */
	protected function replaceAll($text) {
		$text = strtolower(htmlentities($text));
		$text = str_replace(get_html_translation_table(), "-", $text);
		$text = str_replace(" ", "-", $text);
		$text = preg_replace("/[-]+/i", "-", $text);
		return $text;
	}
}
