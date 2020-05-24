<?php

namespace GameX\Forms\Admin\Preferences;

use \Psr\Container\ContainerInterface;
use \GameX\Constants\PreferencesConstants;
use \GameX\Core\BaseForm;
use \GameX\Core\Auth\Helpers\RoleHelper;
use \GameX\Core\Lang\Language;
use \GameX\Core\Configuration\Config;
use \GameX\Core\Forms\Elements\Text;
use \GameX\Core\Forms\Elements\Select;
use \GameX\Core\Forms\Elements\Checkbox;
use \GameX\Core\Validate\Validator;
use \GameX\Core\Validate\Rules\InArray;
use \GameX\Core\Validate\Rules\Boolean;
use \GameX\Core\Validate\Rules\Number;

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
     * @var Language
     */
    protected $language;

    /**
     * @var bool
     */
    protected $hasAccessToEdit;

    /**
     * @var RoleHelper
     */
    protected $roleHelper;
    
    /**
     * @param ContainerInterface $container
     * @param bool $hasAccessToEdit
     */
    public function __construct(ContainerInterface $container, $hasAccessToEdit = true)
    {
        $this->preferences = $container->get('preferences');
        $this->language = $container->get('lang');
        $this->hasAccessToEdit = $hasAccessToEdit;

        $this->roleHelper = new RoleHelper($container);
    }
    
    /**
     * @noreturn
     */
    protected function createForm()
    {
        $main = $this->preferences->getNode(PreferencesConstants::CATEGORY_MAIN);
        $themes = $this->preferences->getNode('themes')->toArray();
        $roles = $this->preferences->getNode(PreferencesConstants::CATEGORY_ROLES);

        $rolesList = $this->roleHelper->getRolesAsArray();

        $this->form->add(new Text('title', $main->get(PreferencesConstants::MAIN_TITLE), [
                'title' => $this->getTranslate('admin_preferences', 'title'),
                'required' => true,
                'disabled' => !$this->hasAccessToEdit,
            ]))->add(new Select('language', $main->get(PreferencesConstants::MAIN_LANGUAGE), $this->language->getLanguages(), [
                'title' => $this->getTranslate('admin_preferences', 'language'),
                'required' => true,
                'disabled' => !$this->hasAccessToEdit,
            ]))->add(new Select('theme', $main->get(PreferencesConstants::MAIN_THEME), $themes, [
                'title' => $this->getTranslate('admin_preferences', 'theme'),
                'required' => true,
                'disabled' => !$this->hasAccessToEdit,
            ]))->add(new Checkbox('auto_activate_users', $main->get(PreferencesConstants::MAIN_AUTO_ACTIVATE_USERS), [
                'title' => $this->getTranslate('admin_preferences', 'auto_activate_users'),
                'required' => false,
                'disabled' => !$this->hasAccessToEdit,
            ]))->add(new Select('default_role', $roles->get('default'), $rolesList, [
                'title' => $this->getTranslate('admin_preferences', 'default_role'),
                'required' => false,
                'disabled' => !$this->hasAccessToEdit,
                'empty_option' => $this->getTranslate('admin_preferences', 'default_role_empty'),
            ]));
        
        $this->form->getValidator()->set('title', true)
	        ->set('language', true, [
                new InArray(array_keys($this->language->getLanguages())),
            ])
	        ->set('theme', true, [
                new InArray(array_keys($themes)),
            ])
	        ->set('auto_activate_users', false, [
                new Boolean(),
            ], ['check' => Validator::CHECK_EMPTY, 'default' => false])
            ->set('default_role', false, [
                new Number(1),
                new InArray(array_keys($rolesList)),
            ]);
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

	    $defaultRole = $this->form->getValue('default_role');
	    if (empty($defaultRole)) {
            $defaultRole = 0;
        }
        $this->preferences
            ->getNode(PreferencesConstants::CATEGORY_ROLES)
            ->set('default', $defaultRole);

        $this->preferences->save();
        return true;
    }
}
