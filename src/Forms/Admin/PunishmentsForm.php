<?php

namespace GameX\Forms\Admin;

use \GameX\Core\BaseForm;
use \GameX\Core\Validate\Validator;
use \GameX\Models\Server;
use \GameX\Models\Punishment;
use \GameX\Core\Forms\Elements\Text;
use \GameX\Core\Forms\Elements\Select;
use \GameX\Core\Forms\Elements\Expired as ExpiredElement;
use \GameX\Core\Validate\Rules\InArray;
use \GameX\Core\Validate\Rules\Number;
use \GameX\Core\Validate\Rules\Expired as ExpiredRule;
use \GameX\Core\Exceptions\PunishmentsFormException;

class PunishmentsForm extends BaseForm
{

    /**
     * @var string
     */
    protected $name = 'admin_punishments';

    /**
     * @var Server
     */
    protected $server;

    /**
     * @var Punishment
     */
    protected $punishment;

    /**
     * @param Server $server
     * @param Punishment $punishment
     */
    public function __construct(Server $server, Punishment $punishment)
    {
        $this->server = $server;
        $this->punishment = $punishment;
    }

    /**
     * @noreturn
     */
    protected function createForm()
    {
        $reasons = $this->getReasons();
        if (!count($reasons)) {
            throw new PunishmentsFormException();
        }

        $this->form->add(new Select('reason', $this->punishment->reason_id, $reasons, [
                'title' => $this->getTranslate($this->name, 'reason'),
                'required' => true,
                'empty_option' => $this->getTranslate($this->name, 'reason_empty'),
            ]))->add(new Text('details', $this->punishment->details, [
                'title' => $this->getTranslate($this->name, 'details'),
                'required' => false,
            ]))->add(new Select('type', $this->punishment->type, [
                'ban' => $this->getTranslate($this->name, 'ban'),
            ], [
		        'title' => $this->getTranslate($this->name, 'type'),
		        'required' => true,
		        'empty_option' => $this->getTranslate($this->name, 'type_empty'),
            ]))->add(new ExpiredElement('expired', $this->punishment->expired_at, [
                'title' => $this->getTranslate($this->name, 'expired'),
                'required' => false,
            ]));

        $this->form->getValidator()
	        ->set('type', true, [
		        new InArray([
			        'ban'
		        ])
	        ])
	        ->set('reason', true, [
                new Number(1),
                new InArray(array_keys($reasons)),
            ])
	        ->set('details', false)
	        ->set('expired', false, [
                new ExpiredRule()
            ], [
		        'check' => Validator::CHECK_ARRAY,
		        'trim' => false,
		        'allow_null' => true,
	        ]);
    }
    
    /**
     * @return bool
     * @throws \Exception
     */
    protected function processForm()
    {
        $this->punishment->reason_id = $this->form->getValue('reason');
        $this->punishment->type = $this->form->getValue('type');
        $this->punishment->expired_at = $this->form->getValue('expired');
        $this->punishment->status = Punishment::STATUS_PUNISHED;
        return $this->punishment->save();
    }
    
    /**
     * @return array
     */
    protected function getReasons()
    {
        $reasons = [];
        foreach ($this->server->reasons as $reason) {
            $reasons[$reason->id] = $reason->title;
        }
        return $reasons;
    }
}
