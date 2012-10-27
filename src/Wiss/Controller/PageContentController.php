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
use Doctrine\ORM\EntityManager;

class PageContentController extends AbstractActionController
{
    /**
     *
     * @var array 
     */
    protected $zoneViewModels = array();
    
    /**
     *
     * @var EntityManager
     */
	protected $entityManager;
			
    /**            
     * This action dispatches multiple other controller actions into
     * so called zones.
     *
     */
	public function routeAction()
	{			
        $em = $this->getEntityManager();
        
        // Get the route this found in the original routeMatch
		$route = $em->getRepository('Wiss\Entity\Route')->findOneBy(array(
            'fullName' => $this->params('route')
        ));		
        		        
        // Get the page that belongs to the found route
		$page = $route->getPage();
        
        // Set the right layout
		$this->layout($page->getLayout()->getPath());
				
				
        // Walk each zone and process the blocks
		foreach($page->getContent() as $content) {
			
            $zoneName = $content->getZone()->getName();
            
            // Get the block from this content part
            $block = $content->getBlock();

            // Alter the current controller's routeMatch		
            $routeMatch	= $this->getEvent()->getRouteMatch();
            $routeMatch->setParam('controller', $block->getController());
            $routeMatch->setParam('action', $block->getAction());

            // Inject all defaults that are set in the content block.
            // Each content block can have its own unique parameters to
            // control a block. This means a new controller action is 
            // dispatched for each block with custom parameters.
            foreach($content->getDefaults() as $key => $value) {
                $routeMatch->setParam($key, $value);
            }

            // Dispatch the new routeMatch. The routeMatch has the
            // right action set, so the only thing that remains is
            // to point to the right controller.
            $view = $this->forward()->dispatch($block->getController());
            
            // The dispatched block can return a viewmodel. We want to 
            // capture this model to a unique key in our zone view model. 
            $view->setCaptureTo($content->getId());
            
            // Add the view to a zone view
            $this->getZoneViewModel($zoneName)->addChild($view);
        }
		
        // If all blocks are added to the zones, add the zones
        // to the layout. We want to expose the zone viewmodel to a variable
        // key in the layout view script.
        foreach($this->zoneViewModels as $zoneName => $viewModel) {           
            $this->layout()->addChild($viewModel, $zoneName);
        }
        
        // No need to render the current action, all blocks are now
        // rendered directly to the layout.
		return false;
	}
    
    /**
     * 
     * @param string $name
     * @return \Zend\View\Model\ViewModel
     */
    public function getZoneViewModel($name)
    {
        // Check if the zone viewmodel already exists
        if(key_exists($name, $this->zoneViewModels)) {
            return $this->zoneViewModels[$name];
        }
        
        // Create a new viewmodel if it does not exist yet.
        $view = new ViewModel();
        
        // Give the viewmodel a special template. In this template the user
        // can alter the surrounding html to their needs
        $view->setTemplate('wiss/page-content/zone');
        
        // We want to pass the current zone name as a variable in the view,
        // just for styling purposes
        $view->setVariable('zone', $name);
        
        // Add the viewmodel to this controllers registry
        $this->zoneViewModels[$name] = $view;
        
        // Return the zone viewmodel
        return $view;
    }
	
    /**
     *
     * @param EntityManager $entityManager
     */
	public function setEntityManager(EntityManager $entityManager)
	{
		$this->entityManager = $entityManager;
	}
	
	/**
	 *
	 * @return EntityManager 
	 */
	public function getEntityManager()
	{
		return $this->entityManager;
	}
}
