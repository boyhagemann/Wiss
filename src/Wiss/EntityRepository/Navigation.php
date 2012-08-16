<?php

namespace Wiss\EntityRepository;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * 
 */
class Navigation extends NestedTreeRepository
{
	public function exportToConfig()
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
	 * @param boolean $root OPTIONAL
	 */
	public function createNavigationFromArray($name, Array $data, $parent = null, $root = false)
	{		
		$em = $this->getEntityManager();
		
		if($root) {
			
			$navigation = $em->getRepository('Wiss\Entity\Navigation')->findOneBy(array(
				'name' => $name,
			));
			
			foreach($data as $childName =>  $childData) {
				$this->createNavigationFromArray($childName, $childData, $navigation);
			}
		}
		else {

			$navigation = new \Wiss\Entity\Navigation;
			$navigation->setLabel($data['label']);
			$navigation->setParent($parent);
						
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
	
}
