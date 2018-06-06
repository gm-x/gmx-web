<?php
namespace GameX\Controllers;

use \GameX\Core\BaseMainController;
use \GameX\Core\Forms\Elements\FormInputCheckbox;
use \GameX\Core\Forms\Elements\FormInputEmail;
use \GameX\Core\Forms\Elements\FormInputPassword;
use GameX\Core\Jobs\JobHelper;
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Core\Forms\Form;
use \GameX\Core\Auth\Helpers\AuthHelper;
use \Exception;

class UserController extends BaseMainController {

	/**
	 * @return string
	 */
	protected function getActiveMenu() {
		return 'index';
	}

	/**
	 * @param RequestInterface $request
	 * @param ResponseInterface $response
	 * @param array $args
	 * @return ResponseInterface
	 */
    public function registerAction(RequestInterface $request, ResponseInterface $response, array $args) {
        $identical_password_validator = function($confirmation, $form) {
            return $form->password === $confirmation;
        };

        /** @var Form $form */
        $form = $this->getContainer('form')->createForm('register');
        $form
            ->setAction((string)$request->getUri())
			->add(new FormInputEmail('email', '', [
				'title' => $this->getTranslate('inputs', 'email'),
				'error' => 'Must be valid email',
				'required' => true,
			]))
            ->add(new FormInputPassword('password', '', [
                'title' => $this->getTranslate('inputs', 'password'),
                'error' => $this->getTranslate('labels', 'required'),
                'required' => true,
			]))
            ->add(new FormInputPassword('password_repeat', '', [
                'title' => $this->getTranslate('inputs', 'password_repeat'),
                'error' => 'Passwords does not match',
                'required' => true,
            ]))
			->setRules('email', ['required', 'trim', 'email', 'min_length' => 1])
			->setRules('password', ['required', 'trim', 'min_length' => 6])
			->setRules('password_repeat', ['required', 'trim', 'min_length' => 6, 'identical' => $identical_password_validator])
            ->processRequest($request);

        if ($form->getIsSubmitted()) {
            if (!$form->getIsValid()) {
                return $this->redirectTo($form->getAction());
            } else {
                try {
                    /** @var \Illuminate\Database\Connection $connection */
                    $connection = $this->getContainer('db')->getConnection();
                    $connection->beginTransaction();
                	$authHelper = new AuthHelper($this->container);
                    $activationCode = $authHelper->registerUser(
                        $form->getValue('email'),
                        $form->getValue('password'),
                        $form->getValue('password_repeat')
                    );
					JobHelper::createTask('sendmail', [
					    'type' => 'activation',
                        'email' => $form->getValue('email'),
                        'title' => 'Activation',
                        'params' => [
                            'link' => $this->pathFor('activation', ['code' => $activationCode], [], true)
                        ],
                    ]);
					$connection->commit();
                    return $this->redirect('login');
                } catch (Exception $e) {
                    $connection->rollBack();
                    return $this->failRedirect($e, $form);
                }
            }
        }

        return $this->render('user/register.twig', [
            'form' => $form,
        ]);
    }

    public function activateAction(RequestInterface $request, ResponseInterface $response, array $args) {
    	$code = $args['code'];
        /** @var Form $form */
        $form = $this->getContainer('form')->createForm('activation');
		$form
            ->setAction((string)$request->getUri())
			->add(new FormInputEmail('email', '', [
				'title' => $this->getTranslate('inputs', 'email'),
				'error' => 'Must be valid email',
				'required' => true,
			]))
			->setRules('email', ['required', 'trim', 'email', 'min_length' => 1])
            ->processRequest($request);

		if ($form->getIsSubmitted()) {
			if (!$form->getIsValid()) {
				return $this->redirectTo($form->getAction());
			} else {
				try {
					$authHelper = new AuthHelper($this->container);
					$authHelper->activateUser($form->getValue('email'), $code);
					return $this->redirect('login');
				} catch (Exception $e) {
					return $this->failRedirect($e, $form);
				}
			}
		}

		return $this->render('user/activation.twig', [
			'form' => $form,
		]);
    }

