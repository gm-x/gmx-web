<?php
namespace GameX\Core\Mail\Helpers;

use \GameX\Core\Mail\Email;
use \GameX\Core\Mail\Helper;
use \GameX\Core\Mail\Message;
use \GameX\Core\Mail\Senders\SMTP;
use \GameX\Core\Mail\Senders\Mail;
use \GameX\Core\Configuration\Node;

class MailHelper extends Helper {

    /**
     * @param Email $to
     * @param string $subject
     * @param string $body
     * @return Message
     */
    protected function message(Email $to, $subject, $body) {
        return (new Message($this->from))
            ->addTo($to)
            ->setSubject($subject)
            ->setBody($body);
    }
    
    /**
     * @param $config
     */
    protected function configure(Node $config) {
        $this->from = new Email($config->get('email'), $config->get('name'));
        switch ($config->get('type')) {
            case 'smtp': {
                $this->sender = new SMTP(
                    $config->get('host'),
                    $config->get('port'),
                    $config->get('secure'),
                    $config->get('username'),
                    $config->get('password')
                );
            } break;
            
            default: {
                $this->sender = new Mail();
            }
        }
    }
}
