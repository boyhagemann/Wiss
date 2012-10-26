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
        		        
        // Get the page
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

            // Inject all defaults
            foreach($content->getDefaults() as $key => $value) {
                $routeMatch->setParam($key, $value);
            }

            // Dispatch the new routeMatch
            $view = $this->forward()->dispatch($block->getController());
            $view->setCaptureTo($content->getId());
            
            // Add the view to a zone view
            $this->getZoneViewModel($zoneName)->addChild($view);
        }
		
        // If all blocks are added to the zones, add the zones
        // to the layout
        foreach($this->zoneViewModels as $zoneName => $viewModel) {           
            $this->layout()->addChild($viewModel, $zoneName);
        }
        
        // No need to render the current action, all blocks are now
        // rendered directly to the layout.
		return false;
	}
    
    /**
     * 
     * @param type $name
     * @return \Zend\View\Model\ViewModel
     */
    public function getZoneViewModel($name)
    {
        if(key_exists($name, $this->zoneViewModels)) {
            return $this->zoneViewModels[$name];
        }
        
        $view = new ViewModel();
        $view->setTemplate('wiss/page-content/zone');
        $view->setVariable('zone', $name);
        $this->zoneViewModels[$name] = $view;
        
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
