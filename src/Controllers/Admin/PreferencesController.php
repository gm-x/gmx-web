<?php

namespace GameX\Controllers\Admin;

use \GameX\Core\BaseAdminController;
use \Slim\Http\Request;
use \Slim\Http\Response;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Core\Pagination\Pagination;
use \GameX\Core\Update\Updater;
use \GameX\Core\Auth\Permissions;
use \GameX\Constants\Admin\PreferencesConstants;
use \GameX\Forms\Admin\Preferences\MainForm;
use \GameX\Forms\Admin\Preferences\MailForm;
use \GameX\Forms\Admin\Preferences\UpdateForm;
use \GameX\Forms\Admin\Preferences\CacheForm;
use \GameX\Forms\Admin\Preferences\SocialForm;
use \GameX\Core\Configuration\Config;
use \GameX\Core\Mail\Email;
use \GameX\Models\Task;
use \GameX\Core\Mail\Exceptions\ConnectException;
use \GameX\Core\Mail\Exceptions\CryptoException;
use \GameX\Core\Mail\Exceptions\CodeException;
use \GameX\Core\Mail\Exceptions\SendException;
use \GameX\Core\Exceptions\ValidationException;
use \GameX\Core\Configuration\Exceptions\CantSaveException;
use \GameX\Core\Configuration\Exceptions\NotFoundException;
use \GameX\Core\Exceptions\RedirectException;
use \GameX\Core\Exceptions\RoleNotFoundException;
use \Exception;

class PreferencesController extends BaseAdminController
{
    
