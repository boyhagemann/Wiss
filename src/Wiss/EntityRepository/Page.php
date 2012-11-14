<?php

namespace Wiss\EntityRepository;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * 
 */
class Page extends \Doctrine\ORM\EntityRepository
{	
	/**
	 * 
	 * @param array $config
	 */
	public function import(Array $config)
	{
		
            \Zend\Debug\Debug::dump($config); exit;
		if(!isset($config['router']['routes'])) {
			return;
		}
		
		$routes = $config['router']['routes'];
		
		// Build the pages from the routes
		foreach($routes as $name => $routeData) {
			$this->createPageFromRoute($name, $routeData);
		}
				
		// Save entities
		$this->getEntityManager()->flush();
	}
	
	/**
	 * 
	 */
	public function export()
	{
		$rootPages = $this->findBy(array(
			'parent' => null,
		));
		
		// Build the route config as array
		$config['router']['routes'] = $this->buildRouteConfigArray($rootPages);
		
		// Also build the controllers currently used
		$config['controllers']['invokables'] = $this->buildControllerInvokables();
		
		// Write the config to disk in the config autoload folder
		$writer = new \Zend\Config\Writer\PhpArray();
		$writer->toFile('config/autoload/routes.global.php', $config);
	}
		
	/**
	 *
	 * @param array $tree
	 * @return array 
	 */
	public function buildRouteConfigArray($pages)
	{
		$config = array();
		foreach($pages as $page) {
			
			$name = $page->getName();
			$route = $page->getRoute();
			$config[$name] = array(
				'type' => 'Segment',
				'may_terminate' => true,
				'options' => array(					
					'route'    => $route->getRoute(),
					'defaults' => (array)$route->getDefaults(),
					'constraints' => $route->getConstraints(),
				)
			);
			
			$children = $page->getChildren();			
			if(count($children)) {
				$config[$name]['child_routes'] = $this->buildRouteConfigArray($children);
			}
		}
		
		return $config;
	}
	
	/**
	 * 
	 * @return array
	 */
	public function buildControllerInvokables()
	{
		$controllers = array();
		$blocks = $this->getEntityManager()->getRepository('Wiss\Entity\Block')->findAll();
		foreach($blocks as $block) {
			$alias = $block->getController();
			if(strrpos($alias, 'Controller') === 0) {
				$alias = substr($alias, 0, strrpos($alias, 'Controller'));
			}
			$controllers[$alias] = $alias . 'Controller';
		}
		
		return $controllers;
	}
	
	/**
	 *
	 * @param string $name
	 * @param array $routeData
	 * @param Wiss\Entity\Route $parentRoute 
	 */
	public function createPageFromRoute($name, $routeData, $parentRoute = null)
	{		
		$em = $this->getEntityManager();
		
		// Build the params to check if the page exists
		$params = array('name' => $name);
		if($parentRoute) {
			$params['parent'] = $parentRoute->getId();
		}
		
		// Check if the page exists
		$route = $this->findOneBy($params);		
		if(!$route) {
			
			// Start a new route
			$route = new \Wiss\Entity\Route;
			$route->setRoute($routeData['options']['route']);
			
			$routeName = '';
			if($parentRoute) {
				$routeName .= $parentRoute->getName() . '/';
			}
			$routeName .= $name;	
			$route->setName($routeName);
			$route->setParent($parentRoute);
			
			if(isset($routeData['options']['defaults'])) {
				$route->setDefaults($routeData['options']['defaults']);
			}
			
			if(isset($routeData['options']['constraints'])) {
				$route->setConstraints($routeData['options']['constraints']);
			}
			
			if(isset($routeData['options']['layout'])) {
                $layout = $em->getRepository('Wiss\Entity\Layout')->findOneBy(array(
                    'name' => $routeData['options']['layout'],
                ));
			}
            
			$em->persist($route);
			
			// Start a new page
			$page = new \Wiss\Entity\Page;
			$page->setTitle($name);
			$page->setName($name);
			$page->setLayout($em->find('Wiss\Entity\Layout', 1));
			$page->setRoute($route);		
            if($layout) {
                $page->setLayout($layout);
            }
			$em->persist($page);
			
			$block = new \Wiss\Entity\Block;
			$block->setTitle($page->getTitle());
			$block->setAction($this->findDefault('action', $route));
			$block->setController($this->findController($route));
			$em->persist($block);

			$content = new \Wiss\Entity\Content;
			$content->setTitle('Default page content');
			$content->setPage($page);
			$content->setBlock($block);
			$content->setZone($page->getLayout()->getMainZone());
			$em->persist($content);	
			
		}		
				
		if(key_exists('child_routes', $routeData)) {
			foreach($routeData['child_routes'] as $name => $childRoute) {
				$this->createPageFromRoute($name, $childRoute, $route);
			}
		}		
		
		$em->persist($page);				
	}
		
	/**
	 *
	 * @param \Page\Entity\Page $page
	 * @return string 
	 */
	public function findController($route)
	{
		$controller = $this->findDefault('__NAMESPACE__', $route);
		if($controller) {
			$controller .= '\\';
		}
        
		$controller .= $this->findDefault('controller', $route);
		       
        // Convert to camelCased controller class
        $filter = new \Zend\Filter\Word\DashToCamelCase();
		return $filter->filter($controller);
	}
	
	/**
	 *
	 * @param string $key
	 * @param \Page\Entity\Route $route
	 * @return string 
	 */
	public function findDefault($key, $route)
	{
		$defaults = (array) $route->getDefaults();
		if(key_exists($key, $defaults)) {
			return $defaults[$key];
		}
		
		if(!$route->getParent()) {
			return '';
		}
		
		return $this->findDefault($key, $route->getParent());
	}
}
