<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Wiss\Controller;

use Wiss\Entity\Model;
use Zend\Mvc\Controller\AbstractActionController;
use Doctrine\ORM\EntityManager;

class ModelElementController extends AbstractActionController {

    /**
     *
     * @param EntityManager $entityManager
     */
    protected $entityManager;

    /**
     *
     * @return array 
     */
    public function createAction() 
	{
        $form = $this->getServiceLocator()->get('Wiss\Form\ModelElement');
        $form->prepareElements();
        $form->bind(new \Wiss\Entity\ModelElement);

                
        // Get the model where the element is part of
        $em = $this->getEntityManager();
        $model = $em->find('Wiss\Entity\Model', $this->params('model'));
        
        if ($this->getRequest()->isPost()) {
			
            $form->setData($this->getRequest()->getPost());

            if ($form->isValid()) {
                
                // Get the modelElement from the form
                $modelElement = $form->getData();
                $modelElement->setModel($model);
                
                // Save the new modelElement
                $em->persist($modelElement);
                $em->flush();
                
                // Redirect
                $this->redirect()->toRoute('wiss/model-element/config', array(
                    'id' => $modelElement->getId()
                ));
            }
        }
        return compact('form');
    }

    /**
     *
     * @return array 
     */
    public function propertiesAction() 
	{
        $form = $this->getServiceLocator()->get('Wiss\Form\ModelElement');
        $form->prepareElements();

        return compact('form');
    }
    
    /**
     *
     * @return array 
     */
    public function configAction() 
	{
        // Get the correct model element
        $modelElement = $this->getEntityManager()->find('Wiss\Entity\ModelElement', $this->params('id'));
        
        // Get the builder that is used to build the form element and the entity mapping
        $builder = $this->getServiceLocator()->get($modelElement->getBuilderClass());
        
        // Get the config form from the builder, to enter the needed options
        // to build the form element or entity mapping
        $form = $builder->getForm();
        
        // Set the config form defaults, based on the existing model 
        // element config
        $form->setData($modelElement->getConfiguration());
        
        // Return the view variables in an array
        return compact('form', 'builder', 'modelElement');
    }

    /**
     *
     * @param EntityManager $entityManager 
     */
    public function setEntityManager(EntityManager $entityManager) {
        $this->entityManager = $entityManager;
    }

    /**
     *
     * @return EntityManager 
     */
    public function getEntityManager() {
        return $this->entityManager;
    }

}
