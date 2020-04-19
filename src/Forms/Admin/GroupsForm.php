<?php

namespace GameX\Forms\Admin;

use \GameX\Core\BaseForm;
use \GameX\Models\Group;
use \GameX\Models\Access;
use \GameX\Core\Forms\Elements\Text;
use \GameX\Core\Forms\Elements\Number as NumberElement;
use \GameX\Core\Forms\Elements\Flags as FlagsElement;
use \GameX\Core\Forms\Elements\ArrayCheckbox;
use \GameX\Core\Validate\Validator;
use \GameX\Core\Validate\Rules\Number as NumberRule;
use \GameX\Core\Validate\Rules\Flags as FlagsRule;
use \GameX\Core\Validate\Rules\Length;
use \GameX\Core\Validate\Rules\ArrayRule;
use \GameX\Core\Validate\Rules\ArrayBoolean;

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
            ->add(new NumberElement('immunity', $this->group->immunity, [
                'title' => $this->getTranslate($this->name, 'immunity'),
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
	        ->set('immunity', true, [
		        new NumberRule(0, 100)
	        ])
            ->set('prefix', false, [
		        new Length(1, 64)
	        ]);

        $access = $this->group->access->map(function (Access $access) {
            return $access->id;
        })->all();

	    $accessList = $this->group->server->access->mapWithKeys(function (Access $access) {
	        return [$access->id => $access->description];
        })->all();

	    $this->form->add(new ArrayCheckbox('access', $access, $accessList));

	    $this->form->getValidator()
		    ->set('access', false, [
			    new ArrayRule(),
			    new ArrayBoolean(),
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
        $this->group->title = $this->form->getValue('title');
        $this->group->flags = $this->form->get('flags')->getFlagsInt();
	    $this->group->prefix = $this->form->getValue('prefix');
	    $this->group->immunity = (int)$this->form->getValue('immunity');

	    $access = array_keys(array_filter($this->form->getValue('access')));
	    $this->group->access()->sync($access);
        return $this->group->save();
    }
}
