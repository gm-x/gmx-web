<?php
namespace GameX\Forms\Admin;

use \GameX\Core\BaseForm;
use \GameX\Models\Server;
use \GameX\Core\Forms\Elements\Text;
use \GameX\Core\Forms\Elements\Number as NumberElement;
use \GameX\Core\Forms\Rules\Required;
use \GameX\Core\Forms\Rules\Trim;
use \GameX\Core\Forms\Rules\Number as NumberRule;
use \GameX\Core\Forms\Rules\IPv4;

abstract class ServerForm extends BaseForm {

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
				'title' => $this->getTranslate('admin_servers', 'name'),
				'required' => true,
			]))
			->add(new Text('ip', $this->server->ip, [
				'title' => $this->getTranslate('admin_servers', 'ip'),
				'required' => true,
			]))
			->add(new NumberElement('port', $this->server->port, [
				'title' => $this->getTranslate('admin_servers', 'port'),
				'required' => true,
			]))
            ->addRule('name', new Trim())
            ->addRule('name', new Required())
			->addRule('ip', new Trim())
			->addRule('ip', new Required())
			->addRule('ip', new IPv4())
			->addRule('port', new Trim())
			->addRule('port', new Required())
			->addRule('port', new NumberRule(1024, 65535));
	}
}
