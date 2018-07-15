<?php
namespace GameX\Core;

use \Psr\Container\ContainerInterface;
use \GameX\Core\Auth\Models\UserModel;
use \Slim\Views\Twig;
use \GameX\Core\Menu\Menu;
use \GameX\Core\Menu\MenuItem;
use \GameX\Core\Forms\Form;
use \GameX\Core\Exceptions\ValidationException;
use \GameX\Core\Exceptions\FormException;
use \Exception;

abstract class BaseMainController extends BaseController {

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
    public function __construct(ContainerInterface $container) {
        parent::__construct($container);
		$this->initMenu();
    }

	/**
	 * @return UserModel
	 */
    public function getUser() {
    	if ($this->user === null) {
    		$this->user = $this->getContainer('auth')->getUser();
		}
    	return $this->user;
	}

    /**
     * @param string $template
     * @param array $data
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function render($template, array $data = []) {
        /** @var Twig $view */
        $view = $this->getContainer('view');
        return $view->render($this->getContainer('response'), $template, $data);
    }

    /**
     * @param string $message
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function addErrorMessage($message) {
        $this->getContainer('flash')->addMessage('error', $message);
    }

    /**
     * @param string $message
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function addSuccessMessage($message) {
        $this->getContainer('flash')->addMessage('success', $message);
    }

    protected function failRedirect(Exception $e, Form $form) {
        if ($e instanceof FormException) {
            $form->setError($e->getField(), $e->getMessage());
        } elseif ($e instanceof ValidationException) {
            $this->addErrorMessage($e->getMessage());
        } else {
            $this->addErrorMessage('Something wrong. Please Try again later.');
        }

        $form->saveValues();

        /** @var \Monolog\Logger $logger */
        $logger = $this->getContainer('log');
        $logger->error((string) $e);

        return $this->redirectTo($form->getAction());
    }

	protected function initMenu() {
		/** @var Twig $view */
		$view = $this->getContainer('view');

        /** @var \GameX\Core\Lang\Language $lang */
        $lang = $this->getContainer('lang');

		$menu = new Menu();

		$menu
			->setActiveRoute($this->getActiveMenu())
			->add(new MenuItem($lang->format('labels',  'index'), 'index', [], null))
			->add(new MenuItem($lang->format('labels',  'punishments'), 'punishments', [], null));

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
}
