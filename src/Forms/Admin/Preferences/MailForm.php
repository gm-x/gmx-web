<?php
namespace GameX\Forms\Admin\Preferences;

use \GameX\Core\BaseForm;
use \GameX\Core\Configuration\Config;
use \GameX\Core\Forms\Elements\Text;
use \GameX\Core\Forms\Elements\Password;
use \GameX\Core\Forms\Elements\Email as EmailElement;
use \GameX\Core\Forms\Elements\Number as NumberElelement;
use \GameX\Core\Forms\Elements\Checkbox;
use \GameX\Core\Forms\Elements\Select;
use GameX\Core\Forms\Rules\Boolean;
use \GameX\Core\Forms\Rules\Required;
use \GameX\Core\Forms\Rules\Trim;
use \GameX\Core\Forms\Rules\Email as EmailRule;
use \GameX\Core\Forms\Rules\Number as NumberRule;
use \GameX\Core\Forms\Rules\InArray;

class MailForm extends BaseForm {

	/**
	 * @var string
	 */
	protected $name = 'admin_preferences_mail';

	/**
	 * @var Config
	 */
	protected $config;

	/**
	 * @param Config $config
	 */
	public function __construct(Config $config, $test) {
		$this->config = $config;
	}

	/**
	 * @noreturn
	 */
	protected function createForm() {
        $config = $this->config->get('mail');
		$this->form
            ->add(new Checkbox('enabled', $config->get('enabled'), [
                'title' => $this->getTranslate('admin_preferences', 'enabled'),
            ]))
            ->add(new Text('from_name', $config->get('name'), [
                'title' => $this->getTranslate('admin_preferences', 'from_name'),
            ]))
            ->add(new EmailElement('from_email', $config->get('email'), [
                'title' => $this->getTranslate('admin_preferences', 'from_email'),
            ]))
            ->add(new Select('transport_type', $config->get('type'), [
                'smtp' => "SMTP",
                'mail' => 'Mail'
            ], [
                'title' => $this->getTranslate('admin_preferences', 'transport'),
                'id' => 'email_pref_transport'
            ]))
            ->add(new Text('smtp_host', $config->get('host'), [
                'title' => $this->getTranslate('admin_preferences', 'host'),
            ]))
            ->add(new NumberElelement('smtp_port', $config->get('port'), [
                'title' => $this->getTranslate('admin_preferences', 'port'),
            ]))
            ->add(new Select('smtp_secure', $config->get('secure'), [
                'none' => $this->getTranslate('admin_preferences', 'secure_none'),
                'ssl' => "SSL",
                'tls' => 'TLS'
            ], [
                'title' => $this->getTranslate('admin_preferences', 'secure'),
            ]))
            ->add(new Text('smtp_user', $config->get('username'), [
                'title' => $this->getTranslate('admin_preferences', 'username'),
            ]))
            ->add(new Password('smtp_pass', $config->get('password'), [
                'title' => $this->getTranslate('admin_preferences', 'password'),
            ]))
            ->addRule('enabled', new Boolean())
            ->addRule('from_name', new Trim())
            ->addRule('from_email', new Trim())
            ->addRule('from_email', new EmailRule())
            ->addRule('transport_type', new Required())
            ->addRule('transport_type', new Trim())
            ->addRule('transport_type', new InArray(['smtp', 'mail']))
            ->addRule('smtp_host', new Trim())
            ->addRule('smtp_port', new NumberRule(1, 65536))
            ->addRule('smtp_secure', new Trim())
            ->addRule('smtp_secure', new InArray(['none', 'ssl', 'tls']))
            ->addRule('smtp_user', new Trim())
            ->addRule('smtp_pass', new Trim());
	}
    
    /**
     * @return boolean
     */
    protected function processForm() {
        $config = $this->config->get('mail');
        $enabled = (bool) $this->form->getValue('enabled');
        $config->set('enabled', $enabled);
        $config->set('name', $this->form->getValue('from_name'));
        $config->set('email', $this->form->getValue('from_email'));
        $config->set('type', $this->form->getValue('transport_type'));
        $config->set('host', $this->form->getValue('smtp_host'));
        $config->set('port', (int) $this->form->getValue('smtp_port'));
        $config->set('secure', $this->form->getValue('smtp_secure'));
        $config->set('username', $this->form->getValue('smtp_user'));
        $config->set('password', $this->form->getValue('smtp_pass'));
        $this->config->save();
        return true;
    }
}
