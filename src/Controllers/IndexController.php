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

class IndexController extends BaseController {
    public function indexAction(RequestInterface $request, ResponseInterface $response, array $args) {
        return $this->render('index/index.twig');
    }

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

        return $this->render('index/register.twig', [
            'form' => $form,
        ]);
    }

    public function activateAction(RequestInterface $request, ResponseInterface $response, array $args) {
    	$code = $args['code'];

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
				return $this->redirect('login');
			} else {
				try {
					$authHelper = new AuthHelper($this->container);
					$authHelper->activateUser($form->getValue('email'), $code);
					return $this->redirect('login');
				} catch (FormException $e) {
					$form->setError($e->getField(), $e->getMessage());
					return $this->redirect('activation', ['code' => $code]);
				} catch (ValidationException $e) {
					$this->addFlashMessage('error', $e->getMessage());
					return $this->redirect('activation', ['code' => $code]);
				} catch (Exception $e) {
					$this->addFlashMessage('error', 'Something wrong. Please Try again later.');
					return $this->redirect('activation', ['code' => $code]);
				}
			}
		}

		return $this->render('index/activation.twig', [
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

            }
        }

        return $this->render('index/login.twig', [
            'form' => $form,
        ]);
    }

    protected function loginUser($email, $password) {
//
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
