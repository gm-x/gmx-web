<?php
namespace GameX\Controllers;

use \GameX\Core\BaseMainController;
use \Slim\Http\Request;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Core\Helpers\UriHelper;
use \GameX\Core\Auth\Helpers\AuthHelper;
use \GameX\Forms\Settings\EmailForm;
use \GameX\Forms\Settings\PasswordForm;
use \GameX\Forms\Settings\AvatarForm;
use \GameX\Core\Exceptions\ValidationException;
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
		$form = new EmailForm($this->getUser());
		try {
			$form->create();

			if ($form->process($request)) {
				$this->addSuccessMessage('Email saved successfully');
				return $this->redirect('user_settings_email');
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
        $form = new PasswordForm($this->getUser(), new AuthHelper($this->container));
        try {
            $form->create();
            
            if ($form->process($request)) {
                $this->addSuccessMessage('Password updated successfully');
                return $this->redirect('user_settings_password');
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

		return $this->render('settings/password.twig', [
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
	public function avatarAction(Request $request, ResponseInterface $response, array $args) {
	    
	    
        $form = new AvatarForm($this->getUser());
        try {
            $form->create();
            
            if ($form->process($request)) {
                $this->addSuccessMessage('Avatar updated successfully');
                return $this->redirect('user_settings_avatar');
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
		return $this->render('settings/avatar.twig', [
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
