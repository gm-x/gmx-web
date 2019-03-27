<?php

namespace GameX\Controllers\Admin\Preferences;

use \GameX\Core\BaseAdminController;
use \Slim\Http\Request;
use \Slim\Http\Response;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Constants\Admin\PreferencesMainConstants;
use \GameX\Core\Auth\Permissions;
use \GameX\Forms\Admin\Preferences\Main\GeneralForm;
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
     * @throws \GameX\Core\Exceptions\RoleNotFoundException
     */
    public function indexAction(Request $request, Response $response, array $args = [])
    {
        $this->getBreadcrumbs()
            ->add($this->getTranslate('admin_preferences', 'tab_main'));

        $hasAccessToEdit = $this->getPermissions()->hasUserAccessToPermission(
            PreferencesMainConstants::PERMISSION_GROUP,
            PreferencesMainConstants::PERMISSION_KEY,
            Permissions::ACCESS_EDIT
        );
        
        /** @var Config $preferences */
        $preferences = $this->getContainer('preferences');
        $form = new GeneralForm($preferences, $hasAccessToEdit);
        if ($this->processForm($request, $form)) {
            $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
            return $this->redirect(PreferencesMainConstants::ROUTE_INDEX);
        }
        
        return $this->getView()->render($response, 'admin/preferences/main/index.twig', [
            'currentHref' => UriHelper::getUrl($request->getUri(), false),
            'form' => $form->getForm(),
            'hasAccessToEdit' => $hasAccessToEdit
        ]);
    }
}
