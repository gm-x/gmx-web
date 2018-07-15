<?php
namespace GameX\Controllers\Admin;

use \GameX\Core\BaseAdminController;
use GameX\Core\Mail\Email;
use \Slim\Http\Request;
use \Slim\Http\Response;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Core\Configuration\Config;
use \GameX\Core\Configuration\Node;
use \GameX\Core\Forms\Form;
use \GameX\Core\Forms\Elements\FormInputCheckbox;
use \GameX\Core\Forms\Elements\FormInputText;
use \GameX\Core\Forms\Elements\FormInputEmail;
use \GameX\Core\Forms\Elements\FormSelect;
use \GameX\Core\Forms\Elements\FormInputNumber;
use \GameX\Core\Forms\Elements\FormInputPassword;
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
	 * @param Response $response
	 * @param array $args
	 * @return ResponseInterface
	 */
    public function indexAction(Request $request, Response $response, array $args = []) {
		/** @var Config $config */
		$config = $this->getContainer('config');
		$main = $config->get('main');
		$language = $config->get('language');
        $languages = $language->get('list')->toArray();

		/** @var Form $form */
		$form = $this->createForm('admin_preferences_main')
			->add(new FormInputText('title', $main->get('title'), [
				'title' => $this->getTranslate('admin_preferences', 'title'),
				'required' => true,
			]))
			->add(new FormSelect('language', $language->get('default'), $languages, [
				'title' => $this->getTranslate('admin_preferences', 'language'),
				'required' => true,
			]))
			->setRules('title', ['required', 'trim', 'min_length' => 1])
			->setRules('language', ['required', 'trim', 'in' => array_keys($languages)])
			->setAction((string)$request->getUri())
			->processRequest($request);

		if ($form->getIsSubmitted()) {
			if (!$form->getIsValid()) {
				return $this->redirectTo($form->getAction());
			} else {
				try {
                    $main->set('title', $form->getValue('title'));
					$language->set('default', $form->getValue('language'));
					$config->save();
                    $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
					return $this->redirect('admin_preferences_index');
				} catch (Exception $e) {
					return $this->failRedirect($e, $form);
				}
			}
		}

		return $this->render('admin/preferences/index.twig', [
			'activeTab' => 'admin_preferences_index',
			'form' => $form,
		]);
    }

	/**
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return ResponseInterface
	 */
    public function emailAction(Request $request, Response $response, array $args = []) {
        /** @var Config $config */
        $config = $this->getContainer('config');
        $mail = $config->get('mail');

		$form = $this->getMailForm($mail)
			->setAction((string)$request->getUri())
			->processRequest($request);

		if ($form->getIsSubmitted()) {
			if (!$form->getIsValid()) {
				return $this->redirectTo($form->getAction());
			} else {
				try {
				    $this->setMailConfig($mail, $form);
				    $config->save();
                    $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
					return $this->redirect('admin_preferences_email');
				} catch (Exception $e) {
					return $this->failRedirect($e, $form);
				}
			}
		}

		return $this->render('admin/preferences/email.twig', [
			'activeTab' => 'admin_preferences_email',
			'form' => $form,
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
            $mailConfig = $config->get('mail');
            
			/** @var \GameX\Core\Mail\Helper $mail */
			$mail = $this->getContainer('mail');
        
            $form = $this
                ->getMailForm($mailConfig)
                ->processRequest($request);
            
            if (!$form->getIsSubmitted() || !$form->getIsValid()) {
                throw new Exception('Form is not valid');
            }
        
            $this->setMailConfig($mailConfig, $form);
            $mail->setConfiguration($mailConfig);
			
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
     * @return Form
     */
	protected function getMailForm(Node $config) {
        return $this->createForm('admin_preferences_email')
            ->add(new FormInputCheckbox('enabled', $config->get('enabled'), [
                'title' => $this->getTranslate('admin_preferences', 'enabled'),
            ]))
            ->add(new FormInputText('from_name', $config->get('name'), [
                'title' => $this->getTranslate('admin_preferences', 'from_name'),
            ]))
            ->add(new FormInputEmail('from_email', $config->get('email'), [
                'title' => $this->getTranslate('admin_preferences', 'from_email'),
            ]))
            ->add(new FormSelect('transport_type', $config->get('type'), [
                'smtp' => "SMTP",
                'mail' => 'Mail'
            ], [
                'title' => $this->getTranslate('admin_preferences', 'transport'),
                'id' => 'email_pref_transport'
            ]))
            ->add(new FormInputText('smtp_host', $config->get('host'), [
                'title' => $this->getTranslate('admin_preferences', 'host'),
            ]))
            ->add(new FormInputNumber('smtp_port', $config->get('port'), [
                'title' => $this->getTranslate('admin_preferences', 'port'),
            ]))
            ->add(new FormSelect('smtp_secure', $config->get('secure'), [
                'none' => $this->getTranslate('admin_preferences', 'secure_none'),
                'ssl' => "SSL",
                'tls' => 'TLS'
            ], [
                'title' => $this->getTranslate('admin_preferences', 'secure'),
            ]))
            ->add(new FormInputText('smtp_user', $config->get('username'), [
                'title' => $this->getTranslate('admin_preferences', 'username'),
            ]))
            ->add(new FormInputPassword('smtp_pass', $config->get('password'), [
                'title' => $this->getTranslate('admin_preferences', 'password'),
            ]))
            ->setRules('enabled', ['bool'])
            ->setRules('from_name', ['trim'])
            ->setRules('from_email', ['trim', 'email'])
            ->setRules('transport_type', ['trim', 'in' => ['smtp', 'mail']])
            ->setRules('smtp_host', ['trim'])
            ->setRules('smtp_port', ['numeric'])
            ->setRules('smtp_secure', ['trim', 'in' => ['none', 'ssl', 'tls']])
            ->setRules('smtp_user', ['trim'])
            ->setRules('smtp_pass', ['trim']);
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
