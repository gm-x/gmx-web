<?php

namespace GameX\Controllers;

use \GameX\Core\BaseMainController;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Core\Jobs\JobHelper;
use \GameX\Core\Auth\Helpers\AuthHelper;
use \GameX\Forms\User\LoginForm;
use \GameX\Forms\User\RegisterForm;
use \GameX\Forms\User\ActivationForm;
use \GameX\Forms\User\ResetPasswordForm;
use \GameX\Forms\User\ResetPasswordCompleteForm;
use \GameX\Core\Exceptions\NotAllowedException;
use \GameX\Core\Exceptions\RedirectException;

class UserController extends BaseMainController
{
    
    /**
     * @var bool
     */
    protected $mailEnabled = false;
    
    /**
     * @return string
     */
    protected function getActiveMenu()
    {
        return 'index';
    }
    
    /**
     * Init UserController
     */
    public function init()
    {
        /** @var \GameX\Core\Configuration\Config $preferences */
        $preferences = $this->getContainer('preferences');
        $this->mailEnabled = (bool)$preferences->getNode('mail')->get('enabled', false);
    }
    
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     * @throws RedirectException
     */
    public function registerAction(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $authHelper = new AuthHelper($this->container);
        $form = new RegisterForm($authHelper, !$this->mailEnabled);
        if ($this->processForm($request, $form, true)) {
            if ($this->mailEnabled) {
                $user = $form->getUser();
                $activationCode = $authHelper->getActivationCode($user);
                JobHelper::createTask('sendmail', [
                    'type' => 'activation',
                    'user' => $user->login,
                    'email' => $user->email,
                    'title' => 'Activation',
                    'params' => [
                        'link' => $this->pathFor('activation', ['code' => $activationCode], [], true)
                    ],
                ]);
            }
            if ($this->mailEnabled) {
                $this->addSuccessMessage($this->getTranslate('user', 'registered_email'));
                return $this->redirect('index');
            } else {
                $this->addSuccessMessage($this->getTranslate('user', 'registered'));
                return $this->redirect('login');
            }
        }
        
        return $this->render('user/register.twig', [
            'form' => $form->getForm(),
        ]);
    }
    
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     * @throws NotAllowedException
     * @throws RedirectException
     */
    public function activateAction(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        if (!$this->mailEnabled) {
            throw new NotAllowedException();
        }
        
        $authHelper = new AuthHelper($this->container);
        $form = new ActivationForm($authHelper, $args['code']);
        if ($this->processForm($request, $form, true)) {
            $this->addSuccessMessage($this->getTranslate('user', 'activated'));
            return $this->redirect('login');
        }
        
        return $this->render('user/activation.twig', [
            'form' => $form->getForm(),
        ]);
    }
    
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     * @throws RedirectException
     */
    public function loginAction(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $form = new LoginForm(new AuthHelper($this->container));
        if ($this->processForm($request, $form, true)) {
            return $this->redirect('index');
        }
        
        return $this->render('user/login.twig', [
            'form' => $form->getForm(),
            'enabledEmail' => $this->mailEnabled
        ]);
    }
    
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function logoutAction(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $authHelper = new AuthHelper($this->container);
        $authHelper->logoutUser();
        return $this->redirect('index');
    }
    
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     * @throws NotAllowedException
     * @throws RedirectException
     */
    public function resetPasswordAction(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        if (!$this->mailEnabled) {
            throw new NotAllowedException();
        }
        
        $authHelper = new AuthHelper($this->container);
        $form = new ResetPasswordForm($authHelper);
        if ($this->processForm($request, $form, true)) {
            JobHelper::createTask('sendmail', [
                'type' => 'reset_password',
                'user' => $form->getUser()->login,
                'email' => $form->getUser()->email,
                'title' => 'Reset Password',
                'params' => [
                    'link' => $this->pathFor('reset_password_complete', ['code' => $form->getCode()], [], true)
                ],
            ]);
            $this->addSuccessMessage($this->getTranslate('user', 'reset_password_sent'));
            return $this->redirect('index');
        }
        
        $this->render('user/reset_password.twig', [
            'form' => $form->getForm()
        ]);
    }
    
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     * @throws NotAllowedException
     * @throws RedirectException
     */
    public function resetPasswordCompleteAction(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ) {
        if (!$this->mailEnabled) {
            throw new NotAllowedException();
        }
        
        $form = new ResetPasswordCompleteForm(new AuthHelper($this->container), $args['code']);
        if ($this->processForm($request, $form, true)) {
            $this->addSuccessMessage($this->getTranslate('user', 'reset_password_done'));
            return $this->redirect('login');
        }
        
        return $this->render('user/reset_password_complete.twig', [
            'form' => $form->getForm(),
        ]);
    }
}
