<?php

namespace Wiss\Model\Element;
/**
 * Description of AbstractBuilder
 *
 * @author Boy
 */
class AbstractBuilder implements Builder 
{
    
    /**
     * 
     * @return \Zend\Form
     */
    public function getForm()
    {
        
    }
    
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
     * @return \Zend\Form\Element
     */
    public function getFormElement()
    {
        
    }
}

?>
