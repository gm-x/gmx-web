<?php

namespace GameX\Forms\Admin\Preferences;

use \GameX\Core\BaseForm;
use \GameX\Core\Forms\Elements\Checkbox;
use \GameX\Core\Validate\Rules\Boolean;
use \GameX\Core\Update\Actions;
use \GameX\Core\Update\Actions\ActionClearDirectory;
use \GameX\Core\Update\Actions\ActionComposerInstall;
use \GameX\Core\Update\Actions\ActionDeleteFile;
use \GameX\Core\Update\Actions\ActionMigrationsRun;
use \GameX\Core\Update\Exceptions\ActionException;
use \GameX\Core\Exceptions\ValidationException;

class CacheForm extends BaseForm
{
    
    /**
     * @var string
     */
    protected $name = 'admin_preferences_cache';
    
    /**
     * @var string
     */
    protected $baseDir;

	/**
	 * @var bool
	 */
	protected $hasAccessToEdit;
    
    /**
     * @param string $baseDir
     * @param bool $hasAccessToEdit
     */
    public function __construct($baseDir, $hasAccessToEdit = true)
    {
        $this->baseDir = $baseDir;
        $this->hasAccessToEdit = $hasAccessToEdit;
    }
    
    /**
     * @noreturn
     */
    protected function createForm()
    {
        $this->form
	        ->add(new Checkbox('cache', false, [
	            'title' => $this->getTranslate('admin_preferences', 'clear_cache'),
	            'required' => false,
		        'disabled' => !$this->hasAccessToEdit,
	        ]))
	        ->add(new Checkbox('dependencies', false, [
		        'title' => $this->getTranslate('admin_preferences', 'update_dependencies'),
		        'required' => false,
		        'disabled' => !$this->hasAccessToEdit,
	        ]))
	        ->add(new Checkbox('migrations', false, [
		        'title' => $this->getTranslate('admin_preferences', 'update_migrations'),
		        'required' => false,
		        'disabled' => !$this->hasAccessToEdit,
	        ]));
        
        $this->form->getValidator()
	        ->set('cache', false, [
	            new Boolean()
	        ])
	        ->set('dependencies', false, [
	            new Boolean()
	        ])
	        ->set('migrations', false, [
	            new Boolean()
	        ]);
    }
    
    /**
     * @return bool
     * @throws \Exception
     */
    protected function processForm()
    {
	    $actions = new Actions();

	    if ($this->form->getValue('cache')) {
		    $actions->add(new ActionClearDirectory($this->baseDir . 'runtime' . DIRECTORY_SEPARATOR . 'cache'));
		    $actions->add(new ActionClearDirectory($this->baseDir . 'runtime' . DIRECTORY_SEPARATOR . 'twig_cache'));
		    $actions->add(new ActionDeleteFile($this->baseDir . 'runtime' . DIRECTORY_SEPARATOR . 'routes.php'));
	    }

	    if ($this->form->getValue('dependencies')) {
		    $actions->add(new ActionComposerInstall($this->baseDir));
	    }

	    if ($this->form->getValue('migrations')) {
		    $actions->add(new ActionMigrationsRun($this->baseDir));
	    }

	    try {
		    $actions->run();
		    return true;
	    } catch (ActionException $e) {
	    	throw new ValidationException($this->getTranslate('admin_preferences', 'cache_error'));
	    }
    }
}
