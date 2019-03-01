<?php

namespace GameX\Controllers\Admin;

use GameX\Core\Auth\Helpers\AuthHelper;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \Cartalyst\Sentinel\Users\UserRepositoryInterface;
use \GameX\Core\BaseAdminController;
use \GameX\Constants\Admin\UsersConstants;
use \GameX\Core\Auth\Models\UserModel;
use \GameX\Forms\Admin\Users\RoleForm;
use \GameX\Forms\Admin\Users\EditForm;
use \GameX\Core\Auth\Helpers\RoleHelper;
use \GameX\Core\Pagination\Pagination;
use \Slim\Exception\NotFoundException;

class UsersController extends BaseAdminController
{
    
    /**
     * @return string
     */
    protected function getActiveMenu()
    {
        return UsersConstants::ROUTE_LIST;
    }
    
    /** @var  UserRepositoryInterface */
    protected $userRepository;
    
    public function init()
    {
        $this->userRepository = $this->getContainer('auth')->getUserRepository();
    }
    
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function indexAction(ServerRequestInterface $request, ResponseInterface $response, array $args = [])
    {
        $pagination = new Pagination($this->userRepository->get(), $request);
        return $this->getView()->render($response, 'admin/users/index.twig', [
            'users' => $pagination->getCollection(),
            'pagination' => $pagination,
        ]);
    }
    
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     * @throws NotFoundException
     */
    public function viewAction(ServerRequestInterface $request, ResponseInterface $response, array $args = [])
    {
        $user = $this->getUserFromRequest($request, $response, $args);
        return $this->getView()->render($response, 'admin/users/view.twig', [
            'user' => $user
        ]);
    }
    
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     * @throws NotFoundException
     * @throws \GameX\Core\Exceptions\RedirectException
     */
    public function editAction(ServerRequestInterface $request, ResponseInterface $response, array $args = [])
    {
        $user = $this->getUserFromRequest($request, $response, $args);
    
        $editForm = new EditForm($user, new RoleHelper($this->container));
        if ($this->processForm($request, $editForm, true)) {
            $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
            return $this->redirect(UsersConstants::ROUTE_VIEW, [
                'user' => $user->id,
            ]);
        }
        
        return $this->getView()->render($response, 'admin/users/form.twig', [
            'user' => $user,
            'editForm' => $editForm->getForm(),
        ]);
    }
    
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     * @throws NotFoundException
     */
    public function activateAction(ServerRequestInterface $request, ResponseInterface $response, array $args = [])
    {
        $user = $this->getUserFromRequest($request, $response, $args);
        $authHelper = new AuthHelper($this->container);
        if ($authHelper->checkActivationCompleted($user)) {
            return $response->withJson([
                'success' => true,
            ]);
        }
        $success = (bool)$authHelper->activateUserWithoutCode($user);
        return $response->withJson([
            'success' =>  $success,
        ]);
    }
    
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return UserModel
     * @throws NotFoundException
     */
    protected function getUserFromRequest(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $user = $this->userRepository->findById($args['user']);
        if (!$user) {
            throw new NotFoundException($request, $response);
        }
        
        return $user;
    }
}
