<?php

namespace GameX\Forms\Admin\Preferences;

use \GameX\Core\BaseForm;
use \DirectoryIterator;
use \GameX\Core\Forms\Elements\Checkbox;
use \GameX\Core\Validate\Rules\Boolean;

class CacheForm extends BaseForm
{
    
    /**
     * @var string
     */
    protected $name = 'admin_preferences_cache';
    
    /**
     * @var string[]
     */
    protected $dirs;

	/**
	 * @var bool
	 */
	protected $hasAccessToEdit;
    
    /**
     * @param string[] $dirs
     * @param bool $hasAccessToEdit
     */
    public function __construct(array $dirs, $hasAccessToEdit = true)
    {
        $this->dirs = $dirs;
        $this->hasAccessToEdit = $hasAccessToEdit;
    }
    
    /**
     * @noreturn
     */
    protected function createForm()
    {
        $this->form->add(new Checkbox('accept', false, [
            'title' => $this->getTranslate('admin_preferences', 'cache_accept'),
            'required' => false,
	        'disabled' => !$this->hasAccessToEdit,
        ]));
        
        $this->form->getValidator()->set('accept', false, [
            new Boolean()
        ]);
    }
    
    /**
     * @return bool
     * @throws \Exception
     */
    protected function processForm()
    {
        if ($this->form->getValue('accept')) {
            foreach ($this->dirs as $dir) {
                $this->rmDir($dir);
            }
        }
        return true;
    }
    
    protected function rmDir($dir)
    {
        $i = new DirectoryIterator($dir);
        foreach($i as $f) {
            if($f->isFile()) {
                unlink($f->getRealPath());
            } else if(!$f->isDot() && $f->isDir()) {
                $this->rmDir($f->getRealPath());
            }
        }
    }
}
