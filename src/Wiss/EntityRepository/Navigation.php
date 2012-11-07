<?php

namespace Wiss\EntityRepository;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

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
		$navigation = $this->childrenHierarchy();
		
		// Build the route config as array
		$config = array();
		foreach($navigation as $node) {
			$config['navigation'][$node['name']] = $this->buildNavigationConfigArray($node['__children']);
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
			
			if(key_exists('pages', $data)) {
				$data = $data['pages'];
			}
			 
			foreach($data as $childName =>  $childData) {
				$this->createNavigationFromArray($childName, $childData, $this->findOneByName($name));
			}
		}
		else {

			if(isset($data['route'])) {
				$route = $em->getRepository('Wiss\Entity\Route')->findOneBy(array(
					'fullName' => $data['route']
				));
                
                if(!$route) {
                    throw new \Exception(sprintf('route "%s" not found', $data['route']));
                }
			}
            
            if($parent && $route) {
                $navigation = $em->getRepository('Wiss\Entity\Navigation')->findOneBy(array(
					'parent' => $parent->getId(),
                    'route' => $route->getId(),
				));
            }
            
            if(!$navigation) {
                $navigation = new \Wiss\Entity\Navigation;
                $navigation->setRoute($route);
                $navigation->setParent($parent);
            }
				
			$navigation->setLabel($data['label']);
			$navigation->setName($name);
			if(isset($data['params'])) {
				$navigation->setParams($data['params']);
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
	public function buildNavigationConfigArray(Array $navigation)
	{
		$config = array();
			
		foreach($navigation as $data) {
			
			$node = $this->find($data['id']);
			
			$name = $node->getName();
			$config[$name] = array(
				'label' => $node->getLabel(),
				'route' => $node->getRoute()->getFullName(),
			);
			
			if($node->getParams()) {			
				$config[$name]['params'] = $node->getParams();
			}

			if($data['__children'])
			$config[$name]['pages'] = $this->buildNavigationConfigArray($data['__children']);
		}
				
		return $config;
	}
	
	/**
	 *
	 * @return \Wiss\Entity\Navigation 
	 */
	public function getContentNavigation()
	{
		$navigation = $this->findOneByName('content', 1);
		
		if(!$navigation) {
			
			// We have to use a route for the navigation, so just grab
			// a neutral route
			$route = $this->getEntityManager()->getRepository('Wiss\Entity\Route')->findOneBy(array(
				'fullName' => 'wiss/model'
			));
			
			// Insert navigation
			$navigation = new \Wiss\Entity\Navigation;
			$navigation->setLabel('Content');
			$navigation->setParent($this->findOneByName('cms', 0));
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
	public function findOneByName($name, $level = null)
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
