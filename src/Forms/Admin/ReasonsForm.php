<?php
namespace GameX\Forms\Admin;

use \GameX\Core\BaseForm;
use \GameX\Models\Reason;
use \GameX\Core\Forms\Elements\Text;
use \GameX\Core\Forms\Elements\Number as NumberElement;
use \GameX\Core\Forms\Elements\Checkbox;
use \GameX\Core\Forms\Rules\Number as NumberRule;
use \GameX\Core\Forms\Rules\Boolean;

class ReasonsForm extends BaseForm {

	/**
	 * @var string
	 */
	protected $name = 'admin_reason';

	/**
	 * @var Reason
	 */
	protected $reason;

	/**
	 * @param Reason $reason
	 */
	public function __construct(Reason $reason) {
		$this->reason = $reason;
	}

	/**
	 * @noreturn
	 */
	protected function createForm() {
        $timeEnabled = $this->reason->time !== null;
        $timeAttributes = $timeEnabled ? [] : [
            'disabled' => 'disabled'
        ];
		$this->form
			->add(new Text('title', $this->reason->title, [
                'title' => 'Title',
                'error' => 'Required',
                'required' => true,
            ]))
            ->add(new Checkbox('time_enabled', $timeEnabled, [
                'id' => 'reason-time-enabled',
                'title' => 'Admin can choose time',
                'required' => false,
            ]))
            ->add(new NumberElement('time', $this->reason->time, [
                'id' => 'reason-time',
                'title' => 'Time',
                'error' => 'Required',
                'required' => false,
                'attributes' => $timeAttributes
            ]))
            ->add(new Checkbox('overall', $this->reason->overall, [
                'title' => 'Punish at all servers',
                'required' => false,
            ]))
            ->add(new Checkbox('menu', $this->reason->menu, [
                'title' => 'Show in punish menu',
                'required' => false,
            ]))
            ->add(new Checkbox('active', $this->reason->active, [
                'title' => 'Active',
                'required' => false,
            ]));
		
		$this->form->getValidator()
            ->set('title', true)
            ->set('time_enabled', false, [
                new Boolean()
            ])
            ->set('time', false, [
                new NumberRule(0)
            ])
            ->set('overall', false, [
                new Boolean()
            ])
            ->set('menu', false, [
                new Boolean()
            ])
            ->set('active', false, [
                new Boolean()
            ]);
	}
    
    /**
     * @return boolean
     */
    protected function processForm() {
        $this->reason->title = $this->form->getValue('title');
        $this->reason->time = $this->form->getValue('time_enabled')
            ? $this->form->getValue('time')
            : null;
        $this->reason->overall = $this->form->getValue('overall') ? 1 : 0;
        $this->reason->menu = $this->form->getValue('menu') ? 1 : 0;
        $this->reason->active = $this->form->getValue('active') ? 1 : 0;
        return $this->reason->save();
    }
}
