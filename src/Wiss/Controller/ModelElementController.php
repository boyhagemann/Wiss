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
        // Get the form to create the element properties
        $form = $this->getServiceLocator()->get('Wiss\Form\ModelElement');
        $form->prepareElements();
        
        // Attach a new ModelElement entity to the form, to set and
        // get the values directly from the form and to the entity
        $form->bind(new \Wiss\Entity\ModelElement);
                
        // Get the model where the element is part of
        $em = $this->getEntityManager();
        $model = $em->find('Wiss\Entity\Model', $this->params('model'));
        
        // Check if data is posted
        if ($this->getRequest()->isPost()) {
			
            // Set the raw post data as form values
            $form->setData($this->getRequest()->getPost());

            // Check if the posted data is valid
            if ($form->isValid()) {
                
                // Get the modelElement from the form
                $modelElement = $form->getData();
                $modelElement->setModel($model);
                
                // Save the new modelElement
                $em->persist($modelElement);
                $em->flush();
                
                // Show a flash message
                $this->flashMessenger()->addMessage('Element saved!');
                
                // Redirect
                $this->redirect()->toRoute('wiss/model-element/config', array(
                    'id' => $modelElement->getId()
                ));
            }
        }
        
        // Return the view variables in an array
        return compact('form', 'model');
    }

    /**
     *
     * @return array 
     */
    public function propertiesAction() 
	{
        // Get the correct model element
        $em = $this->getEntityManager();
        $modelElement = $em->find('Wiss\Entity\ModelElement', $this->params('id'));
        $model = $modelElement->getModel();
        
        // Get the basic properties form
        $form = $this->getServiceLocator()->get('Wiss\Form\ModelElement');
        $form->prepareElements();
        $form->bind($modelElement);

        if ($this->getRequest()->isPost()) {
			
            $form->setData($this->getRequest()->getPost());

            if ($form->isValid()) {
                                
                // Save the new modelElement
                $em->persist($form->getData());
                $em->flush();                
                
                // Show a flash message
                $this->flashMessenger()->addMessage('Element properties saved!');
                
                // Redirect
                $this->redirect()->toRoute('wiss/model-element/properties', array(
                    'id' => $this->params('id')
                ));
            }
        }
        
        // Return the view variables in an array
        return compact('form', 'modelElement', 'model');
    }
    
    /**
     *
     * @return array 
     */
    public function configAction() 
	{
        // Get the correct model element
        $em = $this->getEntityManager();
        $modelElement = $em->find('Wiss\Entity\ModelElement', $this->params('id'));
        $model = $modelElement->getModel();
        
        // Get the builder that is used to build the form element and the entity mapping
        $builder = $this->getServiceLocator()->get($modelElement->getBuilderClass());
        
        // Get the config form from the builder, to enter the needed options
        // to build the form element or entity mapping
        $form = $builder->getConfigurationForm();
        
        // Set the config form defaults, based on the existing model 
        // element config
        $form->setData((array)$modelElement->getConfiguration());
        
        if ($this->getRequest()->isPost()) {
			
            $form->setData($this->getRequest()->getPost());

            if ($form->isValid()) {
                                
                // Save the new modelElement
                $modelElement->setConfiguration($form->getData());
                $em->persist($modelElement);
                $em->flush();
                
                // Show a flash message
                $this->flashMessenger()->addMessage('Element config saved!');
                
                // Redirect
                $this->redirect()->toRoute('wiss/model-element/config', array(
                    'id' => $this->params('id')
                ));
            }
        }
        
        // Return the view variables in an array
        return compact('form', 'builder', 'modelElement', 'model');
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
