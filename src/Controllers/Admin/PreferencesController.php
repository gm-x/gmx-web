<?php
namespace GameX\Controllers\Admin;

use \GameX\Core\BaseAdminController;
use \Slim\Http\Request;
use \Slim\Http\Response;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Core\Configuration\Config;
use \GameX\Core\Forms\Form;
use \GameX\Core\Forms\Elements\FormInputCheckbox;
use \GameX\Core\Forms\Elements\FormInputText;
use \GameX\Core\Forms\Elements\FormInputEmail;
use \GameX\Core\Forms\Elements\FormSelect;
use \GameX\Core\Forms\Elements\FormInputNumber;
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
		return $this->render('admin/preferences/index.twig', [
			'activeTab' => 'admin_preferences_index',
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
        $settings = $config->get('mail');
        $from = $settings->get('from');
        $transport = $settings->get('transport');

		/** @var Form $form */
		$form = $this->getContainer('form')->createForm('admin_preferences_email');
		$form
			->add(new FormInputCheckbox('enabled', $settings->get('enabled'), [
				'title' => 'Enabled',
			]))
			->add(new FormInputText('from_name', $from->get('name'), [
				'title' => 'From Name',
			]))
			->add(new FormInputEmail('from_email', $from->get('email'), [
				'title' => 'From Email',
			]))
			->add(new FormSelect('transport_type', $transport->get('type'), [
			    'smtp' => "SMTP",
                'mail' => 'Mail'
            ], [
				'title' => 'Mail Transport',
                'id' => 'email_pref_transport'
			]))
            ->add(new FormInputText('smtp_host', $transport->get('host'), [
                'title' => 'Host',
            ]))
            ->add(new FormInputNumber('smtp_port', $transport->get('port'), [
                'title' => 'Port',
            ]))
			->setRules('enabled', ['bool'])
			->setRules('from_name', ['trim'])
			->setRules('from_email', ['trim', 'email'])
			->setRules('transport_type', ['trim', 'in' => ['smtp', 'mail']])
			->setRules('smtp_host', ['trim'])
			->setRules('smtp_port', ['numeric'])
			->setAction((string)$request->getUri())
			->processRequest($request);

		if ($form->getIsSubmitted()) {
			if (!$form->getIsValid()) {
				return $this->redirectTo($form->getAction());
			} else {
				try {
				    $enabled = (bool) $form->getValue('enabled');
                    $settings->set('enabled', $enabled);
                    $from->set('name', $form->getValue('from_name'));
                    $from->set('email', $form->getValue('from_email'));
                    $transport->set('type', $form->getValue('transport_type'));
                    $transport->set('host', $form->getValue('smtp_host'));
                    $transport->set('port', (int) $form->getValue('smtp_port'));
				    $config->save();
					return $this->redirect('admin_preferences_email');
				} catch (Exception $e) {
					return $this->failRedirect($e, $form);
				}
			}
		}

		return $this->render('admin/preferences/email.twig', [
			'activeTab' => 'admin_preferences_email',
			'form' => $form
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
			/** @var \GameX\Core\Mail\Helper $mail */
			$mail = $this->getContainer('mail');
			$mail->send($mail->getFrom(), 'test', 'Test Email');
			return $response->withJson([
				'success' => true,
			]);
		} catch (Exception $e) {
			return $response->withJson([
				'success' => false,
				'message' => $e->getMessage()
			]);
		}
	}
}
