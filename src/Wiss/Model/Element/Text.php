<?php

namespace Wiss\Model\Element;

/**
 *
 * @author Boy
 */
class Text extends AbstractBuilder
{    
    /**
     * Build a Doctrine class metadata object, that holds all
     * the information to communicate with the database
     * 
     * @return \Doctrin\ORM\Mapping\ClassMetadata
     */
    public function getEntityMetadata()
    {
        
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
}
