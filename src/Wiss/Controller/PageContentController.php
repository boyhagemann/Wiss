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

class PageContentController extends AbstractActionController
{
	protected $entityManager;
			
	public function routeAction()
	{
        $em = $this->getEntityManager();
		$route = $em->getRepository('Wiss\Entity\Route')->findOneBy(array('name' => $this->params('route')));		
		$page = $route->getPage();
		$routeMatch = $this->getEvent()->getRouteMatch();
		
		$view = new ViewModel();
		$view->setTemplate('wiss/page-content/route');
		$view->setOption('layout', $page->getLayout()->getPath());
					
		// Split the content into zones
		$zones = array();
		foreach($page->getContent() as $content) {
			$zones[$content->getZone()->getId()][] = $content;
		}
				
		foreach($zones as $blocks) {

			$zone = $content->getZone();
			
			// Build a zone view model
			$viewZone = new ViewModel();
			$viewZone->setTemplate('page-content/zone');
			$viewZone->setCaptureTo($zone->getName());	
			$view->addChild($viewZone);
			
			// Add content to this zone
			foreach($blocks as $content) {

				$block = $content->getBlock();

				// Alter the current controller's routeMatch			
				$routeMatch->setParam('controller', $block->getController());
				$routeMatch->setParam('action', $block->getAction());
				
				// Inject all defaults
				foreach((array)$content->getDefaults() as $key => $value) {
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
     */
    public function generateAction()
    {
        $em = $this->getEntityManager();
        $classes = array(
          $em->getClassMetadata('Page\Entity\Content'),
          $em->getClassMetadata('Page\Entity\Block'),
        );
        
        $tool = new \Doctrine\ORM\Tools\SchemaTool($em);
        
        try {
            $tool->dropSchema($classes);
        }
        catch(Exception $e) {
            print $e->getMessage();
        }
        $tool->createSchema($classes);
			
		$layout = $em->find('Page\Entity\Layout', 1);
		
		// Insert zone
		$zone2 = new \Page\Entity\Zone;
		$zone2->setTitle('Sidebar content');
		$zone2->setName('sidebar');
		$zone2->setLayout($layout);
		$em->persist($zone2);
				
		$em->flush();
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
