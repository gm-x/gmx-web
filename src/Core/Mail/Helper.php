<?php
namespace GameX\Core\Mail;

use \Slim\Views\Twig;
use \GameX\Core\Mail\Senders\SMTP;
use \GameX\Core\Mail\Senders\Mail;

class Helper {

	/**
	 * @var Twig
	 */
	protected $view;

	/**
	 * @var Sender
	 */
	protected $sender;

	/**
	 * @var Email
	 */
	protected $from;

	public function __construct(Twig $view, array $config) {
		$this->view = $view;
		$this->sender = $this->createSender($config['transport']);
		$this->from = new Email($config['from']['email'], !empty($config['from']['name']) ? $config['from']['name'] : null);
	}

	/**
	 * @param string $template
	 * @param array $data
	 * @return string
	 */
	public function render($template, array $data = []) {
		return $this->view->fetch('email/' . $template . '.twig', $data);
	}

	/**
	 * @param Email $to
	 * @param string $subject
	 * @param string $body
	 * @param array $attachments
	 */
	public function send(Email $to, $subject, $body, array $attachments = []) {
		$message = new Message($this->from);

		$message
			->addTo($to)
			->setSubject($subject)
			->setBody($body);

		foreach ($attachments as $name => $attachment) {
			$message->addAttachment($name, $attachment);
		}

		$this->sender->send($message);
	}

	/**
	 * @param $config
	 * @return Sender
	 */
	protected function createSender($config) {
		switch ($config['type']) {
			case 'smtp': {
				$secure = null;
				if (!empty($config['secure']) && in_array($config['secure'], ['ssl', 'tls'], true)) {
					$secure = $config['secure'];
				}
				$username = !empty($config['username']) ? $config['username'] : null;
				$password = !empty($config['password']) ? $config['password'] : null;
				return new SMTP($config['host'], $config['port'], $secure, $username, $password);
			}

			default: {
				return new Mail();
			}
		}
	}
}
