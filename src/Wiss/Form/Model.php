<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Wiss\Form;

use Zend\Form\Element;
use Zend\Form\Fieldset;
use Zend\Form\Form;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

class Model extends Form
{	
    protected $entityManager;
	
    public function __construct($em)
    {
		$this->setEntityManager($em);
		
		parent::__construct('model');
		
		$this->setHydrator(new \Zend\Stdlib\Hydrator\ClassMethods());
		$this->setAttribute('class', 'form-horizontal');
		
		// Title
		$title = new Element('title');
		$title->setAttributes(array(
			'type' => 'text',
			'label' => 'Name of the model'
		));
		
		// Title field
		$titleField = new Element\Select('title_field');
		$titleField->setAttributes(array(
			'type' => 'select',
			'label' => 'Which field is used as title?'
		));
		
		// Class
		$class = new Element('entity_class');
		$class->setAttributes(array(
			'type' => 'text',
			'label' => 'Class',
		));

		// Submit
		$submit = new Element('submit');
		$submit->setAttributes(array(
			'type'  => 'submit',
			'value' => 'Install',
			'class' => 'btn btn-primary btn-large',
		));

		$this->add($title);
		$this->add($titleField);
		$this->add($class);
		$this->add($submit);

		$inputFilter = new InputFilter();
		$inputFilter->add(new Input('title'));
		$inputFilter->add(new Input('title_field'));
		$inputFilter->add(new Input('entity_class'));
		$this->setInputFilter($inputFilter);
	}
	
	/**
	 *
	 * @param type $data 
	 */
	public function setData($data)
	{
		parent::setData($data);
		
		if(isset($data['entity_class'])) {
			$this->setTitleFieldOptions($data['entity_class']);
		}
		
		$elements = new Fieldset('elements');
		
		$inputFilter = $this->getInputFilter();
		$inputFilter->add(new Input('elements', array(
			'required' => false,
		)));
			
		
		foreach($this->getFieldMapping($data['entity_class']) as $field) {
						
			$fieldset = new Fieldset($field['fieldName']);
			$fieldset->setOptions(array(
				'legend' => $field['fieldName'],
			));
			
			// Add the select field with the available elements
			$select = new Element\Select('type');
			$select->setLabel($field['fieldName']);
			$select->setValueOptions(array(
				''								=> 'No element assigned yet...',
				'Wiss\Form\ElementConfig\Text'		=> 'Text',
				'Wiss\Form\ElementConfig\Textarea'	=> 'Textarea',
				'Wiss\Form\ElementConfig\DatePicker'	=> 'DatePicker',
			)); 
                        $select->setAttributes(array(
                            'class' => 'form-class',
                        ));
			$fieldset->add($select);
			
			// Add the trigger button to show the modal window
			$button = new Element\Button('trigger');
			$button->setOptions(array(
                            'label' => 'Configure',
			));
                        $button->setAttributes(array(
                            'class' => 'element-config-trigger',
//                            'data-toggle' => 'modal',
//                            'data-target' => "#myModal",
                            'data-remote' => $data['element-config-url'],
                        ));
			$fieldset->add($button);
			
			// Store the result of the modal window form in a hidden element
			$fieldset->add(new Element\Hidden('configuration'));
                        
                        
			// Add the hidden config element
			$config = new Element\Hidden('button');
			$fieldset->add($config);
			
			$elements->add($fieldset);		
		}
		
		$this->add($elements);
		
		
	}
	
	/**
	 *
	 * @param string $class
	 * @return array 
	 */
	public function getFieldMapping($class)
	{
		$meta = $this->getEntityManager()->getClassMetadata($class);
		return $meta->fieldMappings;
	}
	
	/**
	 *
	 * @param string $class 
	 */
	public function setTitleFieldOptions($class)
	{
		$options = array('' => 'Choose a field name...');
		foreach($this->getFieldMapping($class) as $field) {
			$options[$field['fieldName']] = $field['fieldName'];
		}
		$this->get('title_field')->setAttribute('options', $options);		
	}
	
	public function getEntityManager() {
		return $this->entityManager;
	}

	public function setEntityManager($entityManager) {
		$this->entityManager = $entityManager;
	}

}
