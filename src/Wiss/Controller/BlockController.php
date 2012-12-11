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
use Wiss\Annotation\Block;
use Wiss\Annotation\Content;

class BlockController extends AbstractActionController
{
	protected $entityManager;
		
    /**
     * 
     * @Content(action="scan", zone="sidebar")
     */
    public function indexAction()
    {						
        $em = $this->getEntityManager();
		$blocks = $em->getRepository('Wiss\Entity\Block')->findAll();
		
		return compact('blocks');
    }
		
    /**
     * 
     * @Block(title="Available blocks")
     */
    public function availableAction()
    {						
        $em = $this->getEntityManager();
		$blocks = $em->getRepository('Wiss\Entity\Block')->findBy(array(
            'available' => true
        ));
		
		return compact('blocks');
    }
    
    /**
     * 
     * @Block(title="Scanned blocks")
     */
    public function scanAction()
    {
        $em = $this->getEntityManager();        
        $repo = $em->getRepository('Wiss\Entity\Block');
                
        // Get the scanned blocks
        $controllerLoader = $this->getServiceLocator()->get('controllerloader');
        $blocks = $repo->scanControllers($controllerLoader);
        
        // Get the blocks that are already registered
        $used = $em->getRepository('Wiss\Entity\Block')->findBy(array(
            'available' => true
        ));
        
        /// Filter the blocks that are already available
        foreach($used as $block) {
            $key = $block->getSlug();
            if(isset($blocks[$key])) {
                unset($blocks[$key]);
            }
        }
        
        if(!$blocks) {
            return false;
        }
        
        return compact('blocks');
    }
    
    /**
     * 
     * @return boolean
     */
    public function createAction()
    {
        $em = $this->getEntityManager();        
        $repo = $em->getRepository('Wiss\Entity\Block');
        $form = $this->getForm();
        $key = $this->params('key');
                
        if($key) {

            // Get the scanned blocks
            $controllerLoader = $this->getServiceLocator()->get('controllerloader');
            $blocks = $repo->scanControllers($controllerLoader);
            $repo->saveBlocks($blocks);

            // Show a flash message
            $this->flashMessenger()->addMessage('New block added succesfully!');

            // Redirect
            $this->redirect()->toRoute('wiss/block');
        }
        
        return false;
    }
    
    /**
     * 
     * @return array
     */
    public function propertiesAction()
    {
        // Get the block entity
        $em = $this->getEntityManager();
        $block = $em->getRepository('Wiss\Entity\Block')->find($this->params('id'));
        
        // Get the form
        $form = $this->getForm();
        $form->bind($block);
        
        if($this->getRequest()->isPost()) {
            
            $form->setData($this->getRequest()->getPost());
            
            if($form->isValid()) {
                
                // Save the entity
                $em->persist($form->getData());
                $em->flush();
                
                // Show a flash message
                $this->flashMessenger()->addMessage('Block properties are updated!');
                
                // Redirect
                $this->redirect()->toRoute('wiss/block/properties', array(
                    'id' => $block->getId(),
                ));
            }
            
        }
        
        return compact('form', 'block');
    }
    
    /**
     * 
     * @return \Wiss\Form\Model
     */
    public function getForm()
    {
        $sl = $this->getServiceLocator();
        $em = $this->getEntityManager();
        $hydrator = new EntityHydrator($this->getEntityManager());
        
        $form = $sl->get('Wiss\Form\Block'); 
		$form->setAttribute('class', 'form-horizontal');
		$form->setHydrator($hydrator);   
        $form->prepareElements();                
                
        return $form;
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
