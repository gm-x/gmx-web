<?php

namespace GameX\Forms\Admin\Preferences;

use \GameX\Constants\PreferencesConstants;
use \GameX\Core\BaseForm;
use \GameX\Core\Configuration\Config;
use \GameX\Core\Forms\Elements\Text;
use \GameX\Core\Forms\Elements\Select;
use \GameX\Core\Forms\Elements\Checkbox;
use \GameX\Core\Forms\Validator;
use \GameX\Core\Forms\Rules\InArray;
use \GameX\Core\Forms\Rules\Boolean;

class MainForm extends BaseForm
{
    
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
    public function __construct(Config $preferences)
    {
        $this->preferences = $preferences;
    }
    
    /**
     * @noreturn
     */
    protected function createForm()
    {
        $main = $this->preferences->getNode(PreferencesConstants::CATEGORY_MAIN);
        $languages = $this->preferences->getNode('languages')->toArray();
        $themes = $this->preferences->getNode('themes')->toArray();
        $this->form->add(new Text('title', $main->get(PreferencesConstants::MAIN_TITLE), [
                'title' => $this->getTranslate('admin_preferences', 'title'),
                'required' => true,
            ]))->add(new Select('language', $main->get(PreferencesConstants::MAIN_LANGUAGE), $languages, [
                'title' => $this->getTranslate('admin_preferences', 'language'),
                'required' => true,
            ]))->add(new Select('theme', $main->get(PreferencesConstants::MAIN_THEME), $themes, [
                'title' => $this->getTranslate('admin_preferences', 'theme'),
                'required' => true,
            ]))->add(new Checkbox('auto_activate_users', $main->get(PreferencesConstants::MAIN_AUTO_ACTIVATE_USERS), [
                'title' => $this->getTranslate('admin_preferences', 'auto_activate_users'),
                'required' => false,
            ]));
        
        $this->form->getValidator()->set('title', true)->set('language', true, [
                new InArray(array_keys($languages))
            ])->set('theme', true, [
                new InArray(array_keys($themes))
            ])->set('auto_activate_users', false, [
                new Boolean()
            ], ['check' => Validator::CHECK_EMPTY, 'default' => false]);
    }
    
    /**
     * @return bool
     * @throws \GameX\Core\Configuration\Exceptions\CantSaveException
     * @throws \GameX\Core\Configuration\Exceptions\NotFoundException
     */
    protected function processForm()
    {
        $main = $this->preferences->getNode(PreferencesConstants::CATEGORY_MAIN);
        $main->set(PreferencesConstants::MAIN_TITLE, $this->form->getValue('title'));
        $main->set(PreferencesConstants::MAIN_LANGUAGE, $this->form->getValue('language'));
        $main->set(PreferencesConstants::MAIN_THEME, $this->form->getValue('theme'));
        $main->set(PreferencesConstants::MAIN_AUTO_ACTIVATE_USERS, $this->form->getValue('auto_activate_users'));
        $this->preferences->save();
        return true;
    }
}
