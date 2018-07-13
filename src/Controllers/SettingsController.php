<?php
namespace GameX\Controllers;

use \GameX\Core\BaseMainController;
use \Slim\Http\Request;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Core\Auth\Helpers\AuthHelper;
use \GameX\Core\Forms\Elements\FormInputEmail;
use \GameX\Core\Forms\Elements\FormInputPassword;
use \GameX\Core\Exceptions\FormException;
use \Exception;

class SettingsController extends BaseMainController {
    protected function getActiveMenu() {
        return 'user_settings';
    }
    
    /**
     * @param Request $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function indexAction(Request $request, ResponseInterface $response, array $args) {
        $user = $this->getUser();
        
        $emailForm = $this->createForm('user_settings_email')
            ->add(new FormInputEmail('email', $user->email, [
                'title' => 'Email',
                'error' => 'Must be valid email',
                'required' => true,
            ]))
            ->setRules('email', ['required', 'trim', 'email', 'min_length' => 1])
            ->setAction($request->getUri())
            ->processRequest($request);
    
        $passwordValidator = function($confirmation, $form) {
            return $form->password === $confirmation;
        };
        
        $passwordForm = $this->createForm('user_settings_password')
            ->add(new FormInputPassword('old_password', '', [
                'title' => 'Old password',
                'required' => true,
            ]))
            ->add(new FormInputPassword('new_password', '', [
                'title' => 'New password',
                'required' => true,
            ]))
            ->add(new FormInputPassword('repeat_password', '', [
                'title' => 'Repeat password',
                'required' => true,
            ]))
            ->setRules('old_password', ['required', 'trim', 'min_length' => 6])
            ->setRules('new_password', ['required', 'trim', 'min_length' => 6])
            ->setRules('repeat_password', ['required', 'trim', 'min_length' => 6, 'identical' => $passwordValidator])
            ->setAction($request->getUri())
            ->processRequest($request);
    
        if ($emailForm->getIsSubmitted()) {
            if (!$emailForm->getIsValid()) {
                return $this->redirectTo($emailForm->getAction());
            } else {
                try {
                    $user->email = $emailForm->getValue('email');
                    $user->save();
                    $this->addSuccessMessage('Email saved successfully');
                    return $this->redirect('user_settings');
                } catch (Exception $e) {
                    return $this->failRedirect($e, $emailForm);
                }
            }
        }
    
        if ($passwordForm->getIsSubmitted()) {
            if (!$passwordForm->getIsValid()) {
                return $this->redirectTo($passwordForm->getAction());
            } else {
                try {
                    $authHelper = new AuthHelper($this->container);
                    if (!$authHelper->validatePassword($user, $passwordForm->getValue('old_password'))) {
                        throw new FormException('old_password', "Bad password");
                    }
                    $authHelper->changePassword($user, $passwordForm->getValue('new_password'));
                    $this->addSuccessMessage('Password updated successfully');
                    return $this->redirect('user_settings');
                } catch (Exception $e) {
                    return $this->failRedirect($e, $emailForm);
                }
            }
        }
        
        return $this->render('settings/index.twig', [
            'emailForm' => $emailForm,
            'passwordForm' => $passwordForm,
        ]);
    }
}
