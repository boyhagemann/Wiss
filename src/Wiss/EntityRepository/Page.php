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
        $defaults = $route->getDefaults();

        // Get the right layout        
        if(!isset($defaults['layout'])) {
            $layoutParams['id'] = 1;
        }
        elseif(is_numeric($defaults['layout'])) {
            $layoutParams['id'] = $defaults['layout'];
        }
        else {
            $layoutParams['slug'] = $defaults['layout'];
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

        $this->addPageContent($page, $defaults);	
        
        $em->flush();
	}
    
    /**
     * 
     * @param \Wiss\Entity\Page $page
     */
    public function addPageContent(\Wiss\Entity\Page $page, Array $defaults)
    {        
		$em = $this->getEntityManager();
        $route = $page->getRoute();
        
        $controller = $this->buildControllerClass($defaults);
        
        // Create a new default block for this page
        $block = new \Wiss\Entity\Block;
        $block->setTitle($page->getTitle());
        $block->setAction($defaults['action']);
        $block->setController($controller);
        $em->persist($block);

        // Add this block to the content
        $content = new \Wiss\Entity\Content;
        $content->setTitle('Default page content');
        $content->setPage($page);
        $content->setBlock($block);
        $content->setZone($page->getLayout()->getMainZone());
        $em->persist($content);	
    }
    
    /**
     * 
     * @param array $defaults
     * @return string
     */
    public function buildControllerClass(Array $defaults)
    {
        $filter = new \Zend\Filter\Word\DashToCamelCase();
        $controllerClass = '';
        if(isset($defaults['__NAMESPACE__'])) {
            $controllerClass .= trim($defaults['__NAMESPACE__'], '\\') . '\\';
        }
        $controllerClass .= ucfirst($filter->filter($defaults['controller']));
        
        return $controllerClass;
    }
    
}
