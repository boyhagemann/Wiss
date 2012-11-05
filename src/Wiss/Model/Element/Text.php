<?php

namespace Wiss\Model\Element;

/**
 *
 * @author Boy
 */
class Text extends AbstractBuilder
{
    /**
     * 
     * @return \Zend\Form\Form
     */
    public function getForm() 
    {
        $form = new \Zend\Form\Form;
        
        $form->add(array(
            'type' => 'Zend\Form\Element\Text',
            'name' => 'size',
            'attributes' => array(),
            'options' => array(
                'label' => 'Size',
            ),
        ));
        
        return $form;
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
