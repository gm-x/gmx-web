<?php
namespace GameX\Forms\Admin;

use \GameX\Core\BaseForm;
use \GameX\Core\Forms\Form;
use \GameX\Models\Server;
use \GameX\Models\Privilege;
use \GameX\Models\Group;
use \GameX\Core\Forms\Elements\Text;
use \GameX\Core\Forms\Elements\Select;
use \GameX\Core\Forms\Elements\Date as DateElement;
use \GameX\Core\Forms\Elements\Checkbox;
use \GameX\Core\Forms\Rules\Required;
use \GameX\Core\Forms\Rules\Trim;
use \GameX\Core\Forms\Rules\InArray;
use \GameX\Core\Forms\Rules\Boolean;
use \GameX\Core\Forms\Rules\Number;
use \GameX\Core\Forms\Rules\Date as DateRule;
use \GameX\Core\Forms\Rules\Callback;

class PrivilegesForm extends BaseForm {

	/**
	 * @var string
	 */
	protected $name = 'admin_privileges';

	/**
	 * @var Privilege
	 */
	protected $privilege;

	/**
	 * @param Privilege $privilege
	 */
	public function __construct(Privilege $privilege) {
		$this->privilege = $privilege;
	}
    
    /**
     * @param Form $form
     * @param string $key
     * @return bool
     */
    public function checkExists(Form $form, $key) {
        return Group::where('id', $form->getValue($key))->exists();
    }

	/**
	 * @noreturn
	 */
	protected function createForm() {
        $server = $this->privilege->exists
            ? $this->privilege->group->server
            : Server::first();
        
        $servers = $this->getServers();
        $groups = $this->getGroups($server);
        
		$this->form
            ->add(new Select('server', $server->id, $servers, [
                'id' => 'input_admin_server',
                'title' => 'Server',
                'required' => true,
                'empty_option' => 'Choose server',
            ]))
            ->add(new Select('group', $this->privilege->group_id, $groups, [
                'id' => 'input_player_group',
                'title' => 'Group',
                'required' => true,
                'empty_option' => 'Choose group',
            ]))
            ->add(new Text('prefix', $this->privilege->prefix, [
                'title' => 'Prefix',
            ]))
            ->add(new DateElement('expired', $this->privilege->expired_at, [
                'title' => 'Expired',
                'required' => true,
            ]))
            ->add(new Checkbox('active', $this->privilege->active ? true : false, [
                'title' => 'Active',
            ]))
            ->addRule('server', new Required())
            ->addRule('server', new Number(1))
            ->addRule('server', new InArray(array_keys($servers)))
            ->addRule('group', new Required())
            ->addRule('group', new Number(1))
            ->addRule('group', new Callback([$this, 'checkExists'], 'Group doesn\'t exists'))
            ->addRule('prefix', new Trim())
            ->addRule('expired', new Required())
            ->addRule('expired', new DateRule())
            ->addRule('active', new Boolean());
	}
    
    /**
     * @return boolean
     */
    protected function processForm() {
        $this->privilege->group_id = $this->form->get('group')->getValue();
        $this->privilege->prefix = $this->form->get('prefix')->getValue();
        $this->privilege->expired_at = $this->form->get('expired')->getDate();
        $this->privilege->active = $this->form->get('active')->getValue() ? 1 : 0;
        return $this->privilege->save();
    }
    
    /**
     * @return array
     */
    protected function getServers() {
        $servers = [];
        /** @var Server $server */
        foreach (Server::all() as $server) {
            $servers[$server->id] = $server->name;
        }
        return $servers;
    }
    
    /**
     * @param Server $server
     * @return array
     */
    protected function getGroups(Server $server) {
        $groups = [];
        foreach ($server->groups as $group) {
            $groups[$group->id] = $group->title;
        }
        return $groups;
    }
}
