<?php

namespace GameX\Forms\Admin;

use \GameX\Core\BaseForm;
use \GameX\Core\Validate\Validator;
use \GameX\Models\Server;
use \GameX\Models\Privilege;
use \GameX\Core\Forms\Elements\Select;
use \GameX\Core\Forms\Elements\Expired as ExpiredElement;
use \GameX\Core\Forms\Elements\Checkbox;
use \GameX\Core\Validate\Rules\InArray;
use \GameX\Core\Validate\Rules\Boolean;
use \GameX\Core\Validate\Rules\Number;
use \GameX\Core\Validate\Rules\Expired as ExpiredRule;
use \GameX\Core\Validate\Rules\Callback;
use \GameX\Core\Exceptions\PrivilegeFormException;

class PrivilegesForm extends BaseForm
{

    /**
     * @var string
     */
    protected $name = 'admin_privileges';

    /**
     * @var Server
     */
    protected $server;

    /**
     * @var Privilege
     */
    protected $privilege;

    /**
     * @param Server $server
     * @param Privilege $privilege
     */
    public function __construct(Server $server, Privilege $privilege)
    {
        $this->server = $server;
        $this->privilege = $privilege;
    }
    
    /**
     * @param mixed $value
     * @param array $values
     * @return mixed|null
     */
    public function checkPrivilegeExists($value, array $values)
    {
        return !Privilege::where([
            'player_id' => $this->privilege->player_id,
            'group_id' => $value,
        ])->exists() ? $value : null;
    }

    /**
     * @noreturn
     */
    protected function createForm()
    {
        $groups = $this->getGroups();
        if (!count($groups)) {
            throw new PrivilegeFormException();
        }

        $this->form
	        ->add(new Select('group', $this->privilege->group_id, $groups, [
                'title' => $this->getTranslate($this->name, 'group'),
                'required' => true,
                'empty_option' => $this->getTranslate($this->name, 'group_empty'),
            ]))
	        ->add(new ExpiredElement('expired', $this->privilege->expired_at, [
		        'id' => 'expired',
                'title' => $this->getTranslate($this->name, 'expired'),
            ]))
	        ->add(new Checkbox('active', !$this->privilege->exists || $this->privilege->active ? true : false, [
                'title' => $this->getTranslate($this->name, 'active'),
            ]));

        $validator = $this->form->getValidator();
        $validator
            ->set('group', true, [
                new Number(1),
                new InArray(array_keys($groups)),
            ])
            ->set('expired', false, [
                new ExpiredRule()
            ], [
                'check' => Validator::CHECK_ARRAY,
                'trim' => false,
                'allow_null' => true,
            ])
            ->set('active', false, [
                new Boolean()
            ]);

        if (!$this->privilege->exists) {
            $validator->add('group', new Callback([$this, 'checkPrivilegeExists'], 'Privilege already exists'));
        }
    }
    
    /**
     * @return bool
     * @throws \Exception
     */
    protected function processForm()
    {
        $this->privilege->group_id = $this->form->getValue('group');
        $this->privilege->expired_at = $this->form->getValue('expired');
        $this->privilege->active = $this->form->getValue('active') ? 1 : 0;
        return $this->privilege->save();
    }
    
    /**
     * @return array
     */
    protected function getGroups()
    {
	    $groups = $this->server->groups()->orderBy('priority')->get();
        $result = [];
        foreach ($groups as $group) {
	        $result[$group->id] = $group->title;
        }
        return $result;
    }
}
