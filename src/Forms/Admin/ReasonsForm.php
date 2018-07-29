<?php
namespace GameX\Forms\Admin;

use \GameX\Core\BaseForm;
use \GameX\Models\Reason;
use \GameX\Core\Forms\Elements\Text;
use \GameX\Core\Forms\Elements\Number as NumberElement;
use \GameX\Core\Forms\Elements\Checkbox;
use \GameX\Core\Forms\Rules\Required;
use \GameX\Core\Forms\Rules\Trim;
use \GameX\Core\Forms\Rules\Number as NumberRule;
use \GameX\Core\Forms\Rules\Boolean;

class ReasonsForm extends BaseForm {

	/**
	 * @var string
	 */
	protected $name = 'admin_group';

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
		$this->form
			->add(new Text('title', $this->reason->title, [
                'title' => 'Title',
                'error' => 'Required',
                'required' => true,
            ]))
            ->add(new NumberElement('time', $this->reason->time, [
                'title' => 'Time',
                'error' => 'Required',
                'required' => true,
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
            ]))
            ->addRule('title', new Trim())
            ->addRule('title', new Required())
			->addRule('time', new Required())
			->addRule('time', new NumberRule(0))
			->addRule('overall', new Boolean())
			->addRule('menu', new Boolean())
			->addRule('menu', new Boolean())
			->addRule('active', new Boolean());
	}
    
    /**
     * @return boolean
     */
    protected function processForm() {
        $this->group->title = $this->form->get('title')->getValue();
        $this->group->flags = $this->form->get('flags')->getFlagsInt();
        $this->group->priority = $this->form->get('priority')->getValue();
        return $this->group->save();
    }
}
