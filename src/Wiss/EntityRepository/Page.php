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
	 * @param Wiss\Entity\Page $parent 
	 */
	public function createPageFromRoute($name, $routeData, $parent = null)
	{		
		$em = $this->getEntityManager();
		
		// Build the params to check if the page exists
		$params = array('name' => $name);
		if($parent) {
			$params['parent'] = $parent->getId();
		}
		
		// Check if the page exists
		$page = $this->findOneBy($params);		
		if(!$page) {

			
			// Start a new route
			$route = new \Wiss\Entity\Route;
			$route->setRoute($routeData['options']['route']);
			
			$routeName = '';
			if($parent) {
				$routeName .= $parent->getRoute()->getName() . '/';
			}
			$routeName .= $name;	
			$route->setName($routeName);
			
			if(isset($routeData['options']['defaults'])) {
				$route->setDefaults($routeData['options']['defaults']);
			}
			
			if(isset($routeData['options']['constraints'])) {
				$route->setConstraints($routeData['options']['constraints']);
			}
			
			$em->persist($route);
			
			// Start a new page
			$page = new \Wiss\Entity\Page;
			$page->setTitle($name);
			$page->setName($name);
			$page->setLayout($em->find('Wiss\Entity\Layout', 1));
			$page->setRoute($route);			
			$em->persist($page);
			
			$block = new \Wiss\Entity\Block;
			$block->setTitle($page->getTitle());
			$block->setAction($this->findDefault('action', $page));
			$block->setController($this->findController($page));
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
				$this->createPageFromRoute($name, $childRoute, $page);
			}
		}		
		
		$em->persist($page);				
	}
		
	/**
	 *
	 * @param \Page\Entity\Page $page
	 * @return string 
	 */
	public function findController($page)
	{
		$controller = $this->findDefault('__NAMESPACE__', $page);
		if($controller) {
			$controller .= '\\';
		}
		$controller .= ucfirst($this->findDefault('controller', $page));
		
		return $controller;
	}
	
	/**
	 *
	 * @param string $key
	 * @param \Page\Entity\Page $page
	 * @return string 
	 */
	public function findDefault($key, $page)
	{
		$defaults = (array) $page->getRoute()->getDefaults();
		if(key_exists($key, $defaults)) {
			return $defaults[$key];
		}
		
		if(!$page->getParent()) {
			return '';
		}
		
		return $this->findDefault($key, $page->getParent());
	}
}
