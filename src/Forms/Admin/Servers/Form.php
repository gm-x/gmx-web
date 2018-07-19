<?php
namespace GameX\Forms\Admin\Servers;

use \GameX\Core\BaseForm;
use GameX\Core\Configuration\Config;
use \GameX\Models\Server;
use \GameX\Core\Forms\Elements\Text;
use \GameX\Core\Forms\Elements\Number as NumberElement;
use \GameX\Core\Forms\Rules\Required;
use \GameX\Core\Forms\Rules\Trim;
use \GameX\Core\Forms\Rules\Number as NumberRule;
use \GameX\Core\Forms\Rules\IPv4;

abstract class Form extends BaseForm {

	/**
	 * @var string
	 */
	protected $name = 'admin_server';

	/**
	 * @var Server
	 */
	protected $server;

	/**
	 * @param Server $server
	 */
	public function __construct(Server $server) {
		$this->server = $server;
	}

	/**
	 * @noreturn
	 */
	protected function createForm() {
		$this->form
			->add(new Text('name', $this->server->name, [
				'title' => 'Name',
				'error' => 'Required',
				'required' => true,
				'attributes' => [],
			]))
			->add(new Text('ip', $this->server->ip, [
				'title' => 'IP',
				'error' => 'Required',
				'required' => true,
				'attributes' => [],
			]))
			->add(new NumberElement('port', $this->server->port, [
				'title' => 'Port',
				'error' => 'Required',
				'required' => true,
				'attributes' => [],
			]))
			->addRule('name', new Required())
			->addRule('name', new Trim())
			->addRule('ip', new Required())
			->addRule('ip', new IPv4())
			->addRule('port', new Required())
			->addRule('port', new NumberRule(1024, 65535));
	}
}
