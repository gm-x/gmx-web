<?php
namespace GameX\Core\Mail;

use Psr\Container\ContainerInterface;
use \Tx\Mailer\SMTP;
use \Slim\Views\Twig;

class MailHelper {
    /**
     * @var SMTP|null
     */
    protected $smtp = null;

    /**
     * @var array
     */
    protected $from;

    /**
     * @var Twig
     */
    protected $view;

    public function __construct(ContainerInterface $container) {
        $config = array_merge([
            'from' => [
                'name' => 'test',
                'email' => 'test@example.com'
            ],
            'transport' => [
                'type' => 'mail',
            ]
        ], (array)$container['config']['mail']);


        $this->from = $config['from'];
        $this->view = $container->get('view');
		if ($config['transport']['type'] === 'smtp') {
			$this->smtp = new SMTP();
			$this->smtp->setServer($config['transport']['host'], $config['transport']['port']);
			if (!empty($config['transport']['username']) && !empty($config['transport']['password'])) {
				$this->smtp->setAuth($config['transport']['username'], $config['transport']['password']);
			}
		}
    }

	/**
	 * @param $to
	 * @param $subject
	 * @param $body
	 * @param array $attachments
	 * @return bool
	 */
    public function send($to, $subject, $body, array $attachments = []) {
        $message = new MailMessage();

        $message
            ->setFrom($this->from['name'], $this->from['email'])
            ->addTo($to['name'], $to['email'])
            ->setSubject($subject)
            ->setBody($body);

        foreach ($attachments as $name => $attachment) {
            $message->addAttachment($name, $attachment);
        }

        return $this->smtp !== null
            ? $this->smtp->send($message)
            : $this->sendMail($message);
    }

    public function render($template, array $data = []) {
        return $this->view->fetch('email/' . $template . '.twig', $data);
    }

    protected function sendMail(MailMessage $message) {
    	return false;
//        return mail(
//            $message->getFromEmail(),
//            $message->getSubject(),
//            $message->getBodyForMail(),
//            $message->getHeadersForMail()
//        );
    }
}
