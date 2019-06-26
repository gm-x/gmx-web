<?php

namespace GameX\Forms\Admin;

use \GameX\Core\BaseForm;
use \GameX\Models\Group;
use \GameX\Core\Forms\Elements\Text;
use \GameX\Core\Forms\Elements\Flags as FlagsElement;
use \GameX\Core\Validate\Rules\Flags as FlagsRule;
use \GameX\Core\Validate\Rules\Length;

class GroupsForm extends BaseForm
{

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
    public function __construct(Group $group)
    {
        $this->group = $group;
    }

    /**
     * @noreturn
     */
    protected function createForm()
    {
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
	        ->add(new Text('prefix', $this->group->prefix, [
		        'title' => $this->getTranslate($this->name, 'prefix'),
		        'required' => false,
	        ]));

        $this->form->getValidator()
	        ->set('title', true)
	        ->set('flags', true, [
                new FlagsRule()
            ])
	        ->set('prefix', false, [
		        new Length(1, 64)
	        ]);
    }

	/**
	 * @return bool
	 * @throws \Exception
	 */
    protected function processForm()
    {
        $this->group->title = $this->form->get('title')->getValue();
        $this->group->flags = $this->form->get('flags')->getFlagsInt();
	    $this->group->prefix = $this->form->getValue('prefix');
        return $this->group->save();
    }
}
