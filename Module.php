<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Wiss;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\ModuleManager\ModuleManager;

class Module
{
    /**
     *
     * @param MvcEvent $e
     */
	public function onBootstrap(MvcEvent $e)
    {
        // Get the event manager
        $app    = $e->getApplication();
        $sm     = $app->getServiceManager();
		$evm    = $app->getEventManager();	
		$config = $app->getConfig();	
        
        // Hook in to the route event, where is determined
        // which controller/action has to be used
		$evm->attach(MvcEvent::EVENT_ROUTE, function($e) use($config) {
				
            // Get the matched route and its name	
			$route = $e->getRouteMatch();	
			$originalRoute = clone $route;
			$currentRoute = $route->getMatchedRouteName();	
            
    		// Go to the install page first if there is no valid database connection
            // Only go there is the current route is not 'install' already
			if($currentRoute != 'wiss/install' && $config['application']['installed'] === false) {
				
				// Change to route before it is dispatched. It now goes to the
                // install action.
				$route->setParam('controller', 'Wiss\Controller\Index');
				$route->setParam('action', 'redirectToInstall');				
			}
			
			// Check if there are zones used
			if($config['application']['use_zones']) {
				
				// Rewrite all incoming uri's to a single entry point
				$route->setParam('controller', 'Wiss\Controller\PageContent');
				$route->setParam('action', 'route');
				$route->setParam('route', $currentRoute);				
				$route->setParam('originalRoute', $originalRoute);				
			}

		});
    }
		
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
	
    public function getViewHelperConfig()
    {
        return array(
            'factories' => array(
                
                // Instantiate a Flash Messenger
                'flashMessenger' => function($sm) {
					$locator = $sm->getServiceLocator();
					$messenger = $locator->get('Zend\Mvc\Controller\Plugin\FlashMessenger');
					$flashMessenger = new \Wiss\View\Helper\FlashMessenger($messenger);
					return $flashMessenger;
                },
            ),
        );
    }
	
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
}
