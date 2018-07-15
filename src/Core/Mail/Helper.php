<?php
namespace GameX\Core\Mail;

use \Slim\Views\Twig;
use \GameX\Core\Configuration\Node;

abstract class Helper {

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
		$this->configure($config);
	}

	/**
	 * @return Email
	 */
	public function getFrom() {
		return $this->from;
	}
    
    /**
     * @param Node $config
     * @return $this
     */
	public function setConfiguration(Node $config) {
        $this->configure($config);
        return $this;
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
	 */
	public function send(Email $to, $subject, $body) {
		$this->sender->send($this->message($to, $subject, $body));
	}
    
    /**
     * @param Email $to
     * @param string $subject
     * @param string $body
     * @return Message
     */
	abstract protected function message(Email $to, $subject, $body);

	/**
	 * @param $config
	 */
	abstract protected function configure(Node $config);
}
