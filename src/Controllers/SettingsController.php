<?php

namespace GameX\Controllers;

use \GameX\Core\BaseMainController;
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
     * @param array $args
     * @return ResponseInterface
     * @throws RedirectException
     */
    public function indexAction(Request $request, ResponseInterface $response, array $args)
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

	    $socialNetworks = $this->getSocialNetworks();

        return $this->getView()->render($response, 'settings/index.twig', [
            'tab' => $request->getParam('tab', 'email'),
            'user' => $user,
            'emailForm' => $emailForm->getForm(),
            'passwordForm' => $passwordForm->getForm(),
            'avatarForm' => $avatarForm->getForm(),
	        'socialNetworks' => $socialNetworks,
        ]);
    }

    protected function getSocialNetworks()
    {
    	/** @var SocialAuth $social */
    	$social = $this->getContainer('social');
    	return $social->getProviders();
    }
}
