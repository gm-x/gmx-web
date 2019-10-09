<?php

namespace GameX\Forms\Admin;

use \GameX\Core\BaseForm;
use \GameX\Models\Group;
use \GameX\Models\Access;
use \GameX\Core\Forms\Elements\Text;
use \GameX\Core\Forms\Elements\Flags as FlagsElement;
use \GameX\Core\Validate\Validator;
use \GameX\Core\Validate\Rules\Flags as FlagsRule;
use \GameX\Core\Validate\Rules\Length;
use \GameX\Core\Forms\Elements\Checkbox;
use \GameX\Core\Validate\Rules\ArrayCallback;
use \GameX\Core\Validate\Rules\ArrayRule;

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

	    $access = [];
	    /** @var Access $row */
	    foreach ($this->group->server->access as $row) {
		    $access[$row->key] = $row->description;
	    }

	    $this->form->add(new Checkbox('access', $access));
	    $this->form->getValidator()
		    ->set('access', false, [
			    new ArrayRule(),
			    new ArrayCallback(function ($key, $value) use ($access) {
			    	if (!array_key_exists($key, $access)) {
			    		return null;
				    }
				    $value = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
				    return $value !== false ? $value : null;
			    }, '')
		    ], [
			    'check' => Validator::CHECK_IGNORE,
			    'trim' => false
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
