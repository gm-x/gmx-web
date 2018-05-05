<?php
namespace GameX\Controllers;

use \GameX\Core\BaseController;
use \Cartalyst\Sentinel\Sentinel;
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Core\Forms\FormHelper;
use \GameX\Core\Mail\MailHelper;
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
                    $this->registerUser(
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

    protected function registerUser($email, $password, $password_repeat) {
        if ($password !== $password_repeat) {
            throw new FormException('password_repeat', 'Password didn\'t match');
        }

        /** @var Sentinel $auth */
        $auth = $this->getContainer()->get('auth');

        $user = $auth->getUserRepository()->findByCredentials([
            'email' => $email
        ]);

        if ($user) {
            throw new FormException('email', 'User already exists');
        }

        $user = $auth->register([
            'email'  => $email,
            'password' => $password,
        ]);

        if (!$user) {
            throw new ValidationException('Something wrong. Please Try again later.');
        }

        $activation = $auth->getActivationRepository()->create($user);

        /** @var MailHelper $mail */
        $mail = $this->getContainer()->get('mail');
        $mail->send([
            'name' => $email,
            'email' => $email
        ], 'Activation', 'Your code is ' . $activation->getCode());
    }

    protected function loginUser($email, $password) {
//
    }
}
