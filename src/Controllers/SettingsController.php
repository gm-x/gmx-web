<?php

namespace GameX\Controllers;

use GameX\Core\Auth\Models\UserSocialModel;
use \GameX\Core\BaseMainController;
use Illuminate\Database\Eloquent\Collection;
use \Slim\Http\Request;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Core\Auth\Social\SocialAuth;
use \GameX\Constants\SettingsConstants;
use \GameX\Core\Auth\Helpers\AuthHelper;
use \GameX\Forms\Settings\EmailForm;
use \GameX\Forms\Settings\PasswordForm;
use \GameX\Forms\Settings\AvatarForm;
use \GameX\Core\Exceptions\RedirectException;

class SettingsController extends BaseMainController
{
    protected function getActiveMenu()
    {
        return SettingsConstants::ROUTE_INDEX;
    }
    
    /**
     * @param Request $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     * @throws RedirectException
     */
    public function indexAction(Request $request, ResponseInterface $response)
    {
        $user = $this->getUser();
        
        $emailForm = new EmailForm($user);
        if ($this->processForm($request, $emailForm, true)) {
            $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
            return $this->redirect(SettingsConstants::ROUTE_INDEX);
        }
        
        $passwordForm = new PasswordForm($user, new AuthHelper($this->container));
        if ($this->processForm($request, $passwordForm, true)) {
            $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
            return $this->redirect(SettingsConstants::ROUTE_INDEX, [], ['tab' => 'password']);
        }
        
        $avatarForm = new AvatarForm($user, $this->getContainer('upload'));

        if ($this->processForm($request, $avatarForm, true)) {
            $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
            return $this->redirect(SettingsConstants::ROUTE_INDEX, [], ['tab' => 'avatar']);
        }

	    $passwordForm->getForm()->setAction($this->pathFor(SettingsConstants::ROUTE_INDEX, [], ['tab' => 'password']));
	    $avatarForm->getForm()->setAction($this->pathFor(SettingsConstants::ROUTE_INDEX, [], ['tab' => 'avatar']));

	    /** @var SocialAuth $social */
	    $social = $this->getContainer('social');
	    $socialNetworks = $social->getProviders();

	    $userSocial = $this->processDisconnectSocial($request);
		if ($userSocial) {
			$this->addSuccessMessage($this->getTranslate('settings', 'social_disconnected', $social->getTitle($userSocial->provider)));
			return $this->redirect(SettingsConstants::ROUTE_INDEX, [], ['tab' => 'social']);
		}

	    $userSocials = $user->socials
            ->keyBy(function (UserSocialModel $item) {
                return $item->provider;
            })
            ->all();

        return $this->getView()->render($response, 'settings/index.twig', [
            'tab' => $request->getParam('tab', 'email'),
            'user' => $user,
            'emailForm' => $emailForm->getForm(),
            'passwordForm' => $passwordForm->getForm(),
            'avatarForm' => $avatarForm->getForm(),
	        'socialNetworks' => $socialNetworks,
	        'userSocials' => $userSocials,
        ]);
    }

	/**
	 * @param Request $request
	 * @return bool|UserSocialModel
	 * @throws \Exception
	 */
	protected function processDisconnectSocial(Request $request)
	{
		if (!$request->isPost()) {
			return false;
		}

		$id = $request->getParam('social');
		if (!$id) {
			return false;
		}

		$userSocial = UserSocialModel::find($id);
		if ($userSocial->user_id != $this->getUser()->id) {
			return false;
		}
		$userSocial->delete();
		return $userSocial;
	}
}
