<?php
namespace GameX\Controllers;

use \GameX\Core\BaseMainController;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Core\Jobs\JobHelper;
use \GameX\Core\Auth\Helpers\AuthHelper;
use \GameX\Core\Auth\Models\UserModel;
use \GameX\Forms\User\LoginForm;
use \GameX\Forms\User\RegisterForm;
use \GameX\Forms\User\ActivationForm;
use \GameX\Forms\User\ResetPasswordForm;
use \GameX\Forms\User\ResetPasswordCompleteForm;
use \GameX\Core\Exceptions\NotAllowedException;
use \GameX\Core\Exceptions\FormException;
use \GameX\Core\Exceptions\ValidationException;

class UserController extends BaseMainController {
    
    /**
     * @var bool
     */
    protected $mailEnabled = false;

	/**
	 * @return string
	 */
	protected function getActiveMenu() {
		return 'index';
	}
    
    /**
     * Init UserController
     */
	public function init() {
        $this->mailEnabled = (bool) $this->getConfig('mail')->get('enabled', false);
    }
    
    /**
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
	 * @param array $args
	 * @return ResponseInterface
	 */
    public function registerAction(ServerRequestInterface $request, ResponseInterface $response, array $args) {
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
            return $this->redirect('login');
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
	 */
    public function activateAction(ServerRequestInterface $request, ResponseInterface $response, array $args) {
		if (!$this->mailEnabled) {
			throw new NotAllowedException();
		}

		$authHelper = new AuthHelper($this->container);
		$form = new ActivationForm($authHelper, $args['code']);
		if ($this->processForm($request, $form, true)) {
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
	 */
    public function loginAction(ServerRequestInterface $request, ResponseInterface $response, array $args) {
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
    public function logoutAction(ServerRequestInterface $request, ResponseInterface $response, array $args) {
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
	 */
	public function resetPasswordAction(ServerRequestInterface $request, ResponseInterface $response, array $args) {
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
	 */
	public function resetPasswordCompleteAction(ServerRequestInterface $request, ResponseInterface $response, array $args) {
        if (!$this->mailEnabled) {
            throw new NotAllowedException();
        }

		$form = new ResetPasswordCompleteForm(new AuthHelper($this->container), $args['code']);
        if ($this->processForm($request, $form, true)) {
            return $this->redirect('login');
        }

        return $this->render('user/reset_password_complete.twig', [
            'form' => $form->getForm(),
        ]);
    }
}
