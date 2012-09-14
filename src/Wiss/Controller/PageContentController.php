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
        
        // Get the page, layout and routematch
		$page       = $route->getPage();
        $layout     = $page->getLayout();
		$routeMatch = $this->getEvent()->getRouteMatch();
		
        // Start a new view model
		$view = new ViewModel();
		$view->setTemplate('wiss/page-content/route');
		$view->setOption('layout', $layout->getPath());
					
		// Collect the content per zone
		$zones = array();
		foreach($page->getContent() as $content) {
            $zoneId = $content->getZone()->getId();
			$zones[$zoneId][] = $content;
		}
				
        // Walk each zone and process the blocks
		foreach($zones as $blocks) {
			
			// Build a zone view model
			$viewZone = new ViewModel();
			$viewZone->setTemplate('wiss/page-content/zone');
			$viewZone->setCaptureTo($content->getZone()->getName());	
			$view->addChild($viewZone);
			
			// Add content to this zone
			foreach($blocks as $content) {

                // Get the block from this content part
				$block = $content->getBlock();

				// Alter the current controller's routeMatch		
				$routeMatch->setParam('controller', $block->getController());
				$routeMatch->setParam('action', $block->getAction());
				
				// Inject all defaults
				foreach($content->getDefaults() as $key => $value) {
					$routeMatch->setParam($key, $value);
				}

				// Dispatch the new routeMatch
				$viewChild = $this->forward()->dispatch($block->getController());
				$viewChild->setCaptureTo($content->getId());
				$viewZone->addChild($viewChild);
			}		
				
		}
		
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
