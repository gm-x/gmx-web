<?php

namespace GameX\Forms\Admin;

use \GameX\Core\BaseForm;
use \GameX\Models\Access;
use \GameX\Core\Forms\Elements\Text;
use \GameX\Core\Validate\Rules\Length;
use \GameX\Core\Validate\Rules\Callback;

class AccessForm extends BaseForm
{

    /**
     * @var string
     */
    protected $name = 'admin_reasons';

    /**
     * @var Access
     */
    protected $access;

    /**
     * @param Access $reason
     */
    public function __construct(Access $access)
    {
        $this->access = $access;
    }
    
    /**
     * @param mixed $value
     * @return mixed|null
     */
    public function checkExists($value)
    {
        return !Access::where([
            'server_id' => $this->access->server_id,
            'key' => $value
        ])->exists() ? $value : null;
    }
    
    /**
     * @noreturn
     */
    protected function createForm()
    {
        $this->form
	        ->add(new Text('key', $this->access->key, [
                'title' => $this->getTranslate($this->name, 'key'),
                'error' => 'Required',
                'required' => true,
            ]))
	        ->add(new Text('title', $this->access->description, [
		        'title' => $this->getTranslate($this->name, 'description'),
		        'error' => 'Required',
		        'required' => true,
	        ]));

        $validator = $this->form->getValidator();
        $validator
	        ->set('key', true, [
	        	new Length(1, 64)
	        ])
	        ->set('description', true);

        if (!$this->access->exists) {
            $validator->add('key',
                new Callback([$this, 'checkExists'], $this->getTranslate($this->name, 'exists')));
        }
    }
    
    /**
     * @return boolean
     */
    protected function processForm()
    {
        $this->access->title = $this->form->getValue('title');
        $this->access->time = !$this->form->getValue('time_enabled') ? $this->form->getValue('time') : null;
        $this->access->overall = $this->form->getValue('overall') ? 1 : 0;
        $this->access->menu = $this->form->getValue('menu') ? 1 : 0;
        $this->access->active = $this->form->getValue('active') ? 1 : 0;
        return $this->access->save();
    }
}