    public function loginAction(RequestInterface $request, ResponseInterface $response, array $args) {
        /** @var Form $form */
        $form = $this->getContainer('form')->createForm('login');
        $form
			->setAction((string)$request->getUri())
			->add(new FormInputEmail('email', '', [
				'title' => $this->getTranslate('inputs', 'email'),
				'error' => 'Must be valid email',
				'required' => true,
			]))
			->add(new FormInputPassword('password', '', [
				'title' => $this->getTranslate('inputs', 'password'),
				'error' => $this->getTranslate('labels', 'required'),
				'required' => true,
			]))
			->add(new FormInputCheckbox('remember_me', true, [
				'title' => $this->getTranslate('inputs', 'remember_me'),
				'required' => false,
			]))
			->setRules('email', ['required', 'trim', 'email', 'min_length' => 1])
			->setRules('password', ['required', 'trim', 'min_length' => 1])
			->setRules('remember_me', ['bool'])
			->processRequest($request);

		if ($form->getIsSubmitted()) {
			if (!$form->getIsValid()) {
				$form->saveValues();
				return $this->redirectTo($form->getAction());
			} else {
				try {
					$authHelper = new AuthHelper($this->container);
					$authHelper->loginUser(
						$form->getValue('email'),
						$form->getValue('password'),
						(bool)$form->getValue('remember_me')
					);
					return $this->redirect('index');
				} catch (Exception $e) {
					return $this->failRedirect($e, $form);
				}
			}
		}

		return $this->render('user/login.twig', [
			'form' => $form,
		]);
    }

    public function logoutAction(RequestInterface $request, ResponseInterface $response, array $args) {
		$authHelper = new AuthHelper($this->container);
		$authHelper->logoutUser();
    	return $this->redirect('index');
	}

	public function resetPasswordAction(RequestInterface $request, ResponseInterface $response, array $args) {
		/** @var Form $form */
		$form = $this->getContainer('form')->createForm('reset_password');
		$form
            ->setAction((string)$request->getUri())
			->add(new FormInputEmail('email', '', [
				'title' => $this->getTranslate('inputs', 'email'),
				'error' => 'Must be valid email',
				'required' => true,
			]))
			->setRules('email', ['required', 'trim', 'email', 'min_length' => 1])
            ->processRequest($request);

		if ($form->getIsSubmitted()) {
			if (!$form->getIsValid()) {
                return $this->redirectTo($form->getAction());
			} else {
				try {
					$authHelper = new AuthHelper($this->container);
                    $reminderCode = $authHelper->resetPassword(
						$form->getValue('email')
					);
                    JobHelper::createTask('sendmail', [
                        'type' => 'reset_password',
                        'email' => $form->getValue('email'),
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
        $code = $args['code'];

		$identical_password_validator = function($confirmation, $form) {
			return $form->password === $confirmation;
		};

        /** @var Form $form */
        $form = $this->getContainer('form')->createForm('reset_password_complete');
        $form
            ->setAction((string)$request->getUri())
			->add(new FormInputEmail('email', '', [
				'title' => $this->getTranslate('inputs', 'email'),
				'error' => 'Must be valid email',
				'required' => true,
			]))
			->add(new FormInputPassword('password', '', [
				'title' => $this->getTranslate('inputs', 'password'),
				'error' => $this->getTranslate('labels', 'required'),
				'required' => true,
			]))
			->add(new FormInputPassword('password_repeat', '', [
				'title' => $this->getTranslate('inputs', 'password_repeat'),
				'error' => 'Passwords does not match',
				'required' => true,
			]))
			->setRules('email', ['required', 'trim', 'email', 'min_length' => 1])
			->setRules('password', ['required', 'trim', 'min_length' => 6])
			->setRules('password_repeat', ['required', 'trim', 'min_length' => 6, 'identical' => $identical_password_validator])
            ->processRequest($request);

        if ($form->getIsSubmitted()) {
            if (!$form->getIsValid()) {
                return $this->redirectTo($form->getAction());
            } else {
                try {
                    $authHelper = new AuthHelper($this->container);
                    $authHelper->resetPasswordComplete(
                        $form->getValue('email'),
                        $form->getValue('password'),
                        $form->getValue('password_repeat'),
                        $code
                    );
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
