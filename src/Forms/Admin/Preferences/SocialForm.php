<?php

namespace GameX\Forms\Admin\Preferences;

use \GameX\Constants\PreferencesConstants;
use \GameX\Core\BaseForm;
use \GameX\Core\Configuration\Config;
use \GameX\Core\Forms\Elements\Text;
use \GameX\Core\Forms\Elements\Password;
use \GameX\Core\Forms\Elements\Number as NumberElement;
use \GameX\Core\Forms\Elements\Checkbox;
use \GameX\Core\Validate\Rules\Boolean;
use \GameX\Core\Validate\Rules\Number as NumberRule;
use \GameX\Core\Configuration\Exceptions\CantSaveException;
use \GameX\Core\Configuration\Exceptions\NotFoundException;

class SocialForm extends BaseForm
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
     * @var bool
     */
    protected $hasAccessToEdit;
    
    /**
     * @param Config $preferences
     * @param bool $hasAccessToEdit
     */
    public function __construct(Config $preferences, $hasAccessToEdit = true)
    {
        $this->preferences = $preferences;
        $this->hasAccessToEdit = $hasAccessToEdit;
    }
    
    /**
     * @noreturn
     */
    protected function createForm()
    {
    	$validator = $this->form->getValidator();

        $social = $this->preferences->getNode(PreferencesConstants::CATEGORY_SOCIAL);

        $value = $social->getNode('steam');
        $this->form
	        ->add(new Checkbox('steam_enabled', $value->get('enabled'), [
		        'title' => $this->getTranslate('admin_preferences', 'social_enabled'),
		        'disabled' => !$this->hasAccessToEdit,
	        ]))
	        ->add(new Text('steam_icon', $value->get('icon'), [
		        'title' => $this->getTranslate('admin_preferences', 'social_icon'),
		        'disabled' => !$this->hasAccessToEdit,
	        ]));

	    $validator
		    ->set('steam_enabled', false, [new Boolean()])
		    ->set('steam_icon', false);

	    $value = $social->getNode('vk');
	    $this->form
		    ->add(new Checkbox('vk_enabled', $value->get('enabled'), [
			    'title' => $this->getTranslate('admin_preferences', 'social_enabled'),
			    'disabled' => !$this->hasAccessToEdit,
		    ]))
		    ->add(new Text('vk_icon', $value->get('icon'), [
			    'title' => $this->getTranslate('admin_preferences', 'social_icon'),
			    'disabled' => !$this->hasAccessToEdit,
		    ]))
		    ->add(new NumberElement('vk_id', $value->get('id'), [
			    'title' => $this->getTranslate('admin_preferences', 'social_id'),
			    'disabled' => !$this->hasAccessToEdit,
		    ]))
		    ->add(new Password('vk_key', $value->get('key'), [
			    'title' => $this->getTranslate('admin_preferences', 'social_key'),
			    'disabled' => !$this->hasAccessToEdit,
		    ]))
		    ->add(new Password('vk_secret', $value->get('secret'), [
			    'title' => $this->getTranslate('admin_preferences', 'social_secret'),
			    'disabled' => !$this->hasAccessToEdit,
		    ]));

	    $validator
		    ->set('vk_enabled', false, [new Boolean()])
		    ->set('vk_icon', false)
		    ->set('vk_id', false, [new NumberRule(1)])
		    ->set('vk_key', false)
		    ->set('vk_secret', false);

	    $value = $social->getNode('facebook');
	    $this->form
		    ->add(new Checkbox('facebook_enabled', $value->get('enabled'), [
			    'title' => $this->getTranslate('admin_preferences', 'social_enabled'),
			    'disabled' => !$this->hasAccessToEdit,
		    ]))
		    ->add(new Text('facebook_icon', $value->get('icon'), [
			    'title' => $this->getTranslate('admin_preferences', 'social_icon'),
			    'disabled' => !$this->hasAccessToEdit,
		    ]))
		    ->add(new NumberElement('facebook_id', $value->get('id'), [
			    'title' => $this->getTranslate('admin_preferences', 'social_id'),
			    'disabled' => !$this->hasAccessToEdit,
		    ]))
		    ->add(new Password('facebook_key', $value->get('key'), [
			    'title' => $this->getTranslate('admin_preferences', 'social_key'),
			    'disabled' => !$this->hasAccessToEdit,
		    ]))
		    ->add(new Password('facebook_secret', $value->get('secret'), [
			    'title' => $this->getTranslate('admin_preferences', 'social_secret'),
			    'disabled' => !$this->hasAccessToEdit,
		    ]));

	    $validator
		    ->set('facebook_enabled', false, [new Boolean()])
		    ->set('facebook_icon', false)
		    ->set('facebook_id', false, [new NumberRule(1)])
		    ->set('facebook_key', false)
		    ->set('facebook_secret', false);

	    $value = $social->getNode('discord');
	    $this->form
		    ->add(new Checkbox('discord_enabled', $value->get('enabled'), [
			    'title' => $this->getTranslate('admin_preferences', 'social_enabled'),
			    'disabled' => !$this->hasAccessToEdit,
		    ]))
		    ->add(new Text('discord_icon', $value->get('icon'), [
			    'title' => $this->getTranslate('admin_preferences', 'social_icon'),
			    'disabled' => !$this->hasAccessToEdit,
		    ]))
		    ->add(new NumberElement('discord_id', $value->get('id'), [
			    'title' => $this->getTranslate('admin_preferences', 'social_id'),
			    'disabled' => !$this->hasAccessToEdit,
		    ]))
		    ->add(new Password('discord_key', $value->get('key'), [
			    'title' => $this->getTranslate('admin_preferences', 'social_key'),
			    'disabled' => !$this->hasAccessToEdit,
		    ]))
		    ->add(new Password('discord_secret', $value->get('secret'), [
			    'title' => $this->getTranslate('admin_preferences', 'social_secret'),
			    'disabled' => !$this->hasAccessToEdit,
		    ]));

	    $validator
		    ->set('discord_enabled', false, [new Boolean()])
		    ->set('discord_icon', false)
		    ->set('discord_id', false, [new NumberRule(1)])
		    ->set('discord_key', false)
		    ->set('discord_secret', false);
    }

	/**
	 * @return bool
	 * @throws CantSaveException
	 * @throws NotFoundException
	 */
    protected function processForm()
    {
	    $social = $this->preferences->getNode(PreferencesConstants::CATEGORY_SOCIAL);

	    $value = $social->getNode('steam');
	    $value->set('enabled', $this->form->getValue('steam_enabled'));
	    $value->set('icon', $this->form->getValue('steam_icon'));

	    $value = $social->getNode('vk');
	    $value->set('enabled', $this->form->getValue('vk_enabled'));
	    $value->set('id', $this->form->getValue('vk_id'));
	    $value->set('key', $this->form->getValue('vk_key'));
	    $value->set('secret', $this->form->getValue('vk_secret'));

	    $value = $social->getNode('facebook');
	    $value->set('enabled', $this->form->getValue('facebook_enabled'));
	    $value->set('id', $this->form->getValue('facebook_id'));
	    $value->set('key', $this->form->getValue('facebook_key'));
	    $value->set('secret', $this->form->getValue('facebook_secret'));

	    $value = $social->getNode('discord');
	    $value->set('enabled', $this->form->getValue('discord_enabled'));
	    $value->set('id', $this->form->getValue('discord_id'));
	    $value->set('key', $this->form->getValue('discord_key'));
	    $value->set('secret', $this->form->getValue('discord_secret'));

        $this->preferences->save();
        return true;
    }
}
