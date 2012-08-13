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

class Module
{

	public function onBootstrap($e)
    {
		$evm = $e->getApplication()->getEventManager();		
		$evm->attach(MvcEvent::EVENT_ROUTE, function($e) {
				
			// Rewrite all incoming uri's to a single entry point
			$route = $e->getRouteMatch();
			$current = $route->getMatchedRouteName();	
			$route->setParam('controller', 'Wiss\Controller\PageContent');
			$route->setParam('action', 'route');
			$route->setParam('route', $current);	
		});
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
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
