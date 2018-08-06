<?php
namespace GameX\Forms\Admin;

use \GameX\Core\BaseForm;
use \GameX\Models\Server;
use \GameX\Core\Forms\Elements\Text;
use \GameX\Core\Forms\Elements\Number as NumberElement;
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
			]));
        
        $this->form->getValidator()
            ->set('name', true)
            ->set('ip', true, [
                new IPv4()
            ])
            ->set('port', true, [
                new NumberRule(1024, 65535)
            ]);
	}
}
