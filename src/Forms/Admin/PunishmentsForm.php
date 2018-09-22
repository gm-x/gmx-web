<?php
namespace GameX\Forms\Admin;

use \GameX\Core\BaseForm;
use \GameX\Models\Server;
use \GameX\Models\Punishment;
use \GameX\Models\Group;
use \GameX\Core\Forms\Elements\Text;
use \GameX\Core\Forms\Elements\Select;
use \GameX\Core\Forms\Elements\Date as DateElement;
use \GameX\Core\Forms\Elements\Checkbox;
use \GameX\Core\Forms\Rules\InArray;
use \GameX\Core\Forms\Rules\Boolean;
use \GameX\Core\Forms\Rules\Number;
use \GameX\Core\Forms\Rules\Date as DateRule;
use \GameX\Core\Forms\Rules\Callback;
use \GameX\Core\Exceptions\PunishmentsFormException;

class PunishmentsForm extends BaseForm {

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
	public function __construct(Server $server, Punishment $punishment) {
		$this->server = $server;
		$this->punishment = $punishment;
	}

	/**
	 * @noreturn
	 */
	protected function createForm() {
        $reasons = $this->getReasons();
		if (!count($reasons)) {
			throw new PunishmentsFormException();
		}
		
		$this->form
            ->add(new Select('reason', $this->punishment->reason_id, $reasons, [
                'title' => 'Reason',
                'required' => true,
                'empty_option' => 'Choose reason',
            ]))
            ->add(new DateElement('forever', $this->punishment->expired_at === null, [
                'title' => 'Forever',
            ]))
            ->add(new DateElement('expired', $this->punishment->expired_at, [
                'title' => 'Expired',
                'required' => true,
            ]));
		
		$this->form->getValidator()
            ->set('group', true, [
                new Number(1),
                new InArray(array_keys($reasons)),
            ])
            ->set('forever',false, [
                new Boolean()
            ])
            ->set('expired',true, [
                new DateRule()
            ]);
	}
    
    /**
     * @return boolean
     */
    protected function processForm() {
        $this->punishment->reason_id = $this->form->getValue('reason');
        $this->punishment->expired_at = $this->form->getValue('expired');
        return $this->punishment->save();
    }
    
    /**
     * @return array
     */
    protected function getReasons() {
        $reasons = [];
        foreach ($this->server->reasons as $reason) {
            $reasons[$reason->id] = $reason->title;
        }
        return $reasons;
    }
}
