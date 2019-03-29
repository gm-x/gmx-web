<?php

namespace GameX\Controllers;

use \GameX\Core\BaseMainController;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Core\Auth\Social\SocialAuth;
use \GameX\Constants\IndexConstants;
use \GameX\Constants\PreferencesConstants;
use \GameX\Core\Jobs\JobHelper;
use \GameX\Core\Auth\Helpers\AuthHelper;
use \GameX\Core\Auth\Helpers\SocialHelper;
use \GameX\Forms\User\LoginForm;
use \GameX\Forms\User\RegisterForm;
use \GameX\Forms\User\ActivationForm;
use \GameX\Forms\User\ResetPasswordForm;
use \GameX\Forms\User\ResetPasswordCompleteForm;
use \GameX\Forms\User\SocialForm;
use \GameX\Core\Exceptions\NotAllowedException;
use \GameX\Core\Exceptions\RedirectException;
use \Slim\Exception\NotFoundException;

class UserController extends BaseMainController
{
    
    /**
     * @var bool
     */
    protected $mailEnabled = false;
    
    /**
     * @var bool
     */
    protected $autoActivateUsers = true;
    
    /**
     * @return string
     */
    protected function getActiveMenu()
    {
        return IndexConstants::ROUTE_INDEX;
    }
    
    /**
     * Init UserController
     */
    public function init()
    {
        /** @var \GameX\Core\Configuration\Config $preferences */
        $preferences = $this->getContainer('preferences');
        $this->mailEnabled = (bool)$preferences->getNode('main')->get('enabled', false);
        $this->autoActivateUsers = (bool)$preferences
            ->getNode(PreferencesConstants::CATEGORY_MAIN)
            ->get(PreferencesConstants::MAIN_AUTO_ACTIVATE_USERS, false);
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
        $form = new RegisterForm($authHelper, $this->autoActivateUsers);
        if ($this->processForm($request, $form, true)) {
            if ($this->autoActivateUsers) {
                $this->addSuccessMessage($this->getTranslate('user', 'registered'));
                return $this->redirect('login');
            } elseif ($this->mailEnabled) {
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
                $this->addSuccessMessage($this->getTranslate('user', 'registered_email'));
                return $this->redirect('index');
            } else {
                $this->addSuccessMessage($this->getTranslate('user', 'registered_moderate'));
                return $this->redirect('login');
            }
        }
        
        return $this->getView()->render($response, 'user/register.twig', [
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
        
        return $this->getView()->render($response, 'user/activation.twig', [
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
        $form = new LoginForm(new AuthHelper($this->container), $this->mailEnabled);
        if ($this->processForm($request, $form)) {
            return $this->redirect('index');
        }

        /** @var SocialAuth $social */
        $social = $this->getContainer('social');
        
        return $this->getView()->render($response, 'user/login.twig', [
            'form' => $form->getForm(),
            'enabledEmail' => $this->mailEnabled,
	        'social_providers' => $social->getProviders(),
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
        
        return $this->getView()->render($response, 'user/reset_password.twig', [
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
    public function resetPasswordCompleteAction(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        if (!$this->mailEnabled) {
            throw new NotAllowedException();
        }
        
        $form = new ResetPasswordCompleteForm(new AuthHelper($this->container), $args['code']);
        if ($this->processForm($request, $form, true)) {
            $this->addSuccessMessage($this->getTranslate('user', 'reset_password_done'));
            return $this->redirect('login');
        }
        
        return $this->getView()->render($response, 'user/reset_password_complete.twig', [
            'form' => $form->getForm(),
        ]);
    }

	/**
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
	 * @param array $args
	 * @return ResponseInterface
	 * @throws NotFoundException
	 * @throws RedirectException
	 * @throws \GameX\Core\Configuration\Exceptions\NotFoundException
	 */
	public function socialAction(ServerRequestInterface $request, ResponseInterface $response, array $args)
	{
		/** @var \GameX\Core\Auth\Social\SocialAuth $social */
		$social = $this->getContainer('social');

		$provider = $args['provider'];

		if (!$social->hasProvider($provider)) {
			throw new NotFoundException($request, $response);
		}

		$adapter = $social->getProvider($provider);

		$adapter->authenticate();
		if ($social->isRedirected()) {
			return $this->redirectTo($social->getRedirectUrl());
		}

		$profile = $adapter->getUserProfile();

		$socialHelper = new SocialHelper($this->container);
		$userSocial = $socialHelper->find($provider, $profile);
		if ($userSocial && $userSocial->user) {
			$socialHelper->authenticate($userSocial);
			return $this->redirect(IndexConstants::ROUTE_INDEX);
		}

		/** @var \GameX\Core\Configuration\Config $preferences */
		$preferences = $this->getContainer('preferences');
		$autoActivate = (bool)$preferences
			->getNode(PreferencesConstants::CATEGORY_MAIN)
			->get(PreferencesConstants::MAIN_AUTO_ACTIVATE_USERS, false);
		$mailEnabled = (bool)$preferences->getNode('main')->get('enabled', false);

		$authHelper = new AuthHelper($this->container);
		$form = new SocialForm($provider, $profile, $socialHelper, $authHelper, $autoActivate);
		if ($this->processForm($request, $form, true)) {
			$adapter->disconnect();
			if ($autoActivate) {
				$socialHelper->authenticate($form->getSocialUser());
				$this->addSuccessMessage($this->getTranslate('user', 'registered'));
			} elseif ($mailEnabled) {
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
				$this->addSuccessMessage($this->getTranslate('user', 'registered_email'));
			} else {
				$this->addSuccessMessage($this->getTranslate('user', 'registered_moderate'));
			}

			return $this->redirect(IndexConstants::ROUTE_INDEX);
		}

		return $this->getView()->render($response, 'user/social.twig', [
			'form' => $form->getForm(),
		]);
	}
}
