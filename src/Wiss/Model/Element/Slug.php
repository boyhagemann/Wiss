<?php

namespace Wiss\Model\Element;

/**
 *
 * @author Boy
 */
class Slug extends AbstractBuilder
{    
    /**
     * Build a Doctrine class metadata object, that holds all
     * the information to communicate with the database
     * 
     * @return \Doctrin\ORM\Mapping\ClassMetadata
     */
    public function getEntityMetadata()
    {
        $modelElement = $this->getModelElement();
        $config = $modelElement->getConfiguration();
        
        return array(
            'type' => 'string',
            'extensions' => array(
                'Gedmo\Slug' => array(
                    'fields' => $config['fields'],
                )
            )
        );
    }
    
    /**
     * Build a form element that is used to control the model
     * 
     * @return array
     */
    public function getFormElementConfig()
    {        
        $modelElement = $this->getModelElement();
        
        $config = array(
            'type' => 'Zend\Form\Element\Text',
            'name' => $modelElement->getName(),
            'attributes' => array(
                'label' => $modelElement->getLabel(),
            ),
        );
        
        return $config;
    }
    
    /**
     * Build a form for configuring the element
     * 
     * @return \Zend\Form\Form
     */
    public function getConfigurationForm()
    {
        $form = new \Zend\Form\Form();
        $form->setAttribute('class', 'form-horizontal');
        
        $form->add(array(
            'type' => 'Zend\Form\Element\MultiCheckbox',
            'name' => 'fields',
            'attributes' => array(
                'label' => 'Which field?',
                'options' => array(
                    'title' => 'Title',
                ),
            )
        ));
        
        $form->add(array(
            'type' => 'Zend\Form\Element\Submit',
            'name' => 'sumit',
            'attributes' => array(
                'class' => 'btn btn-primary btn-large',
                'value' => 'Save'
            )
        ));
        
        return $form;
    }
}
