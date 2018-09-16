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
		if ($this->processForm($request, $form, true)) {
            $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
            return $this->redirect('user_settings_email');
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
        if ($this->processForm($request, $form, true)) {
            $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
            return $this->redirect('user_settings_password');
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
	    $user = $this->getUser();
        $form = new AvatarForm($user, $this->getContainer('upload'));
        if ($this->processForm($request, $form, true)) {
            $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
            return $this->redirect('user_settings_avatar');
        }
        
		return $this->render('settings/avatar.twig', [
			'currentHref' => UriHelper::getUrl($request->getUri()),
			'user' => $user,
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
		return $this->render('settings/steamid.twig', [
			'currentHref' => UriHelper::getUrl($request->getUri()),
		]);
	}
}
