<?php

namespace GameX\Controllers\Admin\Preferences;

use \GameX\Core\BaseAdminController;
use \Slim\Http\Request;
use \Slim\Http\Response;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Constants\Admin\PreferencesMainConstants;
use \GameX\Forms\Admin\Preferences\MainForm;
use \GameX\Core\Helpers\UriHelper;
use \GameX\Core\Configuration\Config;

class MainController extends BaseAdminController
{
    /**
     * @return string
     */
    protected function getActiveMenu()
    {
        return PreferencesMainConstants::ROUTE_INDEX;
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
        
        $this->getBreadcrumbs()
            ->add($this->getTranslate('admin_preferences', 'tab_main'));
        
        /** @var Config $preferences */
        $preferences = $this->getContainer('preferences');
        $form = new MainForm($preferences);
        if ($this->processForm($request, $form)) {
            $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
            return $this->redirect(PreferencesMainConstants::ROUTE_INDEX);
        }
        
        return $this->getView()->render($response, 'admin/preferences/main/index.twig', [
            'currentHref' => UriHelper::getUrl($request->getUri(), false),
            'form' => $form->getForm(),
        ]);
    }
}
