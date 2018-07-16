<?php
namespace GameX\Controllers;

use \GameX\Core\BaseMainController;
use \Slim\Http\Request;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Core\Helpers\UriHelper;
use \GameX\Core\Auth\Helpers\AuthHelper;
use \GameX\Core\Forms\Elements\FormInputEmail;
use \GameX\Core\Forms\Elements\FormInputPassword;
use \GameX\Core\Forms\Elements\FormInputFile;
use \GameX\Core\Exceptions\FormException;
use \Exception;

class SettingsController extends BaseMainController {
    protected function getActiveMenu() {
        return 'user_settings_index';
    }
    
    /**
     * @param Request $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function indexAction(Request $request, ResponseInterface $response, array $args) {
        return $this->render('settings/index.twig', [
        	'currentHref' => UriHelper::getUrl($request->getUri()),
        ]);
    }

	/**
	 * @param Request $request
	 * @param ResponseInterface $response
	 * @param array $args
	 * @return ResponseInterface
	 */
	public function emailAction(Request $request, ResponseInterface $response, array $args) {
		$user = $this->getUser();

		$form = $this->createForm('user_settings_email')
			->add(new FormInputEmail('email', $user->email, [
				'title' => 'Email',
				'error' => 'Must be valid email',
				'required' => true,
			]))
			->setRules('email', ['required', 'trim', 'email', 'min_length' => 1])
			->setAction($request->getUri())
			->processRequest($request);

		if ($form->getIsSubmitted()) {
			if (!$form->getIsValid()) {
				return $this->redirectTo($form->getAction());
			} else {
				try {
					$user->email = $form->getValue('email');
					$user->save();
					$this->addSuccessMessage('Email saved successfully');
					return $this->redirect('user_settings_email');
				} catch (Exception $e) {
					return $this->failRedirect($e, $form);
				}
			}
		}

		return $this->render('settings/email.twig', [
			'currentHref' => UriHelper::getUrl($request->getUri()),
			'form' => $form,
		]);
	}

	/**
	 * @param Request $request
	 * @param ResponseInterface $response
	 * @param array $args
	 * @return ResponseInterface
	 */
	public function passwordAction(Request $request, ResponseInterface $response, array $args) {
		$user = $this->getUser();

		$passwordValidator = function($confirmation, $form) {
			return $form->new_password === $confirmation;
		};

		$form = $this->createForm('user_settings_password')
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

		if ($form->getIsSubmitted()) {
			if (!$form->getIsValid()) {
				return $this->redirectTo($form->getAction());
			} else {
				try {
					$authHelper = new AuthHelper($this->container);
					if (!$authHelper->validatePassword($user, $form->getValue('old_password'))) {
						throw new FormException('old_password', "Bad password");
					}
					$authHelper->changePassword($user, $form->getValue('new_password'));
					$this->addSuccessMessage('Password updated successfully');
					return $this->redirect('user_settings_password');
				} catch (Exception $e) {
					return $this->failRedirect($e, $form);
				}
			}
		}

		return $this->render('settings/password.twig', [
			'currentHref' => UriHelper::getUrl($request->getUri()),
			'form' => $form,
		]);
	}

	/**
	 * @param Request $request
	 * @param ResponseInterface $response
	 * @param array $args
	 * @return ResponseInterface
	 */
	public function avatarAction(Request $request, ResponseInterface $response, array $args) {
		$user = $this->getUser();

		$form = $this->createForm('user_settings_avatar')
			->add(new FormInputFile('avatar', '', [
				'title' => 'Avatar',
				'required' => true,
			]))
			->setAction($request->getUri())
			->processRequest($request);

		if ($form->getIsSubmitted()) {
			if (!$form->getIsValid()) {
				return $this->redirectTo($form->getAction());
			} else {
				try {
					$this->addSuccessMessage('Avatar updated successfully');
					return $this->redirect('user_settings_avatar');
				} catch (Exception $e) {
					return $this->failRedirect($e, $form);
				}
			}
		}

		return $this->render('settings/avatar.twig', [
			'currentHref' => UriHelper::getUrl($request->getUri()),
			'form' => $form,
		]);
	}

	/**
	 * @param Request $request
	 * @param ResponseInterface $response
	 * @param array $args
	 * @return ResponseInterface
	 */
	public function steamidAction(Request $request, ResponseInterface $response, array $args) {
		$user = $this->getUser();

		$form = $this->createForm('user_settings_steamid')
			->setAction($request->getUri())
			->processRequest($request);

		if ($form->getIsSubmitted()) {
			if (!$form->getIsValid()) {
				return $this->redirectTo($form->getAction());
			} else {
				try {
					$this->addSuccessMessage('Steamid updated successfully');
					return $this->redirect('user_settings_steamid');
				} catch (Exception $e) {
					return $this->failRedirect($e, $form);
				}
			}
		}

		return $this->render('settings/steamid.twig', [
			'currentHref' => UriHelper::getUrl($request->getUri()),
			'form' => $form,
		]);
	}
}
