<?php

namespace Wiss\EntityRepository;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

/**
 * 
 */
class Route extends NestedTreeRepository
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
		
        $em = $this->getEntityManager();
		$routes = $config['router']['routes'];
		
		// Build the pages from the routes
		foreach($routes as $name => $routeData) {
            $route = $this->createRoute($name, $routeData);
		}
				
		// Save entities
		$this->getEntityManager()->flush();
	}
	
	/**
	 * 
	 */
	public function export()
	{
		$routes = $this->childrenHierarchy();
		
		// Build the route config as array
		$config['router']['routes'] = $this->buildRouteConfigArray($routes);
		
		// Also build the controllers currently used
		$config['controllers']['invokables'] = $this->buildControllerInvokables();
		
		// Write the config to disk in the config autoload folder
		$writer = new \Zend\Config\Writer\PhpArray();
		$writer->toFile('config/autoload/routes.global.php', $config);
	}
    
    /**
     * 
     * @param string $name
     * @param array $routeData
     * @param \Wiss\Entity\Route $parentRoute
     * @return \Wiss\Entity\Route
     */
    public function createRoute($name, Array $routeData, \Wiss\Entity\Route $parentRoute = null)
    {
        $route = $this->findOneByNameAndParentRoute($name, $parentRoute);
        
        if(!$route) {
            
            $em = $this->getEntityManager();
            $options = $routeData['options'];
            
            // Start a new route
            $route = new \Wiss\Entity\Route;
            $route->setRoute($options['route']);

            if(isset($routeData['type'])) {
                $route->setType($routeData['type']);
            }
            
            $fullName = '';
            if($parentRoute) {
                $fullName .= $parentRoute->getFullName() . '/';
            }
            $fullName .= $name;	
            $route->setName($name);
            $route->setFullName($fullName);
            $route->setParent($parentRoute);

            $defaults = array();
            if(isset($options['defaults'])) {
                $defaults += $options['defaults'];
            }
            if($parentRoute) {
                $defaults += $this->getParentRouteDefaults($parentRoute);
            }
            $route->setDefaults($defaults);

            if(isset($options['constraints'])) {
                $route->setConstraints($options['constraints']);
            }

            $em->persist($route);

            // Create a page on top of the route
            $em->getRepository('Wiss\Entity\Page')->createFromRoute($route, $options);
        }	
            
		// Does this route have any children? Than these routes must be
        // created recursively.
		if(key_exists('child_routes', $routeData)) {
			foreach($routeData['child_routes'] as $name => $childRoute) {
				$this->createRoute($name, $childRoute, $route);
			}
		}	
        
        return $route;
    }
            
    /**
     * 
     * @param \Wiss\Entity\Route $route
     * @return array
     */
    public function getParentRouteDefaults(\Wiss\Entity\Route $route)
    {
        $defaults = (array) $route->getDefaults();
        if($route->getParent() instanceof \Wiss\Entity\Route) {
            $defaults += $this->getParentRouteDefaults($route->getParent());
        }
        return $defaults;
    }
    
    /**
     * 
     * @param string $name
     * @param \Wiss\Entity\Route $parentRoute
     * @return \Wiss\Entity\Route|null
     */
    public function findOneByNameAndParentRoute($name, \Wiss\Entity\Route $parentRoute = null)
    {
        $params['name'] = $name;
        if($parentRoute) {
            $params['parent'] = $parentRoute->getId();
        }
        return $this->findOneBy($params);
    }
		
	/**
	 *
	 * @param array $routes
	 * @return array 
	 */
	public function buildRouteConfigArray($routes)
	{
		$config = array();
                        
		foreach($routes as $data) {
			            
			$route = $this->find($data['id']);
			$name = $route->getName();
            $routeData = array(
				'type' => $data['type'],
				'may_terminate' => true,
				'options' => array(					
					'route'    => $route->getRoute(),
				)
			);
                        
            if($route->getDefaults()) {
                $routeData['defaults'] = $route->getDefaults();
            }
            
            if($route->getConstraints()) {
                $routeData['options'] = $route->getConstraints();
            }
            
			$config[$name] = $routeData;
			
			if($data['__children']) {
				$config[$name]['child_routes'] = $this->buildRouteConfigArray($data['__children']);
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
}
