<?php

namespace Wiss\EntityRepository;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * 
 */
class Navigation extends NestedTreeRepository
{
	/**
	 * 
	 * @param array $config
	 */
	public function import(Array $config)
	{
		if(!isset($config['navigation'])) {
			return;
		}
		
		$navigation = $config['navigation'];		
		$parent = $this->getContentNavigation();
		
		// Build the pages from the routes
		foreach($navigation as $name => $navigationData) {
			$this->createNavigationFromArray($name, $navigationData, $parent);
		}
				
		// Save entities
		$this->getEntityManager()->flush();
	}
	
	/**
	 * 
	 */
	public function export()
	{		
		$navigation = $this->findBy(array(
			'parent' => null,
		));				
		
		// Build the route config as array
		$config = array();
		foreach($navigation as $node) {
			$config['navigation'][$node->getName()] = $this->buildNavigationConfigArray($node->getChildren());
		}
		
		// Write the config to disk in the config autoload folder
		$writer = new \Zend\Config\Writer\PhpArray();
		$writer->toFile('config/autoload/navigation.global.php', $config);
	}
		
	/**
	 *
	 * @param string $name
	 * @param array $data
	 * @param Wiss\Entity\Navigation $parent 
	 */
	public function createNavigationFromArray($name, Array $data, $parent = null)
	{		
		$em = $this->getEntityManager();
		
		if(!key_exists('label', $data)) {
			
			key_exists('pages', $data) ?: $data = $data['pages']; 
			
			foreach($data as $childName =>  $childData) {
				$this->createNavigationFromArray($childName, $childData, $this->getNavigation($name));
			}
		}
		else {
			\Zend\Debug\Debug::dump($data);

			$navigation = new \Wiss\Entity\Navigation;
			$navigation->setLabel($data['label']);
			$navigation->setParent($parent);
			$navigation->setName($name);
			if(isset($data['params'])) {
				$navigation->setParams($data['params']);
			}
			
			if(isset($data['route'])) {
				$route = $em->getRepository('Wiss\Entity\Route')->findOneBy(array(
					'name' => $data['route']
				));
				$navigation->setRoute($route);	
			}
			
			$em->persist($navigation);		

			if(isset($data['pages'])) {
				foreach($data['pages'] as $childName =>  $childData) {
					$this->createNavigationFromArray($childName, $childData, $navigation);
				}
			}	
		
		}
			
		
	}
	
	/**
	 *
	 * @param array $navigation
	 * @return array 
	 */
	public function buildNavigationConfigArray($navigation)
	{
		$config = array();
		
		if(!$navigation instanceof \ArrayAccess) {
			return $config;
		}
		
		foreach($navigation as $node) {
			
			$name = $node->getName();
			$config[$name] = array(
				'label' => $node->getLabel(),
				'route' => $node->getRoute()->getName(),
			);
			
			if($node->getParams()) {			
				$config[$name]['params'] = $node->getParams();
			}

			$config[$name]['pages'] = $this->buildNavigationConfigArray($node->getChildren());
		}
				
		return $config;
	}
	
	/**
	 *
	 * @return \Wiss\Entity\Navigation 
	 */
	public function getContentNavigation()
	{
		$navigation = $this->getNavigation('content', 1);
		
		if(!$navigation) {
			
			// We have to use a route for the navigation, so just grab
			// a neutral route
			$route = $this->getEntityManager()->getRepository('Wiss\Entity\Route')->findOneBy(array(
				'name' => 'module'
			));
			
			// Insert navigation
			$navigation = new \Wiss\Entity\Navigation;
			$navigation->setLabel('Content');
			$navigation->setParent($this->getNavigation('cms', 0));
			$navigation->setRoute($route);
			
			$this->getEntityManager()->persist($navigation);
			$this->getEntityManager()->flush();		
		}
		
		return $navigation;
	}
	
	/**
	 *
	 * @param $name OPTIONAL
	 * @param $level OPTIONAL
	 * @return Wiss\Entity\Navigation 
	 */
	public function getNavigation($name, $level = null)
	{
		$params = array(
			'name' => $name
		);
		if($level) {
			$params['lvl'] = $level;
		}
		
		$navigation = $this->findOneBy($params);	
		
		return $navigation;
	}
}
