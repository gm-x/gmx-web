<?php
namespace GameX\Forms\Admin;

use \GameX\Core\BaseForm;
use \GameX\Models\Group;
use \GameX\Core\Forms\Elements\Text;
use \GameX\Core\Forms\Elements\Flags as FlagsElement;
use \GameX\Core\Forms\Elements\Number as NumberElement;
use \GameX\Core\Forms\Rules\Required;
use \GameX\Core\Forms\Rules\Trim;
use \GameX\Core\Forms\Rules\Number as NumberRule;
use \GameX\Core\Forms\Rules\Flags as FlagsRule;

class GroupForm extends BaseForm {

	/**
	 * @var string
	 */
	protected $name = 'admin_group';

	/**
	 * @var Group
	 */
	protected $group;

	/**
	 * @param Group $group
	 */
	public function __construct(Group $group) {
		$this->group = $group;
	}

	/**
	 * @noreturn
	 */
	protected function createForm() {
		$this->form
			->add(new Text('title', $this->group->title, [
                'title' => 'Title',
                'error' => 'Required',
                'required' => true,
            ]))
            ->add(new FlagsElement('flags', $this->group->flags, [
                'title' => 'Flags',
                'error' => 'Required',
                'required' => true,
            ]))
            ->add(new NumberElement('priority', $this->group->priority, [
                'title' => 'Priority',
                'required' => false,
            ]))
			->addRule('title', new Required())
			->addRule('title', new Trim())
			->addRule('flags', new Required())
			->addRule('flags', new Trim())
			->addRule('flags', new FlagsRule())
			->addRule('priority', new Required())
			->addRule('priority', new NumberRule(0));
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
