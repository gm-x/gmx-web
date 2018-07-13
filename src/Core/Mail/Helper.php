<?php
namespace GameX\Core\Mail;

use \Slim\Views\Twig;
use \GameX\Core\Mail\Senders\SMTP;
use \GameX\Core\Mail\Senders\Mail;
use \GameX\Core\Configuration\Node;

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

    /**
     * Helper constructor.
     * @param Twig $view
     * @param Node $config
     */
	public function __construct(Twig $view, Node $config) {
		$this->view = $view;
		$this->sender = $this->createSender($config->get('transport'));
		$from = $config->get('from');
		$this->from = new Email($from->get('email'), $from->get('name'));
	}

	/**
	 * @return Email
	 */
	public function getFrom() {
		return $this->from;
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
	protected function createSender(Node $config) {
		switch ($config->get('type')) {
			case 'smtp': {
				return new SMTP(
				    $config->get('host'),
				    $config->get('port'),
				    $config->get('secure'),
				    $config->get('username'),
				    $config->get('password')
                );
			}

			default: {
				return new Mail();
			}
		}
	}
}
