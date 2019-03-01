<?php

namespace GameX\Core;

use \Psr\Container\ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface;
use \GameX\Core\Auth\Permissions;
use \GameX\Core\Auth\Models\UserModel;
use \Slim\Views\Twig;
use \GameX\Core\Menu\Menu;
use \GameX\Core\Menu\MenuItem;
use \GameX\Core\Forms\Form;
use \GameX\Core\Exceptions\ValidationException;
use \GameX\Core\Exceptions\FormException;
use \GameX\Core\Exceptions\RedirectException;

abstract class BaseMainController extends BaseController
{
    
    /**
     * @var UserModel
     */
    protected $user = null;
    
    /**
     * @return string
     */
    abstract protected function getActiveMenu();
    
    /**
     * BaseController constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->initMenu();
    }
    
    /**
     * @return UserModel
     */
    public function getUser()
    {
        if ($this->user === null) {
            $this->user = $this->getContainer('auth')->getUser();
        }
        return $this->user;
    }
    
    /**
     * @param string $name
     * @return Form
     */
    public function createForm($name)
    {
        return $this->getContainer('form')->createForm($name);
    }
    
    /**
     * @param string $message
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function addErrorMessage($message)
    {
        $this->getContainer('flash')->addMessage('error', $message);
    }
    
    /**
     * @param string $message
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function addSuccessMessage($message)
    {
        $this->getContainer('flash')->addMessage('success', $message);
    }
    
    /**
     * Menu initialization
     */
    protected function initMenu()
    {
        /** @var Twig $view */
        $view = $this->getContainer('view');
        
        /** @var \GameX\Core\Lang\Language $lang */
        $lang = $this->getContainer('lang');
        
        $menu = new Menu();
        $menu->setActiveRoute($this->getActiveMenu())->add(new MenuItem($lang->format('labels', 'index'), 'index', [],
                null))->add(new MenuItem($lang->format('labels', 'punishments'), 'punishments', [], null));
        
        $modules = $this->getContainer('modules');
        /** @var \GameX\Core\Module\ModuleInterface $module */
        foreach ($modules as $module) {
            $items = $module->getMenuItems();
            foreach ($items as $item) {
                $menu->add($item);
            }
        }
        
        $view->getEnvironment()->addGlobal('menu', $menu);
    }
    
    /**
     * @param ServerRequestInterface $request
     * @param BaseForm $form
     * @param bool $withTransaction
     * @return bool
     * @throws RedirectException
     */
    protected function processForm(ServerRequestInterface $request, BaseForm $form, $withTransaction = false)
    {
        /** @var \Illuminate\Database\Connection|null $connection */
        $connection = $withTransaction ? $this->getContainer('db')->getConnection() : null;
        
        try {
            $form->create();
            
            if ($withTransaction) {
                $connection->beginTransaction();
            }
            
            $form->process($request);
            $success = $form->getIsSubmitted() && $form->getIsValid();
            if ($withTransaction) {
                $connection->commit();
            }
            
            return $success;
        } catch (FormException $e) {
            if ($withTransaction) {
                $connection->rollBack();
            }
            $form->getForm()->setError($e->getField(), $e->getMessage());
            $form->getForm()->saveValues();
            throw new RedirectException($form->getForm()->getAction(), 302);
        } catch (ValidationException $e) {
            if ($withTransaction) {
                $connection->rollBack();
            }
            if ($e->hasMessage()) {
                $this->addErrorMessage($e->getMessage());
            }
            $form->getForm()->saveValues();
            throw new RedirectException($form->getForm()->getAction(), 302);
        }
    }
    
    /**
     * @return Permissions
     */
    protected function getPermissions()
    {
        return $this->container->get('permissions');
    }

    /**
     * @return Twig
     */
    protected function getView()
    {
        return $this->container->get('view');
    }
}
