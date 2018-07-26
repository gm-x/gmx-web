<?php
namespace GameX\Forms\Admin\Preferences;

use \GameX\Core\BaseForm;
use \GameX\Core\Configuration\Node;
use \GameX\Core\Forms\Elements\Text;
use \GameX\Core\Forms\Elements\Password;
use \GameX\Core\Forms\Elements\Email as EmailElement;
use \GameX\Core\Forms\Elements\Number as NumberElement;
use \GameX\Core\Forms\Elements\Checkbox;
use \GameX\Core\Forms\Elements\Select;
use \GameX\Core\Forms\Rules\Boolean;
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
	 * @param Node $config
	 */
	public function __construct(Node $config) {
		$this->config = $config;
	}

	/**
	 * @noreturn
	 */
	protected function createForm() {
		$this->form
            ->add(new Checkbox('enabled', $this->config->get('enabled'), [
                'title' => $this->getTranslate('admin_preferences', 'enabled'),
            ]))
            ->add(new Text('from_name', $this->config->get('name'), [
                'title' => $this->getTranslate('admin_preferences', 'from_name'),
            ]))
            ->add(new EmailElement('from_email', $this->config->get('email'), [
                'title' => $this->getTranslate('admin_preferences', 'from_email'),
            ]))
            ->add(new Select('transport_type', $this->config->get('type'), [
                'smtp' => "SMTP",
                'mail' => 'Mail'
            ], [
                'title' => $this->getTranslate('admin_preferences', 'transport'),
                'id' => 'email_pref_transport'
            ]))
            ->add(new Text('smtp_host', $this->config->get('host'), [
                'title' => $this->getTranslate('admin_preferences', 'host'),
            ]))
            ->add(new NumberElement('smtp_port', $this->config->get('port'), [
                'title' => $this->getTranslate('admin_preferences', 'port'),
            ]))
            ->add(new Select('smtp_secure', $this->config->get('secure'), [
                'none' => $this->getTranslate('admin_preferences', 'secure_none'),
                'ssl' => "SSL",
                'tls' => 'TLS'
            ], [
                'title' => $this->getTranslate('admin_preferences', 'secure'),
            ]))
            ->add(new Text('smtp_user', $this->config->get('username'), [
                'title' => $this->getTranslate('admin_preferences', 'username'),
            ]))
            ->add(new Password('smtp_pass', $this->config->get('password'), [
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
        $this->config->set('enabled', $this->form->getValue('enabled'));
        $this->config->set('name', $this->form->getValue('from_name'));
        $this->config->set('email', $this->form->getValue('from_email'));
        $this->config->set('type', $this->form->getValue('transport_type'));
        $this->config->set('host', $this->form->getValue('smtp_host'));
        $this->config->set('port', (int) $this->form->getValue('smtp_port'));
        $this->config->set('secure', $this->form->getValue('smtp_secure'));
        $this->config->set('username', $this->form->getValue('smtp_user'));
        $this->config->set('password', $this->form->getValue('smtp_pass'));
        return true;
    }
}
