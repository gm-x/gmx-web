<?php
namespace GameX\Controllers\Admin;

use \GameX\Core\BaseAdminController;
use GameX\Core\Forms\Validator;
use \Slim\Http\Request;
use \Slim\Http\Response;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Forms\Admin\Preferences\MainForm;
use \GameX\Forms\Admin\Preferences\MailForm;
use \GameX\Core\Helpers\UriHelper;
use \GameX\Core\Configuration\Config;
use \GameX\Core\Configuration\Node;
use \GameX\Core\Mail\Email;
use \GameX\Core\Exceptions\FormException;
use \GameX\Core\Exceptions\ValidationException;
use \Exception;

class PreferencesController extends BaseAdminController {

	/**
	 * @return string
	 */
	protected function getActiveMenu() {
		return 'admin_preferences_index';
	}

	/**
	 * @param Request $request
	 * @param ResponseInterface $response
	 * @param array $args
	 * @return ResponseInterface
	 */
    public function indexAction(Request $request, ResponseInterface $response, array $args = []) {
        $form = new MainForm($this->getContainer('config'));
        try {
            $form->create();
        
            if ($form->process($request)) {
                $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
                return $this->redirect('admin_preferences_index');
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

		return $this->render('admin/preferences/index.twig', [
            'currentHref' => UriHelper::getUrl($request->getUri(), false),
			'form' => $form->getForm(),
		]);
    }

	/**
	 * @param Request $request
	 * @param ResponseInterface $response
	 * @param array $args
	 * @return ResponseInterface
	 */
    public function emailAction(Request $request, ResponseInterface $response, array $args = []) {
        /** @var Config $config */
        $config = clone $this->getContainer('config');
        $form = new MailForm($config->get('mail'));
        try {
            $form->create();
        
            if ($form->process($request)) {
                $config->save();
                $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
                return $this->redirect('admin_preferences_email');
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
    public function testAction(Request $request, Response $response, array $args = []) {
    	try {
            /** @var Config $config */
            $config = $this->getContainer('config');
            $form = new MailForm($config->get('mail'));
             $form->create();
        
            if (!$form->process($request)) {
                throw new ValidationException('Form is not valid');
            }
        
            /** @var \GameX\Core\Mail\Helper $mail */
            $mail = $this->getContainer('mail');
            $mail->setConfiguration($config->get('mail'));
            $to = new Email($this->getUser()->email, $this->getUser()->login);
            $mail->send($to, 'test', 'Test Email');
            return $response->withJson([
                'success' => true,
            ]);
		} catch (Exception $e) {
			return $response->withJson([
				'success' => false,
				'message' => $e->getMessage(),
			]);
		}
	}
    

    
    /**
     * @param Node $config
     * @param Form $form
     */
    protected function setMailConfig(Node $config, Form $form) {
        $enabled = (bool) $form->getValue('enabled');
        $config->set('enabled', $enabled);
        $config->set('name', $form->getValue('from_name'));
        $config->set('email', $form->getValue('from_email'));
        $config->set('type', $form->getValue('transport_type'));
        $config->set('host', $form->getValue('smtp_host'));
        $config->set('port', (int) $form->getValue('smtp_port'));
        $config->set('secure', $form->getValue('smtp_secure'));
        $config->set('username', $form->getValue('smtp_user'));
        $config->set('password', $form->getValue('smtp_pass'));
    }
}
