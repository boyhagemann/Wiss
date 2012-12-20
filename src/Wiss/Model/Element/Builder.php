<?php

namespace Wiss\Model\Element;

/**
 *
 * @author Boy
 */
interface Builder 
{        
    /**
     * Build a Doctrine class metadata object, that holds all
     * the information to communicate with the database
     * 
     * @return \Doctrin\ORM\Mapping\ClassMetadata
     */
    public function getEntityMetadata();
    
    /**
     * Build a form element config
     * 
     * @return array
     */
    public function getFormElementConfig();
    
    /**
     * Build a form for configuring the element
     * 
     * @return \Zend\Form\Form
     */
    public function getConfigurationForm();
}
