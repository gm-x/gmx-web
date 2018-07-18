<?php
namespace GameX\Controllers;

use \GameX\Core\BaseMainController;
use GameX\Forms\User\ActivationForm;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Forms\User\LoginForm;
use \GameX\Forms\User\RegisterForm;
use \GameX\Core\Jobs\JobHelper;
use \GameX\Core\Auth\Helpers\AuthHelper;
use \GameX\Core\Auth\Models\UserModel;
use \GameX\Core\Forms\Form;
use \GameX\Core\Forms\Elements\Password;
use \GameX\Core\Forms\Elements\Text;
use \GameX\Core\Exceptions\NotAllowedException;
use \GameX\Core\Exceptions\FormException;
use \GameX\Core\Exceptions\ValidationException;
use \Exception;

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

    public function logoutAction(RequestInterface $request, ResponseInterface $response, array $args) {
		$authHelper = new AuthHelper($this->container);
		$authHelper->logoutUser();
    	return $this->redirect('index');
	}

	public function resetPasswordAction(RequestInterface $request, ResponseInterface $response, array $args) {
        $enabledEmail = (bool) $this->getConfig('mail')->get('enabled', false);
        if (!$enabledEmail) {
            throw new NotAllowedException();
        }

		$form = $this->createForm('reset_password')
            ->setAction($request->getUri())
			->add(new Text('login', '', [
				'title' => $this->getTranslate('inputs', 'login_email'),
				'error' => 'Required',
				'required' => true,
			]))
			->setRules('login', ['required', 'trim', 'min_length' => 1])
            ->processRequest($request);

		if ($form->getIsSubmitted()) {
			if (!$form->getIsValid()) {
                return $this->redirectTo($form->getAction());
			} else {
				try {
					$authHelper = new AuthHelper($this->container);
					$user = $authHelper->findUser($form->getValue('login'));
					if (!$user) {
						throw new FormException('login', 'User not found');
					}
                    $reminderCode = $authHelper->resetPassword($user);
                    JobHelper::createTask('sendmail', [
                        'type' => 'reset_password',
                        'user' => $user->login,
                        'email' => $user->email,
                        'title' => 'Reset Password',
                        'params' => [
                            'link' => $this->pathFor('reset_password_complete', ['code' => $reminderCode], [], true)
                        ],
                    ]);
					return $this->redirect('index');
				} catch (Exception $e) {
					return $this->failRedirect($e, $form);
				}
			}
		}

		$this->render('user/reset_password.twig', [
			'form' => $form
		]);
	}

	public function resetPasswordCompleteAction(RequestInterface $request, ResponseInterface $response, array $args) {
        $enabledEmail = (bool) $this->getConfig('mail')->get('enabled', false);
        if (!$enabledEmail) {
            throw new NotAllowedException();
        }
        $code = $args['code'];
        $passwordValidator = function($confirmation, $form) {
			return $form->password === $confirmation;
		};

        $form = $this->createForm('reset_password_complete')
            ->setAction($request->getUri())
			->add(new Text('login', '', [
				'title' => $this->getTranslate('inputs', 'login_email'),
				'error' => 'Required',
				'required' => true,
			]))
			->add(new Password('password', '', [
				'title' => $this->getTranslate('inputs', 'password'),
				'error' => $this->getTranslate('labels', 'required'),
				'required' => true,
			]))
			->add(new Password('password_repeat', '', [
				'title' => $this->getTranslate('inputs', 'password_repeat'),
				'error' => 'Passwords does not match',
				'required' => true,
			]))
			->setRules('login', ['required', 'trim', 'min_length' => 1])
			->setRules('password', ['required', 'trim', 'min_length' => 6])
			->setRules('password_repeat', ['required', 'trim', 'min_length' => 6, 'identical' => $passwordValidator])
            ->processRequest($request);

        if ($form->getIsSubmitted()) {
            if (!$form->getIsValid()) {
                return $this->redirectTo($form->getAction());
            } else {
                try {
                    $authHelper = new AuthHelper($this->container);
					$user = $authHelper->findUser($form->getValue('login'));
					if (!$user) {
						throw new FormException('login', 'User not found');
					}
                    $authHelper->resetPasswordComplete($user, $form->getValue('password'), $code);
                    return $this->redirect('login');
                } catch (Exception $e) {
                    return $this->failRedirect($e, $form);
                }
            }
        }

        return $this->render('user/reset_password_complete.twig', [
            'form' => $form,
        ]);
    }
}
