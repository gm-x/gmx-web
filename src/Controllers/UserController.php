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
	 * @return string
	 */
	protected function getActiveMenu() {
		return 'index';
	}

	/**
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
	 * @param array $args
	 * @return ResponseInterface
	 */
    public function registerAction(ServerRequestInterface $request, ResponseInterface $response, array $args) {
		$emailEnabled = (bool) $this->getConfig('mail')->get('enabled', false);
		$authHelper = new AuthHelper($this->container);
		/** @var \Illuminate\Database\Connection $connection */
		$connection = $this->getContainer('db')->getConnection();
		$form = new RegisterForm($authHelper, $emailEnabled);
		try {
			$form->create();
			$connection->beginTransaction();
			/** @var UserModel|null $user */
			$user = $form->process($request);
			if ($user) {
				if ($emailEnabled) {
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
				$connection->commit();
				return $this->redirect('login');
			}
			$connection->commit();
		} catch (FormException $e) {
			$connection->rollBack();
			$form->getForm()->setError($e->getField(), $e->getMessage());
			return $this->redirectTo($form->getForm()->getAction());
		} catch (ValidationException $e) {
			$connection->rollBack();
			if ($e->hasMessage()) {
				$this->addErrorMessage($e->getMessage());
			}
			return $this->redirectTo($form->getForm()->getAction());
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
        $enabledEmail = (bool) $this->getConfig('mail')->get('enabled', false);
		if (!$enabledEmail) {
			throw new NotAllowedException();
		}

		/** @var \Illuminate\Database\Connection $connection */
		$connection = $this->getContainer('db')->getConnection();
		$authHelper = new AuthHelper($this->container);

		$form = new ActivationForm($authHelper, $args['code']);
		try {
			$form->create();
			$connection->beginTransaction();
			if ($form->process($request)) {
				$connection->commit();
				return $this->redirect('login');
			}
			$connection->commit();
		} catch (FormException $e) {
			$connection->rollBack();
			$form->getForm()->setError($e->getField(), $e->getMessage());
			return $this->redirectTo($form->getForm()->getAction());
		} catch (ValidationException $e) {
			$connection->rollBack();
			if ($e->hasMessage()) {
				$this->addErrorMessage($e->getMessage());
			}
			return $this->redirectTo($form->getForm()->getAction());
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
		$enabledEmail = (bool) $this->getConfig('mail')->get('enabled', false);

        $form = new LoginForm(new AuthHelper($this->container));
		try {
			$form->create();

			if ($form->process($request) === true) {
				return $this->redirect('index');
			}
		} catch (FormException $e) {
			$form->getForm()->setError($e->getField(), $e->getMessage());
			return $this->redirectTo($form->getForm()->getAction());
		} catch (ValidationException $e) {
			if ($e->hasMessage()) {
				$this->addErrorMessage($e->getMessage());
			}
			return $this->redirectTo($form->getForm()->getAction());
		}

		return $this->render('user/login.twig', [
			'form' => $form->getForm(),
			'enabledEmail' => $enabledEmail
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
        $enabledEmail = (bool) $this->getConfig('mail')->get('enabled', false);
        if (!$enabledEmail) {
            throw new NotAllowedException();
        }

		$authHelper = new AuthHelper($this->container);
		/** @var \Illuminate\Database\Connection $connection */
		$connection = $this->getContainer('db')->getConnection();
		$form = new ResetPasswordForm($authHelper);
		try {
			$form->create();
			$connection->beginTransaction();
			$result = $form->process($request);
			if ($result) {
				JobHelper::createTask('sendmail', [
					'type' => 'reset_password',
					'user' => $result['user']->login,
					'email' => $result['user']->email,
					'title' => 'Reset Password',
					'params' => [
						'link' => $this->pathFor('reset_password_complete', ['code' => $result['code']], [], true)
					],
				]);
				$connection->commit();
				return $this->redirect('index');
			}
			$connection->commit();
		} catch (FormException $e) {
			$connection->rollBack();
			$form->getForm()->setError($e->getField(), $e->getMessage());
			return $this->redirectTo($form->getForm()->getAction());
		} catch (ValidationException $e) {
			$connection->rollBack();
			if ($e->hasMessage()) {
				$this->addErrorMessage($e->getMessage());
			}
			return $this->redirectTo($form->getForm()->getAction());
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
        $enabledEmail = (bool) $this->getConfig('mail')->get('enabled', false);
        if (!$enabledEmail) {
            throw new NotAllowedException();
        }

		$form = new ResetPasswordCompleteForm(new AuthHelper($this->container), $args['code']);
		try {
			$form->create();

			if ($form->process($request)) {
				return $this->redirect('login');
			}
		} catch (FormException $e) {
			$form->getForm()->setError($e->getField(), $e->getMessage());
			return $this->redirectTo($form->getForm()->getAction());
		} catch (ValidationException $e) {
			if ($e->hasMessage()) {
				$this->addErrorMessage($e->getMessage());
			}
			return $this->redirectTo($form->getForm()->getAction());
		}

        return $this->render('user/reset_password_complete.twig', [
            'form' => $form->getForm(),
        ]);
    }
}
