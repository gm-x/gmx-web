<?php
namespace GameX\Forms\Admin;

use \GameX\Core\BaseForm;
use \GameX\Models\Server;
use \GameX\Core\Forms\Elements\Text;
use \GameX\Core\Forms\Elements\Number as NumberElement;
use \GameX\Core\Forms\Rules\Number as NumberRule;
use \GameX\Core\Forms\Rules\IPv4;
use \GameX\Core\Forms\Rules\Callback;

class ServersForm extends BaseForm {

	/**
	 * @var string
	 */
	protected $name = 'admin_servers';

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
     * @param mixed $value
     * @param array $values
     * @return mixed|null
     */
    public function checkExists($value, array $values) {
        return !Server::where([
            'ip' => $values['ip'],
            'port' => $values['port']
        ])->exists() ? $value : null;
    }

	/**
	 * @noreturn
	 */
	protected function createForm() {
		$this->form
			->add(new Text('name', $this->server->name, [
				'title' => $this->getTranslate($this->name, 'name'),
				'required' => true,
			]))
			->add(new Text('ip', $this->server->ip, [
				'title' => $this->getTranslate($this->name, 'ip'),
				'required' => true,
                'attributes' => [
                    'pattern' => '((^|\.)((25[0-5])|(2[0-4]\d)|(1\d\d)|([1-9]?\d))){4}$',
                ],
			]))
			->add(new NumberElement('port', $this->server->port, [
				'title' => $this->getTranslate($this->name, 'port'),
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
        
        if (!$this->server->exists) {
            $this->form->getValidator()
                ->add('port', new Callback(
                    [$this, 'checkExists'], $this->getTranslate($this->name, 'already_exists')
                ));
        }
	}
    
    /**
     * @return boolean
     */
    protected function processForm() {
        $this->server->fill($this->form->getValues());
        return $this->server->save();
    }
}
