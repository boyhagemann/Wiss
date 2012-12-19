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
use DoctrineORMModule\Stdlib\Hydrator\DoctrineEntity as EntityHydrator;
use Wiss\Entity\Page;
use Wiss\Annotation\Content;

class PageController extends AbstractActionController
{
	protected $entityManager;
	
	/**
	 *
	 * @return array 
	 */
    public function indexAction()
    {
		$pages = $this->getEntityManager()->getRepository('Wiss\Entity\Page')->findAll();
				
		return compact('pages');
    }
		
	/**
	 *
	 * @return array 
	 */
	public function createAction()
	{					        
        $em = $this->getEntityManager();
        $repo = $em->getRepository('Wiss\Entity\Route');
        $form = $this->getForm();
        
        $form->setData(array(
            'node' => $this->params('node'),
            'position' => $this->params('position'),
        ));
        
        if($this->getRequest()->isPost()) {
            
            $form->setData($this->getRequest()->getPost());
            
            if($form->isValid()) {
             
                // Collect the needed info
                $data = $form->getData();
                
                // Create a name
                $filter = new \Zend\Filter\Word\SeparatorToDash(' ');
                $name = strtolower($filter->filter($data['title']));
                
                // Create a route and page
                $route = $repo->createRoute($name, array('options' => $data));
                
                // Create the navigation
                $em->getRepository('Wiss\Entity\Navigation')->createFromTree($data['title'], $route, $data['node'], $data['position']);

                // Update the route config
                $repo->export();
                
                // Show a flash message
                $this->flashMessenger()->addMessage('Page created');
                
                // Redirect
                $this->redirect()->toRoute('wiss/page/content', array(
                    'id' => $route->getPage()->getId()
                ));
            }
            
        }
                
		return compact('form');
	}
    
    /**
     * 
     * @return \Wiss\Form\Page
     */
    public function getForm()
    {
        $sl = $this->getServiceLocator();
        $em = $this->getEntityManager();
        $hydrator = new EntityHydrator($this->getEntityManager());
        
        $form = $sl->get('Wiss\Form\Page'); 
		$form->setAttribute('class', 'form-horizontal');
		$form->setHydrator($hydrator);   
        $form->prepareElements();                
        $form->get('layout')->getProxy()->setObjectManager($em);
                
        return $form;
    }
    
	/**
	 *
	 * @return array 
	 */
	public function propertiesAction()
	{
        $em = $this->getEntityManager();
		$repo = $em->getRepository('Wiss\Entity\Page');
		$page = $repo->find($this->params('id'));
								  
        $form = $this->getForm();
        $form->remove('route');
        $form->bind($page);
                        
        if($this->getRequest()->isPost()) {
            
            $form->setData($this->getRequest()->getPost());
            
            if($form->isValid()) {
                             
                $em->persist($form->getData());
                $em->flush();
                                
                // Show a flash message
                $this->flashMessenger()->addMessage('Page properties updated');
                
                // Redirect
                $this->redirect()->toRoute('wiss/page/properties', array(
                    'id' => $page->getId()
                ));
            }
        }
        
		return compact('form', 'page');
	}
    
	/**
	 *
	 * @return array 
	 */
	public function routeAction()
	{
        // Get the page
        $sl = $this->getServiceLocator();
        $em = $this->getEntityManager();
		$repo = $em->getRepository('Wiss\Entity\Page');
		$page = $repo->find($this->params('id'));
								 
        // Get the route form
        $form = $sl->get('Wiss\Form\Route');
        $hydrator = new EntityHydrator($this->getEntityManager());
		$form->setHydrator($hydrator);  
        $form->prepareElements(); 
        $form->bind($page->getRoute()); 
                       
        if($this->getRequest()->isPost()) {
            
            $form->setData($this->getRequest()->getPost());
            
            if($form->isValid()) {
                             
                // Save the route
                $em->persist($form->getData());
                $em->flush();
                
                // Update the route config
                $em->getRepository('Wiss\Entity\Route')->export();
                                
                // Show a flash message
                $this->flashMessenger()->addMessage('Page route updated');
                
                // Redirect
                $this->redirect()->toRoute('wiss/page/route', array(
                    'id' => $page->getId()
                ));
            }
        }
        
		return compact('form', 'page');
	}
		
	/**
	 *
     * @Content(controller="Wiss\Controller\Block", action="available", zone="sidebar")
	 * @return array 
	 */
	public function contentAction()
	{
        $em = $this->getEntityManager();
		$repo = $em->getRepository('Wiss\Entity\Page');
		$page = $repo->find($this->params('id'));
		
		$zones = $page->getLayout()->getZones();
		$used = array();
		foreach($zones as $zone) {
			$used[$zone->getId()] = array();
		}
		
        // Add the blocks to the right zone
		$pageContent = $em->getRepository('Wiss\Entity\Content')->findByPage($page);
		foreach($pageContent as $content) {
			$zoneId = $content->getZone()->getId();
			$used[$zoneId][] = $content;
		}
        
        // Sort the blocks per zone
        foreach($used as &$zone) {
            ksort($zone);
        }
        
		return compact('page', 'zones', 'used');
	}
    
	/**
	 *
	 * @return \Zend\View\Model\JsonModel 
	 */
    public function sortAction()
    {        
        $em = $this->getEntityManager();
        
        try {
        
            $items = $this->params()->fromQuery('items');
            $data = array();
            
            foreach($items as $item) {
                
                if(isset($item['contentId'])) {

                    $content = $em->find('Wiss\Entity\Content', $item['contentId']);
                    $content->setZone($em->find('Wiss\Entity\Zone', $item['zoneId']));
                    $content->setPosition($item['position']);
                    $em->persist($content);                    
                }
                else {
                    
                    $block = $em->find('Wiss\Entity\Block', $item['blockId']);
                    
                    $content = new \Wiss\Entity\Content;
                    $content->setPage($em->find('Wiss\Entity\Page', $this->params('id')));
                    $content->setZone($em->find('Wiss\Entity\Zone', $item['zoneId']));
                    $content->setBlock($block);
                    $content->setPosition($item['position']);
                    $content->setTitle($block->getTitle());
                    $em->persist($content);
                    $em->flush();
                    
                    // Render the block partial
                    $partial = new ViewModel();
                    $partial->setTerminal(true)
                            ->setTemplate('wiss/page/partial/content-actions.phtml')
                            ->setVariable('content', $content);
                    
                    // Return the content ID
                    $renderer = $this->getServiceLocator()->get('viewrenderer');
                    $data['html'] = $renderer->render($partial);
                    $data['content']['id'] = $content->getId();
                }
                
            }
        
            $em->flush();
            
        }
        catch(\Exception $e) {
            $data['message'] = $e->getMessage();
        }
        
        $viewModel = new \Zend\View\Model\JsonModel;
        $viewModel->setVariables(array($data));
        return $viewModel;
    }
    
	/**
	 *
	 * @param \Doctrine\ORM\EntityManager $entityManager 
	 */
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
