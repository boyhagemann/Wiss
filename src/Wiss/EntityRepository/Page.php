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
	 * @param \Wiss\Entity\Route $route
     * @param array $options
	 */
	public function createFromRoute(\Wiss\Entity\Route $route, Array $options = array())
	{		
		$em = $this->getEntityManager();					

        // Get the right layout        
        if(!isset($options['layout'])) {
            $layoutParams['id'] = 1;
        }
        elseif(is_numeric($options['layout'])) {
            $layoutParams['id'] = $options['layout'];
        }
        else {
            $layoutParams['slug'] = $options['layout'];
        }
        $layout = $em->getRepository('Wiss\Entity\Layout')->findOneBy($layoutParams);
        
        // Build a nice title
        $filter = new \Zend\Filter\Word\DashToSeparator(' ');
        $name = $route->getName();
        $title = ucfirst($filter->filter($name));
        
        // Start a new page
        $page = new \Wiss\Entity\Page;
        $page->setTitle($title);
        $page->setName($name);
        $page->setLayout($layout);
        $page->setRoute($route);	
        $em->persist($page);
        
        // Also bind the page to the route
        $route->setPage($page);
        $em->persist($route);

        $this->addPageContent($page);	
        
        $em->flush();
	}
        
    /**
     * 
     * @param \Wiss\Entity\Page $page
     */
    public function addPageContent(\Wiss\Entity\Page $page)
    {        
		$em = $this->getEntityManager();
        $route = $page->getRoute();
        
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
		$controller .= ucfirst($this->findDefault('controller', $route));
		
		return $controller;
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
    
	/**
	 * 
	 * @param array $config
	 */
//	public function import(Array $config)
//	{
//        print 'not used: ' . __CLASS__; exit;
//        
//        $em = $this->getEntityManager();
//        
//		if(!isset($config['router']['routes'])) {
//			return;
//		}
//		
//		$routes = $config['router']['routes'];
//		
//		// Build the pages from the routes
//		foreach($routes as $name => $routeData) {
//            $route = $em->getRepository('Wiss\Entity\Route')->createRoute($name, $options);
//            $this->createPageFromRoute($route);
//		}
//				
//		// Save entities
//		$em->flush();
//	}
//	
//	/**
//	 * 
//	 */
//	public function export()
//	{
//		$rootPages = $this->findBy(array(
//			'parent' => null,
//		));
//		
//		// Build the route config as array
//		$config['router']['routes'] = $this->buildRouteConfigArray($rootPages);
//		
//		// Also build the controllers currently used
//		$config['controllers']['invokables'] = $this->buildControllerInvokables();
//		
//		// Write the config to disk in the config autoload folder
//		$writer = new \Zend\Config\Writer\PhpArray();
//		$writer->toFile('config/autoload/routes.global.php', $config);
//	}
//		
//	/**
//	 *
//	 * @param array $tree
//	 * @return array 
//	 */
//	public function buildRouteConfigArray($pages)
//	{
//		$config = array();
//		foreach($pages as $page) {
//			
//			$name = $page->getName();
//			$route = $page->getRoute();
//			$config[$name] = array(
//				'type' => 'Segment',
//				'may_terminate' => true,
//				'options' => array(					
//					'route'    => $route->getRoute(),
//					'defaults' => (array)$route->getDefaults(),
//					'constraints' => $route->getConstraints(),
//				)
//			);
//			
//			$children = $page->getChildren();			
//			if(count($children)) {
//				$config[$name]['child_routes'] = $this->buildRouteConfigArray($children);
//			}
//		}
//		
//		return $config;
//	}
//	
//	/**
//	 * 
//	 * @return array
//	 */
//	public function buildControllerInvokables()
//	{
//		$controllers = array();
//		$blocks = $this->getEntityManager()->getRepository('Wiss\Entity\Block')->findAll();
//		foreach($blocks as $block) {
//			$alias = $block->getController();
//			if(strrpos($alias, 'Controller') === 0) {
//				$alias = substr($alias, 0, strrpos($alias, 'Controller'));
//			}
//			$controllers[$alias] = $alias . 'Controller';
//		}
//		
//		return $controllers;
//	}
//	
//		
//	/**
//	 *
//	 * @param \Page\Entity\Page $page
//	 * @return string 
//	 */
//	public function findController($route)
//	{
//		$controller = $this->findDefault('__NAMESPACE__', $route);
//		if($controller) {
//			$controller .= '\\';
//		}
//        
//		$controller .= $this->findDefault('controller', $route);
//		       
//        // Convert to camelCased controller class
//        $filter = new \Zend\Filter\Word\DashToCamelCase();
//		return $filter->filter($controller);
//	}
//	
//	/**
//	 *
//	 * @param string $key
//	 * @param \Page\Entity\Route $route
//	 * @return string 
//	 */
//	public function findDefault($key, $route)
//	{
//		$defaults = (array) $route->getDefaults();
//		if(key_exists($key, $defaults)) {
//			return $defaults[$key];
//		}
//		
//		if(!$route->getParent()) {
//			return '';
//		}
//		
//		return $this->findDefault($key, $route->getParent());
//	}
}
