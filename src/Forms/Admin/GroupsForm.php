<?php

namespace GameX\Forms\Admin;

use \GameX\Core\BaseForm;
use \GameX\Models\Group;
use \GameX\Core\Forms\Elements\Text;
use \GameX\Core\Forms\Elements\Flags as FlagsElement;
use \GameX\Core\Validate\Rules\Flags as FlagsRule;

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
            ]))->add(new FlagsElement('flags', $this->group->flags, [
                'title' => $this->getTranslate($this->name, 'flags'),
                'error' => 'Required',
                'required' => true,
            ]));

        $this->form->getValidator()->set('title', true)->set('flags', true, [
                new FlagsRule()
            ]);
    }
    
    /**
     * @return boolean
     */
    protected function processForm()
    {
        $this->group->title = $this->form->get('title')->getValue();
        $this->group->flags = $this->form->get('flags')->getFlagsInt();
        return $this->group->save();
    }
}