    /**
     * @return string
     */
    protected function getActiveMenu()
    {
        return PreferencesConstants::ROUTE_MAIN;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return ResponseInterface
     * @throws RedirectException
     * @throws RoleNotFoundException
     * @throws \GameX\Core\Cache\NotFoundException
     */
    public function mainAction(Request $request, Response $response)
    {
        $this->getBreadcrumbs()
            ->add($this->getTranslate('admin_preferences', 'tab_main'));

        $hasAccessToEdit = $this->getPermissions()->hasUserAccessToPermission(
            PreferencesConstants::PERMISSION_GROUP,
            PreferencesConstants::PERMISSION_MAIN_KEY,
            Permissions::ACCESS_EDIT
        );

        $form = new MainForm($this->container, $hasAccessToEdit);
        if ($this->processForm($request, $form)) {
            $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
            return $this->redirect(PreferencesConstants::ROUTE_MAIN);
        }

        return $this->getView()->render($response, 'admin/preferences/main.twig', [
            'form' => $form->getForm(),
            'hasAccessToEdit' => $hasAccessToEdit,
        ]);
    }

	/**
	 * @param Request $request
	 * @param Response $response
	 * @return ResponseInterface
	 * @throws CantSaveException
	 * @throws NotFoundException
	 * @throws RedirectException
	 * @throws RoleNotFoundException
	 * @throws \GameX\Core\Cache\NotFoundException
	 */
    public function emailAction(Request $request, Response $response)
    {
        $this->getBreadcrumbs()
            ->add($this->getTranslate('admin_preferences', 'tab_email'));

        $hasAccessToEdit = $this->getPermissions()->hasUserAccessToPermission(
            PreferencesConstants::PERMISSION_GROUP,
            PreferencesConstants::PERMISSION_EMAIL_KEY,
            Permissions::ACCESS_EDIT
        );

        /** @var Config $config */
        $config = clone $this->getContainer('preferences');
        $form = new MailForm($config->getNode('mail'), $hasAccessToEdit);
        if ($this->processForm($request, $form)) {
            $config->save();
            $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
            return $this->redirect(PreferencesConstants::ROUTE_EMAIL);
        }
        
        return $this->getView()->render($response, 'admin/preferences/email.twig', [
            'form' => $form->getForm(),
            'hasAccessToEdit' => $hasAccessToEdit,
        ]);
    }
    
    /**
     * @param Request $request
     * @param Response $response
     * @return ResponseInterface
     */
    public function testAction(Request $request, Response $response)
    {
        try {
            /** @var Config $config */
            $config = $this->getContainer('preferences');
            $form = new MailForm($config->getNode('mail'), true);
            $form->create();
            
            if (!$form->process($request)) {
                throw new ValidationException();
            }
            
            /** @var \GameX\Core\Mail\Helper $mail */
            $mail = $this->getContainer('mail');
            $mail->setConfiguration($config->getNode('mail'));
            $to = new Email($this->getUser()->email, $this->getUser()->login);
            $mail->send($to, 'test', $mail->render('test'));
            return $response->withJson([
                'success' => true,
            ]);
        } catch (ValidationException $e) {
            return $response->withJson([
                'success' => false,
                'message' => $this->getTranslate('admin_preferences', 'email_not_valid'),
            ]);
        } catch (ConnectException $e) {
            return $response->withJson([
                'success' => false,
                'message' => $this->getTranslate('admin_preferences', 'email_connect_fail'),
            ]);
        } catch (CryptoException $e) {
            return $response->withJson([
                'success' => false,
                'message' => $this->getTranslate('admin_preferences', 'email_encrypt_fail'),
            ]);
        } catch (CodeException $e) {
            return $response->withJson([
                'success' => false,
                'message' => $this->getTranslate('admin_preferences', 'email_code_fail', $e->getExpected(),
                    $e->getReceived()),
            ]);
        } catch (SendException $e) {
            return $response->withJson([
                'success' => false,
                'message' => $this->getTranslate('admin_preferences', 'email_send_fail'),
            ]);
        } catch (Exception $e) {
            return $response->withJson([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

	/**
	 * @param Request $request
	 * @param Response $response
	 * @return ResponseInterface
	 * @throws RedirectException
	 * @throws RoleNotFoundException
	 * @throws \GameX\Core\Cache\NotFoundException
	 */
    public function updateAction(Request $request, Response $response)
    {
        $this->getBreadcrumbs()
            ->add($this->getTranslate('admin_preferences', 'tab_update'));

        $hasAccessToEdit = $this->getPermissions()->hasUserAccessToPermission(
            PreferencesConstants::PERMISSION_GROUP,
            PreferencesConstants::PERMISSION_UPDATE_KEY,
            Permissions::ACCESS_EDIT
        );

        /** @var Updater $updater */
        $updater = $this->getContainer('updater');
        $form = new UpdateForm($updater, $hasAccessToEdit);
        if ($this->processForm($request, $form)) {
            $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
            return $this->redirect(PreferencesConstants::ROUTE_UPDATE);
        }
        
        return $this->getView()->render($response, 'admin/preferences/update.twig', [
            'form' => $form->getForm(),
            'version' => $updater->getManifest()->getVersion(),
            'hasAccessToEdit' => $hasAccessToEdit,
        ]);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return ResponseInterface
     * @throws RedirectException
     * @throws RoleNotFoundException
     * @throws \GameX\Core\Cache\NotFoundException
     */
    public function cacheAction(Request $request, Response $response)
    {
        $this->getBreadcrumbs()
            ->add($this->getTranslate('admin_preferences', 'tab_cache'));

	    $hasAccessToEdit = $this->getPermissions()->hasUserAccessToPermission(
		    PreferencesConstants::PERMISSION_GROUP,
		    PreferencesConstants::PERMISSION_CACHE_KEY,
		    Permissions::ACCESS_EDIT
	    );

        $form = new CacheForm($this->container->get('root'), $hasAccessToEdit);
        if ($this->processForm($request, $form)) {
            $this->addSuccessMessage($this->getTranslate('admin_preferences', 'cache_success'));
            return $this->redirect(PreferencesConstants::ROUTE_CACHE);
        }
        
        return $this->getView()->render($response, 'admin/preferences/cache.twig', [
            'form' => $form->getForm(),
	        'hasAccessToEdit' => $hasAccessToEdit,
        ]);
    }

	/**
	 * @param Request $request
	 * @param Response $response
	 * @return ResponseInterface
	 */
    public function cronAction(Request $request, Response $response)
    {
        $this->getBreadcrumbs()
            ->add($this->getTranslate('admin_preferences', 'tab_cron'));
        
        $root = $this->container->get('root');

        $tasks = Task::get();
        $pagination = new Pagination($tasks, $request);
        
        return $this->getView()->render($response, 'admin/preferences/cron.twig', [
            'root' => $root,
            'tasks' => $pagination->getCollection(),
            'pagination' => $pagination,
        ]);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return ResponseInterface
     * @throws RedirectException
     * @throws RoleNotFoundException
     * @throws \GameX\Core\Cache\NotFoundException
     */
    public function socialAction(Request $request, Response $response)
    {
	    $this->getBreadcrumbs()
		    ->add($this->getTranslate('admin_preferences', 'tab_social'));

	    $hasAccessToEdit = $this->getPermissions()->hasUserAccessToPermission(
		    PreferencesConstants::PERMISSION_GROUP,
		    PreferencesConstants::PERMISSION_SOCIAL_KEY,
		    Permissions::ACCESS_EDIT
	    );

	    /** @var Config $preferences */
	    $preferences = $this->getContainer('preferences');
	    $form = new SocialForm($preferences, $hasAccessToEdit);
	    if ($this->processForm($request, $form)) {
		    $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
		    return $this->redirect(PreferencesConstants::ROUTE_SOCIAL);
	    }

	    return $this->getView()->render($response, 'admin/preferences/social.twig', [
		    'form' => $form->getForm(),
		    'hasAccessToEdit' => $hasAccessToEdit,
	    ]);
    }
}
