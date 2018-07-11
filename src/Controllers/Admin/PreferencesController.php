<?php
namespace GameX\Controllers\Admin;

use \GameX\Core\BaseAdminController;
use \Slim\Http\Request;
use \Slim\Http\Response;
use Psr\Http\Message\ResponseInterface;
use \GameX\Core\Forms\Form;
use \GameX\Core\Forms\Elements\FormInputCheckbox;
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
		/** @var Form $form */
		$form = $this->getContainer('form')->createForm('admin_preferences_email');
		$form
			->add(new FormInputCheckbox('enabled', false, [
				'title' => 'Enabled',
				'error' => 'Required',
				'required' => true,
				'attributes' => [],
			]))
			->setRules('enabled', ['bool'])
			->setAction((string)$request->getUri())
			->processRequest($request);

		if ($form->getIsSubmitted()) {
			if (!$form->getIsValid()) {
				return $this->redirectTo($form->getAction());
			} else {
				try {
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
    	return $response->withJson([
    		'success' => true
		]);
	}
}
