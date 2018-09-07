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
        $main = $this->config->getNode('main');
        $languages = $this->config->getNode('languages')->toArray();
		$this->form
            ->add(new Text('title', $main->get('title'), [
                'title' => $this->getTranslate('admin_preferences', 'title'),
                'required' => true,
            ]))
            ->add(new Select('language', $main->get('language'), $languages, [
                'title' => $this->getTranslate('admin_preferences', 'language'),
                'required' => true,
            ]));
		
		$this->form->getValidator()
            ->set('title', true)
            ->set('language', true, [
                new InArray(array_keys($languages))
            ]);
	}

    /**
     * @return bool
     * @throws \GameX\Core\Configuration\Exceptions\NotFoundException
     */
    protected function processForm() {
        $main = $this->config->getNode('main');
        $main->set('title', $this->form->getValue('title'));
        $main->set('language', $this->form->getValue('language'));
        $this->config->save();
        return true;
    }
}
