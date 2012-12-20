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
     * @var \Wiss\Entity\ModelElement
     */
    protected $modelElement;
    
    /**
     * 
     * @param \Wiss\Entity\ModelElement $modelElement
     */
    public function __construct(\Wiss\Entity\ModelElement $modelElement)
    {
        $this->modelElement = $modelElement;
    }
    
    /**
     * 
     * @return \Wiss\Entity\ModelElement
     */
    public function getModelElement() {
        return $this->modelElement;
    }

    /**
     * 
     * @param \Wiss\Entity\ModelElement $modelElement
     */
    public function setModelElement($modelElement) {
        $this->modelElement = $modelElement;
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
     * Build a form element config
     * 
     * @return array
     */
    public function getFormElementConfig()
    {
        
    }
    
    /**
     * Build a form for configuring the element
     * 
     * @return \Zend\Form\Form
     */
    public function getConfigurationForm()
    {
        
    }
}

?>
