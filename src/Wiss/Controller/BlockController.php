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
        $found = array();
        return compact('found');
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
//        $form->get('module')->getProxy()->setObjectManager($em);
//        $form->get('node')->getProxy()->setObjectManager($em);
                
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
