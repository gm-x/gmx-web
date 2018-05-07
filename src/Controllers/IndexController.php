<?php
namespace GameX\Controllers;

use \GameX\Core\BaseController;

use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Core\Forms\FormHelper;
use \GameX\Core\Auth\AuthHelper;
use GameX\Core\Exceptions\ValidationException;
use \GameX\Core\Exceptions\FormException;
use \Exception;

class IndexController extends BaseController {
    public function indexAction(RequestInterface $request, ResponseInterface $response, array $args) {
        return $this->render('index/index.twig');
    }

    public function registerAction(RequestInterface $request, ResponseInterface $response, array $args) {
        $form = new FormHelper('register');
        $form
            ->addField('email', '', [
                'type' => 'email',
                'title' => 'Email',
                'error' => 'Must be valid email',
                'required' => true,
                'attributes' => [],
            ], ['required', 'email'])
            ->addField('password', '', [
                'type' => 'password',
                'title' => 'Password',
                'error' => 'Required',
                'required' => true,
                'attributes' => [],
            ], ['required', 'trim', 'min_length' => 6])
            ->addField('password_repeat', '', [
                'type' => 'password',
                'title' => 'Repeat Password',
                'error' => 'Passwords does not match',
                'required' => true,
                'attributes' => [],
            ], ['required', 'trim', 'min_length' => 6]);
        $form->processRequest($this->getRequest());

        if ($form->getIsSubmitted()) {
            if (!$form->getIsValid()) {
                $form->saveValues();
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
                } catch (FormException $e) {
                    $form->setError($e->getField(), $e->getMessage());
                    $form->saveValues();
                    return $this->redirect('register');
                } catch (ValidationException $e) {
                    $this->addFlashMessage('error', $e->getMessage());
                    $form->saveValues();
                    return $this->redirect('register');
                } catch (Exception $e) {
                    $this->addFlashMessage('error', 'Something wrong. Please Try again later.');
                    $form->saveValues();
                    return $this->redirect('register');
                }
            }
        }

        return $this->render('index/register.twig', [
            'form' => $form,
        ]);
    }

    public function activateAction(RequestInterface $request, ResponseInterface $response, array $args) {
    	$code = $args['code'];

		$form = new FormHelper('activation');
		$form
			->addField('email', '', [
				'type' => 'email',
				'title' => 'Email',
				'error' => 'Must be valid email',
				'required' => true,
				'attributes' => [],
			], ['required', 'email']);
		$form->processRequest($this->getRequest());

		if ($form->getIsSubmitted()) {
			if (!$form->getIsValid()) {
				$form->saveValues();
				return $this->redirect('login');
			} else {
				try {
					$authHelper = new AuthHelper($this->container);
					$authHelper->activateUser($form->getValue('email'), $code);
					return $this->redirect('login');
				} catch (FormException $e) {
					$form->setError($e->getField(), $e->getMessage());
					$form->saveValues();
					return $this->redirect('activation', ['code' => $code]);
				} catch (ValidationException $e) {
					$this->addFlashMessage('error', $e->getMessage());
					$form->saveValues();
					return $this->redirect('activation', ['code' => $code]);
				} catch (Exception $e) {
					$this->addFlashMessage('error', 'Something wrong. Please Try again later.');
					$form->saveValues();
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
        $form = new FormHelper('login');
        $form
            ->addField('email', '', [
                'type' => 'email',
                'title' => 'Email',
                'error' => 'Must be valid email',
                'required' => true,
                'attributes' => [],
            ], ['required', 'email'])
            ->addField('password', '', [
                'type' => 'password',
                'title' => 'Password',
                'error' => 'Required',
                'required' => true,
                'attributes' => [],
            ], ['required', 'trim', 'min_length' => 6]);
        $form->processRequest($this->getRequest());

        if ($form->getIsSubmitted()) {
            if (!$form->getIsValid()) {
                $form->saveValues();
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
}
