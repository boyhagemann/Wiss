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
        
        if($this->getRequest()->isPost()) {
            
            $form->setData($this->getRequest()->getPost());
            
            if($form->isValid()) {
             
                // Collect the needed info
                $data = $form->getData();
                $filter = new \Zend\Filter\Word\SeparatorToDash(' ');
                $name = strtolower($filter->filter($data['title']));
                
                // Create a route and page
                $route = $repo->createRoute($name, array('options' => $data));
                
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
    
    public function getForm()
    {
        $sl = $this->getServiceLocator();
        $em = $this->getEntityManager();
        
        $form = $sl->get('Wiss\Form\Page'); 
		$form->setAttribute('class', 'form-horizontal');
        
        // Add a hydrator for doctrine entities
        $hydrator = new EntityHydrator($this->getEntityManager());
		$form->setHydrator($hydrator);   
                
        $form->prepareElements();
        $form->get('layout_id')->getProxy()->setObjectManager($em);
        
        
        return $form;
    }
    
	/**
	 *
	 * @return array 
	 */
	public function propertiesAction()
	{
		$repo = $this->getEntityManager()->getRepository('Wiss\Entity\Page');
		$page = $repo->find($this->params('id'));
					
		return compact('page');
	}
		
	/**
	 *
	 * @return array 
	 */
	public function contentAction()
	{
		$repo = $this->getEntityManager()->getRepository('Wiss\Entity\Page');
		$page = $repo->find($this->params('id'));
		
		$zones = $page->getLayout()->getZones();
		$used = array();
		foreach($zones as $zone) {
			$used[$zone->getId()] = array();
		}
		
		$pageContent = $page->getContent();
		foreach($pageContent as $content) {
			$zoneId = $content->getZone()->getId();
			$used[$zoneId][] = $content;
		}
		
		return compact('page', 'zones', 'used');
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
