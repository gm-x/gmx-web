<?php
namespace GameX\Controllers;

use \GameX\Core\BaseMainController;
use \Slim\Http\Request;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Core\Helpers\UriHelper;
use \GameX\Core\Auth\Helpers\AuthHelper;
use \GameX\Forms\UserSettingsEmail;
use \GameX\Forms\UserSettingsPassword;
use \GameX\Core\Forms\Elements\File;
use \GameX\Core\Exceptions\RedirectException;
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
		$form = new UserSettingsEmail($this->getUser());
		try {
			$form->create();

			if ($form->process($request)) {
				$this->addSuccessMessage('Email saved successfully');
				return $this->redirect('user_settings_email');
			}
		} catch (RedirectException $e) {
			$this->redirectTo($e->getUrl());
		} catch (Exception $e) {
			$this->failRedirect($e, $form->getForm());
		}

		return $this->render('settings/email.twig', [
			'currentHref' => UriHelper::getUrl($request->getUri()),
			'form' => $form->getForm(),
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

		$form = UserSettingsPassword::init([])
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
			->add(new File('avatar', '', [
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
