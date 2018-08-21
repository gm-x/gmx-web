<?php
namespace GameX\Forms\Admin\Preferences;

use \GameX\Core\BaseForm;
use \GameX\Core\Configuration\Config;
use \GameX\Core\Forms\Elements\Text;
use \GameX\Core\Forms\Elements\Select;
use \GameX\Core\Forms\Rules\Required;
use \GameX\Core\Forms\Rules\Trim;
use \GameX\Core\Forms\Rules\InArray;

class MainForm extends BaseForm {

	/**
	 * @var string
	 */
	protected $name = 'admin_preferences_main';

	/**
	 * @var Config
	 */
	protected $config;

	/**
	 * @param Config $config
	 */
	public function __construct(Config $config) {
		$this->config = $config;
	}

	/**
	 * @noreturn
	 */
	protected function createForm() {
        $language = $this->config->get('language');
        $languages = $language->get('list')->toArray();
		$this->form
            ->add(new Text('title', $this->config->get('main')->get('title'), [
                'title' => $this->getTranslate('admin_preferences', 'title'),
                'required' => true,
            ]))
            ->add(new Select('language', $language->get('default'), $languages, [
                'title' => $this->getTranslate('admin_preferences', 'language'),
                'required' => true,
            ]))
            ->addRule('title', new Trim())
            ->addRule('title', new Required())
            ->addRule('language', new Trim())
            ->addRule('language', new Required())
            ->addRule('language', new InArray(array_keys($languages)));
	}
    
    /**
     * @return boolean
     */
    protected function processForm() {
        $this->config->get('main')->set('title', $this->form->getValue('title'));
        $this->config->get('language')->set('default', $this->form->getValue('language'));
        $this->config->save();
        return true;
    }
}
