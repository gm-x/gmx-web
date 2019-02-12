<?php

namespace GameX\Controllers\Admin;

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
        return $this->render('admin/users/index.twig', [
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
        return $this->render('admin/users/view.twig', [
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
        
        $roleForm = new RoleForm($user, new RoleHelper($this->container));
        if ($this->processForm($request, $roleForm)) {
            $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
            return $this->redirect(UsersConstants::ROUTE_VIEW, [
                'user' => $user->id,
            ]);
        }
    
        $editForm = new EditForm($user);
        if ($this->processForm($request, $editForm, true)) {
            $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
            return $this->redirect(UsersConstants::ROUTE_VIEW, [
                'user' => $user->id,
            ]);
        }
        
        return $this->render('admin/users/form.twig', [
            'user' => $user,
            'roleForm' => $roleForm->getForm(),
            'editForm' => $editForm->getForm(),
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
