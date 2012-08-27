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

	public function onBootstrap($e)
    {
		$evm = $e->getApplication()->getEventManager();		
		$evm->attach(MvcEvent::EVENT_ROUTE, function($e) {
				
			$config = $e->getApplication()->getConfig();	
			$route = $e->getRouteMatch();	
			$current = $route->getMatchedRouteName();	
				
			// Go to the install page first if there is no valid database connection
			if($current != 'install' && $config['doctrine']['connection']['orm_default']['params']['password'] == 'password') {
				$sm = $e->getApplication()->getServiceManager();
				
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
	
    public function init(ModuleManager $moduleManager)
    {
        $sharedEvents = $moduleManager->getEventManager()->getSharedManager();
        $sharedEvents->attach(__NAMESPACE__, 'dispatch', function($e) {
            // This event will only be fired when an ActionController under the MyModule namespace is dispatched.
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
