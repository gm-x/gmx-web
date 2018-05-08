<?php
namespace GameX\Controllers;

use \GameX\Core\BaseController;
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Core\Forms\Form;
use \GameX\Core\Auth\AuthHelper;
use GameX\Core\Exceptions\ValidationException;
use \GameX\Core\Exceptions\FormException;
use \Exception;

class UserController extends BaseController {
    public function registerAction(RequestInterface $request, ResponseInterface $response, array $args) {
        /** @var Form $form */
        $form = $this->getContainer('form')->createForm('register');
        $form
            ->add('email', '', [
                'type' => 'email',
                'title' => 'Email',
                'error' => 'Must be valid email',
                'required' => true,
                'attributes' => [],
            ], ['required', 'email'])
            ->add('password', '', [
                'type' => 'password',
                'title' => 'Password',
                'error' => 'Required',
                'required' => true,
                'attributes' => [],
            ], ['required', 'trim', 'min_length' => 6])
            ->add('password_repeat', '', [
                'type' => 'password',
                'title' => 'Repeat Password',
                'error' => 'Passwords does not match',
                'required' => true,
                'attributes' => [],
            ], ['required', 'trim', 'min_length' => 6])
            ->processRequest();

        if ($form->getIsSubmitted()) {
            if (!$form->getIsValid()) {
                return $this->redirect('register');
            } else {
                try {
                	$authHelper = new AuthHelper($this->container);
					$authHelper->registerUser(
                        $form->getValue('email'),
                        $form->getValue('password'),
                        $form->getValue('password_repeat')
                    );
                    return $this->redirect('login');
                } catch (Exception $e) {
                    return $this->failRedirect($e, $form, 'register');
                }
            }
        }

        return $this->render('user/register.twig', [
            'form' => $form,
        ]);
    }

    public function activateAction(RequestInterface $request, ResponseInterface $response, array $args) {
        /** @var Form $form */
        $form = $this->getContainer('form')->createForm('activation');
		$form
			->add('email', '', [
				'type' => 'email',
				'title' => 'Email',
				'error' => 'Must be valid email',
				'required' => true,
				'attributes' => [],
			], ['required', 'email']);
		$form->processRequest();

		if ($form->getIsSubmitted()) {
			if (!$form->getIsValid()) {
				return $this->redirect('activation', ['code' => $code]);
			} else {
				try {
					$authHelper = new AuthHelper($this->container);
					$authHelper->activateUser($form->getValue('email'), $code);
					return $this->redirect('login');
				} catch (Exception $e) {
					return $this->failRedirect($e, $form, 'activation', ['code' => $code]);
				}
			}
		}

		return $this->render('user/activation.twig', [
			'form' => $form,
			'code' => $code,
		]);
    }

    public function loginAction(RequestInterface $request, ResponseInterface $response, array $args) {
        /** @var Form $form */
        $form = $this->getContainer('form')->createForm('login');
        $form
            ->add('email', '', [
                'type' => 'email',
                'title' => 'Email',
                'error' => 'Must be valid email',
                'required' => true,
                'attributes' => [],
            ], ['required', 'email'])
            ->add('password', '', [
                'type' => 'password',
                'title' => 'Password',
                'error' => 'Required',
                'required' => true,
                'attributes' => [],
            ], ['required', 'trim', 'min_length' => 6]);
        $form->processRequest();

		if ($form->getIsSubmitted()) {
			if (!$form->getIsValid()) {
				return $this->redirect('login');
			} else {
				try {
					$authHelper = new AuthHelper($this->container);
					$authHelper->loginUser(
						$form->getValue('email'),
						$form->getValue('password')
					);
					return $this->redirect('index');
				} catch (Exception $e) {
					return $this->failRedirect($e, $form, 'login');
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
			->add('email', '', [
				'type' => 'email',
				'title' => 'Email',
				'error' => 'Must be valid email',
				'required' => true,
				'attributes' => [],
			], ['required', 'email']);
		$form->processRequest();

		if ($form->getIsSubmitted()) {
			if (!$form->getIsValid()) {
				return $this->redirect('reset_password');
			} else {
				try {
					$authHelper = new AuthHelper($this->container);
					$authHelper->resetPassword(
						$form->getValue('email')
					);
					return $this->redirect('index');
				} catch (Exception $e) {
					var_dump($e);
					die();
					return $this->failRedirect($e, $form, 'reset_password');
				}
			}
		}

		$this->render('user/reset_password.twig', [
			'form' => $form
		]);
	}

    protected function failRedirect(Exception $e, Form $form, $path, array $data = [], array $queryParams = []) {
        if ($e instanceof FormException) {
            $form->setError($e->getField(), $e->getMessage());
        } elseif ($e instanceof ValidationException) {
            $this->addFlashMessage('error', $e->getMessage());
        } else {
            $this->addFlashMessage('error', 'Something wrong. Please Try again later.');
        }

        $form->saveValues();

        return $this->redirect($path, $data,  $queryParams);
    }
}
