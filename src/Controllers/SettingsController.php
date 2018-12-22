<?php
namespace GameX\Controllers;

use \GameX\Core\BaseMainController;
use GameX\Core\Utils;
use \Slim\Http\Request;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Constants\SettingsConstants;
use \GameX\Core\Helpers\UriHelper;
use \GameX\Core\Auth\Helpers\AuthHelper;
use \GameX\Forms\Settings\EmailForm;
use \GameX\Forms\Settings\PasswordForm;
use \GameX\Forms\Settings\AvatarForm;
use \GameX\Core\Exceptions\RedirectException;

class SettingsController extends BaseMainController {
    protected function getActiveMenu() {
        return SettingsConstants::ROUTE_MAIN;
    }

    /**
     * @param Request $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     * @throws RedirectException
     */
    public function indexAction(Request $request, ResponseInterface $response, array $args) {
        $user = $this->getUser();

        $emailForm = new EmailForm($user);
        if ($this->processForm($request, $emailForm, true)) {
            $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
            return $this->redirect(SettingsConstants::ROUTE_MAIN);
        }

        $passwordForm = new PasswordForm($user, new AuthHelper($this->container));
        if ($this->processForm($request, $passwordForm, true)) {
            $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
            return $this->redirect(SettingsConstants::ROUTE_MAIN);
        }

        $avatarForm = new AvatarForm($user, $this->getContainer('upload'));
        if ($this->processForm($request, $avatarForm, true)) {
            $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
            return $this->redirect(SettingsConstants::ROUTE_MAIN);
        }

        return $this->render('settings/main.twig', [
        	'currentHref' => UriHelper::getUrl($request->getUri()),
            'user' => $user,
            'emailForm' => $emailForm->getForm(),
            'passwordForm' => $passwordForm->getForm(),
            'avatarForm' => $avatarForm->getForm(),
        ]);
    }

	/**
	 * @param Request $request
	 * @param ResponseInterface $response
	 * @param array $args
	 * @return ResponseInterface
	 */
	public function assignAction(Request $request, ResponseInterface $response, array $args) {
		return $this->render('settings/assign.twig', [
			'currentHref' => UriHelper::getUrl($request->getUri()),
            'user' => $this->getUser()
		]);
	}
}
