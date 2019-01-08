<?php
namespace GameX\Forms\Admin;

use \GameX\Core\BaseForm;
use \GameX\Models\Group;
use \GameX\Core\Forms\Elements\Text;
use \GameX\Core\Forms\Elements\Flags as FlagsElement;
use \GameX\Core\Forms\Elements\Number as NumberElement;
use \GameX\Core\Forms\Rules\Number as NumberRule;
use \GameX\Core\Forms\Rules\Flags as FlagsRule;

class GroupsForm extends BaseForm {

	/**
	 * @var string
	 */
	protected $name = 'admin_groups';

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
                'title' => $this->getTranslate($this->name, 'group'),
                'error' => 'Required',
                'required' => true,
            ]))
            ->add(new FlagsElement('flags', $this->group->flags, [
                'title' => $this->getTranslate($this->name, 'flags'),
                'error' => 'Required',
                'required' => true,
            ]))
            ->add(new NumberElement('priority', $this->group->priority, [
                'title' => $this->getTranslate($this->name, 'priority'),
                'required' => false,
            ]));
		
		$this->form->getValidator()
            ->set('title', true)
            ->set('flags', true, [
                new FlagsRule()
            ])
			->set('priority', true, [
			    new NumberRule(0)
            ]);
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
