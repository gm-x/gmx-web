<?php
namespace GameX\Forms\Admin\Preferences;

use \GameX\Core\BaseForm;
use \GameX\Core\Configuration\Config;
use \GameX\Core\Forms\Elements\Text;
use \GameX\Core\Forms\Elements\Select;
use \GameX\Core\Forms\Rules\InArray;

class MainForm extends BaseForm {

	/**
	 * @var string
	 */
	protected $name = 'admin_preferences_main';

	/**
	 * @var Config
	 */
	protected $preferences;

	/**
	 * @param Config $preferences
	 */
	public function __construct(Config $preferences) {
		$this->preferences = $preferences;
	}

	/**
	 * @noreturn
	 */
	protected function createForm() {
        $main = $this->preferences->getNode('main');
        $languages = $this->preferences->getNode('languages')->toArray();
        $themes = $this->preferences->getNode('themes')->toArray();
		$this->form
            ->add(new Text('title', $main->get('title'), [
                'title' => $this->getTranslate('admin_preferences', 'title'),
                'required' => true,
            ]))
            ->add(new Select('language', $main->get('language'), $languages, [
                'title' => $this->getTranslate('admin_preferences', 'language'),
                'required' => true,
            ]))
            ->add(new Select('theme', $main->get('theme'), $themes, [
                'title' => $this->getTranslate('admin_preferences', 'theme'),
                'required' => true,
            ]));
		
		$this->form->getValidator()
            ->set('title', true)
            ->set('language', true, [
                new InArray(array_keys($languages))
            ])
            ->set('theme', true, [
                new InArray(array_keys($themes))
            ]);
	}

    /**
     * @return bool
     * @throws \GameX\Core\Configuration\Exceptions\NotFoundException
     */
    protected function processForm() {
        $main = $this->preferences->getNode('main');
        $main->set('title', $this->form->getValue('title'));
        $main->set('language', $this->form->getValue('language'));
        $main->set('theme', $this->form->getValue('theme'));
        $this->preferences->save();
        return true;
    }
}
