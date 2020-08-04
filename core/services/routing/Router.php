<?php

namespace EventEspresso\core\services\routing;

use EE_Dependency_Map;
use EventEspresso\core\services\loaders\LoaderInterface;
use Exception;

/**
 * Class RoutingSwitch
 *
 * @package EventEspresso\core\services\routing
 * @since   $VID:$
 */
class Router
{

    /**
     * @var EE_Dependency_Map $dependency_map
     */
    protected $dependency_map;

    /**
     * @var LoaderInterface $loader
     */
    protected $loader;

    /**
     * @var RouteHandler $route_handler
     */
    protected $route_handler;

    /**
     * @var string $route_request_type
     */
    protected $route_request_type;


    /**
     * RoutingSwitch constructor.
     *
     * @param EE_Dependency_Map $dependency_map
     * @param LoaderInterface   $loader
     * @param RouteHandler      $router
     */
    public function __construct(EE_Dependency_Map $dependency_map, LoaderInterface $loader, RouteHandler $router)
    {
        $this->dependency_map = $dependency_map;
        $this->loader         = $loader;
        $this->route_handler  = $router;
    }


    /**
     * @throws Exception
     */
    public function loadPrimaryRoutes()
    {
        $this->dependency_map->registerDependencies(
            'EventEspresso\core\domain\entities\routing\handlers\admin\ActivationRequests',
            Route::getFullDependencies()
        );
        $this->dependency_map->registerDependencies(
            'EventEspresso\core\domain\entities\routing\handlers\shared\RegularRequests',
            Route::getFullDependencies()
        );
        // now load and prep all primary Routes
        $this->route_handler->addRoute('EventEspresso\core\domain\entities\routing\handlers\admin\ActivationRequests');
        $this->route_handler->addRoute('EventEspresso\core\domain\entities\routing\handlers\shared\RegularRequests');
        $this->route_request_type = $this->route_handler->getRouteRequestType();
    }


    /**
     *
     * @throws Exception
     */
    public function registerShortcodesModulesAndWidgets()
    {
        switch ($this->route_request_type) {
            case PrimaryRoute::ROUTE_REQUEST_TYPE_ACTIVATION:
                break;
            case PrimaryRoute::ROUTE_REQUEST_TYPE_REGULAR:
                $this->route_handler->addRoute(
                    'EventEspresso\core\domain\entities\routing\handlers\frontend\ShortcodeRequests'
                );
                break;
        }
    }


    /**
     *
     * @throws Exception
     * @throws Exception
     * @throws Exception
     * @throws Exception
     */
    public function brewEspresso()
    {
        switch ($this->route_request_type) {
            case PrimaryRoute::ROUTE_REQUEST_TYPE_ACTIVATION:
                $this->route_handler->addRoute(
                    'EventEspresso\core\domain\entities\routing\handlers\admin\PueRequests'
                );
                break;
            case PrimaryRoute::ROUTE_REQUEST_TYPE_REGULAR:
                $this->route_handler->addRoute(
                    'EventEspresso\core\domain\entities\routing\handlers\shared\GQLRequests'
                );
                $this->route_handler->addRoute(
                    'EventEspresso\core\domain\entities\routing\handlers\admin\PueRequests'
                );
                $this->route_handler->addRoute(
                    'EventEspresso\core\domain\entities\routing\handlers\shared\RestApiRequests'
                );
                break;
        }
    }


    /**
     *
     * @throws Exception
     * @throws Exception
     * @throws Exception
     * @throws Exception
     * @throws Exception
     * @throws Exception
     * @throws Exception
     * @throws Exception
     * @throws Exception
     * @throws Exception
     */
    public function loadControllers()
    {
        switch ($this->route_request_type) {
            case PrimaryRoute::ROUTE_REQUEST_TYPE_ACTIVATION:
                $this->route_handler->addRoute(
                    'EventEspresso\core\domain\entities\routing\handlers\admin\AdminRoute'
                );
                $this->route_handler->addRoute(
                    'EventEspresso\core\domain\entities\routing\handlers\admin\WordPressPluginsPage'
                );
                break;
            case PrimaryRoute::ROUTE_REQUEST_TYPE_REGULAR:
                $this->route_handler->addRoute(
                    'EventEspresso\core\domain\entities\routing\handlers\frontend\FrontendRequests'
                );
                $this->route_handler->addRoute(
                    'EventEspresso\core\domain\entities\routing\handlers\admin\AdminRoute'
                );
                $this->route_handler->addRoute(
                    'EventEspresso\core\domain\entities\routing\handlers\admin\EspressoLegacyAdmin'
                );
                $this->route_handler->addRoute(
                    'EventEspresso\core\domain\entities\routing\handlers\admin\EspressoEventsAdmin'
                );
                $this->route_handler->addRoute(
                    'EventEspresso\core\domain\entities\routing\handlers\admin\EspressoEventEditor'
                );
                $this->route_handler->addRoute(
                    'EventEspresso\core\domain\entities\routing\handlers\admin\GutenbergEditor'
                );
                $this->route_handler->addRoute(
                    'EventEspresso\core\domain\entities\routing\handlers\admin\WordPressPluginsPage'
                );
                $this->route_handler->addRoute(
                    'EventEspresso\core\domain\entities\routing\handlers\shared\WordPressHeartbeat'
                );
                break;
        }
    }


    /**
     *
     * @throws Exception
     * @throws Exception
     */
    public function coreLoadedAndReady()
    {
        switch ($this->route_request_type) {
            case PrimaryRoute::ROUTE_REQUEST_TYPE_ACTIVATION:
                break;
            case PrimaryRoute::ROUTE_REQUEST_TYPE_REGULAR:
                $this->route_handler->addRoute(
                    'EventEspresso\core\domain\entities\routing\handlers\shared\AssetRequests'
                );
                $this->route_handler->addRoute(
                    'EventEspresso\core\domain\entities\routing\handlers\shared\SessionRequests'
                );
                break;
        }
    }


    /**
     *
     * @throws Exception
     */
    public function initializeLast()
    {
        switch ($this->route_request_type) {
            case PrimaryRoute::ROUTE_REQUEST_TYPE_ACTIVATION:
                break;
            case PrimaryRoute::ROUTE_REQUEST_TYPE_REGULAR:
                $this->route_handler->addRoute(
                    'EventEspresso\core\domain\entities\routing\handlers\admin\PersonalDataRequests'
                );
                break;
        }
    }
}