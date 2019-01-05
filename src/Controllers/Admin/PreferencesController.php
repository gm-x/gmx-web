<?php

namespace GameX\Controllers\Admin;

use \GameX\Core\BaseAdminController;
use \Slim\Http\Request;
use \Slim\Http\Response;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Constants\Admin\PreferencesConstants;
use \GameX\Core\Update\Manifest;
use \GameX\Forms\Admin\Preferences\MainForm;
use \GameX\Forms\Admin\Preferences\MailForm;
use \GameX\Forms\Admin\Preferences\UpdateForm;
use \GameX\Core\Helpers\UriHelper;
use \GameX\Core\Configuration\Config;
use \GameX\Core\Mail\Email;
use \GameX\Core\Mail\Exceptions\ConnectException;
use \GameX\Core\Mail\Exceptions\CryptoException;
use \GameX\Core\Mail\Exceptions\CodeException;
use \GameX\Core\Mail\Exceptions\SendException;
use \GameX\Core\Exceptions\ValidationException;
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
     * @param array $args
     * @return ResponseInterface
     * @throws \GameX\Core\Exceptions\RedirectException
     */
    public function indexAction(Request $request, Response $response, array $args = [])
    {
        /** @var Config $preferences */
        $preferences = $this->getContainer('preferences');
        $form = new MainForm($preferences);
        if ($this->processForm($request, $form)) {
            $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
            return $this->redirect(PreferencesConstants::ROUTE_MAIN);
        }
        
        return $this->render('admin/preferences/index.twig', [
            'currentHref' => UriHelper::getUrl($request->getUri(), false),
            'form' => $form->getForm(),
        ]);
    }
    
    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return ResponseInterface
     * @throws \GameX\Core\Configuration\Exceptions\CantSaveException
     * @throws \GameX\Core\Configuration\Exceptions\NotFoundException
     * @throws \GameX\Core\Exceptions\RedirectException
     */
    public function emailAction(Request $request, Response $response, array $args = [])
    {
        /** @var Config $config */
        $config = clone $this->getContainer('preferences');
        $form = new MailForm($config->getNode('mail'));
        if ($this->processForm($request, $form)) {
            $config->save();
            $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
            return $this->redirect(PreferencesConstants::ROUTE_EMAIL);
        }
        
        return $this->render('admin/preferences/email.twig', [
            'currentHref' => UriHelper::getUrl($request->getUri(), false),
            'form' => $form->getForm(),
        ]);
    }
    
    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return ResponseInterface
     */
    public function testAction(Request $request, Response $response, array $args = [])
    {
        try {
            /** @var Config $config */
            $config = $this->getContainer('preferences');
            $form = new MailForm($config->getNode('mail'));
            $form->create();
            
            if (!$form->process($request)) {
                throw new ValidationException();
            }
            
            /** @var \GameX\Core\Mail\Helper $mail */
            $mail = $this->getContainer('mail');
            $mail->setConfiguration($config->getNode('mail'));
            $to = new Email($this->getUser()->email, $this->getUser()->login);
            $mail->send($to, 'test', 'Test Email');
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
     * @param array $args
     * @return ResponseInterface
     * @throws \GameX\Core\Exceptions\RedirectException
     */
    public function updateAction(Request $request, Response $response, array $args = [])
    {
        $form = new UpdateForm($this->getContainer('updater'));
        if ($this->processForm($request, $form)) {
            $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
            return $this->redirect(PreferencesConstants::ROUTE_UPDATE);
        }
        
        return $this->render('admin/preferences/update.twig', [
            'currentHref' => UriHelper::getUrl($request->getUri(), false),
            'form' => $form->getForm()
        ]);
    }
}
