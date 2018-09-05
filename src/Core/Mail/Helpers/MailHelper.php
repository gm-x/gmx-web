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
        $sender = $config->getNode('sender');
        $this->from = new Email($sender->get('email'), $sender->get('name'));
        switch ($config->get('type')) {
            case 'smtp': {
                $smtp = $config->getNode('smtp');
                $this->sender = new SMTP(
                    $smtp->get('host'),
                    $smtp->get('port'),
                    $smtp->get('secure'),
                    $smtp->get('username'),
                    $smtp->get('password')
                );
            } break;
            
            default: {
                $this->sender = new Mail();
            }
        }
    }
}
