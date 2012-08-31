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
			$currentRoute = $route->getMatchedRouteName();	
				
            // Check the db connection. Is the password still 'password'? Then the
            // connection is not set yet.
            $dbParams = $config['doctrine']['connection']['orm_default']['params'];
            
    		// Go to the install page first if there is no valid database connection
            // Only go there is the current route is not 'install' already
			if($currentRoute != 'install' && $dbParams['password'] == 'password') {
				
				// Change to route before it is dispatched. It now goes to the
                // install action.
				$route->setParam('controller', 'Wiss\Controller\Index');
				$route->setParam('action', 'install');				
			}
			
			// Check if there are zones used
			if($config['application']['use_zones']) {
				
				// Rewrite all incoming uri's to a single entry point
				$route->setParam('controller', 'Wiss\Controller\PageContent');
				$route->setParam('action', 'route');
				$route->setParam('route', $current);				
			}

		});
    }
	
    /**
     *
     * @param ModleManager $moduleManager
     */
    public function init(ModuleManager $moduleManager)
    {
        // If the Wiss module is being used in the application, then
        // a special layout is used.
        $sharedEvents = $moduleManager->getEventManager()->getSharedManager();
        $sharedEvents->attach(__NAMESPACE__, 'dispatch', function($e) {
            
            // This event will only be fired when an ActionController u
            // Under the Wiss namespace is dispatched.
            $controller = $e->getTarget();
            $controller->layout('wiss/layout/layout');
            
        }, 100);
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
