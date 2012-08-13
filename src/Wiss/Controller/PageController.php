<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Wiss\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class PageController extends AbstractActionController
{
	protected $entityManager;
	
    public function indexAction()
    {
		$pages = $this->getEntityManager()->getRepository('Wiss\Entity\Page')->findAll();
		
		return compact('pages');
    }
		
	public function viewAction()
	{
		$repo = $this->getEntityManager()->getRepository('Wiss\Entity\Page');
		$page = $repo->find($this->params('id'));
					
		return compact('page');
	}
		
	public function importAction()
	{
		$config = $this->getServiceLocator()->get('config');
		$routes = $config['router']['routes'];
		$em = $this->getEntityManager();
		
		// Build the pages from the routes
		foreach($routes as $name => $routeData) {
			$this->createPageFromRoute($name, $routeData, $em);
		}
				
		// Save entities
		$em->flush();
		
		// Build the config
		$this->exportRoutes();
		
		// Redirect
		$this->redirect()->toRoute('page');
		
		return false;
	}
	
	public function exportRoutes()
	{
		$rootPages = $this->getEntityManager()->getRepository('Wiss\Entity\Page')->findBy(array(
			'parent' => null,
		));
		
		// Build the route config as array
		$config = array();
		$config['router']['routes'] = $this->buildRouteConfigArray($rootPages);
		
		// Write the config to disk in the config autoload folder
		$writer = new \Zend\Config\Writer\PhpArray();
		$writer->toFile('config/autoload/global.page.routes.config.php', $config);
		
		// Redirect
		$this->redirect()->toRoute('page');
		
		return false;
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
			$routeParts = explode('/', $route->getRoute());
			$config[$name] = array(
				'type' => 'Segment',
				'may_terminate' => true,
				'options' => array(					
					'route'    => end($routeParts),
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
	 * @param string $name
	 * @param array $routeData
	 * @param type $em
	 * @param Wiss\Entity\Page $parent 
	 */
	public function createPageFromRoute($name, $routeData, $em, $parent = null)
	{		
		// Build the params to check if the page exists
		$params = array('name' => $name);
		if($parent) {
			$params['parent'] = $parent->getId();
		}
		
		// Check if the page exists
		$page = $em->getRepository('Wiss\Entity\Page')->findOneBy($params);		
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
						
			if($parent) {
				$page->setParent($parent);
			}
			
			$em->persist($page);
			
			$block = new \Wiss\Entity\Block;
			$block->setTitle($page->getTitle());
			$block->setAction($this->findDefault('action', $page));
			$block->setController($this->findController($page));
			$em->persist($block);

			$content = new \Wiss\Entity\Content;
			$content->setPage($page);
			$content->setBlock($block);
			$content->setZone($page->getLayout()->getMainZone());
			$em->persist($content);	
			
		}
				
		if(key_exists('child_routes', $routeData)) {
			foreach($routeData['child_routes'] as $name => $childRoute) {
				$this->createPageFromRoute($name, $childRoute, $em, $page);
			}
		}		
		
		$vars = compact('page');
		$evm = $this->getEventManager();
		$evm->trigger(__FUNCTION__ . '.pre', $this, $vars);
		
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
		$defaults = $page->getRoute()->getDefaults();
		if(key_exists($key, $defaults)) {
			return $defaults[$key];
		}
		
		if(!$page->getParent()) {
			return '';
		}
		
		return $this->findDefault($key, $page->getParent());
	}
	
	public function setEntityManager(\Doctrine\ORM\EntityManager $entityManager)
	{
		$this->entityManager = $entityManager;
	}
	
	/**
	 *
	 * @return type 
	 */
	public function getEntityManager()
	{
		return $this->entityManager;
	}
}
