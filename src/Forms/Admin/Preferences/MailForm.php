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
use \GameX\Core\Forms\Rules\Email as EmailRule;
use \GameX\Core\Forms\Rules\Number as NumberRule;
use \GameX\Core\Forms\Rules\InArray;

class MailForm extends BaseForm
{
    
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
    public function __construct(Node $config)
    {
        $this->config = $config;
    }
    
    /**
     * @noreturn
     */
    protected function createForm()
    {
        $sender = $this->config->getNode('sender');
        $smtp = $this->config->existsNode('smtp') ? $this->config->getNode('smtp') : $this->getEmptySMTPNode();
        $this->form->add(new Checkbox('enabled', $this->config->get('enabled'), [
                'title' => $this->getTranslate('admin_preferences', 'enabled'),
            ]))->add(new Text('from_name', $sender->get('name'), [
                'title' => $this->getTranslate('admin_preferences', 'from_name'),
            ]))->add(new EmailElement('from_email', $sender->get('email'), [
                'title' => $this->getTranslate('admin_preferences', 'from_email'),
            ]))->add(new Select('transport_type', $this->config->get('type'), [
                'smtp' => "SMTP",
                'mail' => 'Mail'
            ], [
                'title' => $this->getTranslate('admin_preferences', 'transport'),
                'id' => 'email_pref_transport'
            ]))->add(new Text('smtp_host', $smtp->get('host'), [
                'title' => $this->getTranslate('admin_preferences', 'host'),
            ]))->add(new NumberElement('smtp_port', $smtp->get('port'), [
                'title' => $this->getTranslate('admin_preferences', 'port'),
            ]))->add(new Select('smtp_secure', $smtp->get('secure'), [
                'none' => $this->getTranslate('admin_preferences', 'secure_none'),
                'ssl' => "SSL",
                'tls' => 'TLS'
            ], [
                'title' => $this->getTranslate('admin_preferences', 'secure'),
            ]))->add(new Text('smtp_user', $smtp->get('username'), [
                'title' => $this->getTranslate('admin_preferences', 'username'),
            ]))->add(new Password('smtp_pass', $smtp->get('password'), [
                'title' => $this->getTranslate('admin_preferences', 'password'),
            ]));
        
        $this->form->getValidator()->set('enabled', false, [
                new Boolean()
            ])->set('from_name', true)->set('from_email', true, [
                new EmailRule()
            ])->set('transport_type', true, [
                new InArray(['smtp', 'mail'])
            ])->set('smtp_host', false)->set('smtp_port', false, [
                new NumberRule(1, 65536)
            ])->set('smtp_secure', false, [
                new InArray(['none', 'ssl', 'tls'])
            ])->set('smtp_user', false)->set('smtp_pass', false);
    }
    
    /**
     * @return boolean
     */
    protected function processForm()
    {
        $enabled = (bool)$this->form->getValue('enabled');
        $this->config->set('enabled', $enabled);
        $this->config->getNode('sender')->set('name', $this->form->getValue('from_name'))->set('email',
                $this->form->getValue('from_email'));
        $this->config->set('type', $this->form->getValue('transport_type'));
        
        if ($this->form->getValue('transport_type') === 'smtp') {
            if (!$this->config->existsNode('smtp')) {
                $this->config->set('smtp', []);
            }
            
            $this->config->getNode('smtp')->set('host', $this->form->getValue('smtp_host'))->set('port',
                    (int)$this->form->getValue('smtp_port'))->set('secure',
                    $this->form->getValue('smtp_secure'))->set('username',
                    $this->form->getValue('smtp_user'))->set('password', $this->form->getValue('smtp_pass'));
        }
        
        return true;
    }
    
    protected function getEmptySMTPNode()
    {
        return new Node([
            'host' => '127.0.0.1',
            'port' => 25,
            'secure' => 'none',
            'username' => '',
        ]);
    }
}
