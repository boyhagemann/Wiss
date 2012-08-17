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
    public function prepareElements() 
	{
		// Title
		$title = new Element('title');
		$title->setAttributes(array(
			'type' => 'text',
			'label' => 'Name of the model'
		));
		
		// Title field
		$titleField = new Element('titleField');
		$titleField->setAttributes(array(
			'type' => 'text',
			'label' => 'Which field is used as title?'
		));
		
		// Class
		$class = new Element('class');
		$class->setAttributes(array(
			'type' => 'hidden',
		));

		// Submit
		$submit = new Element('send');
		$submit->setAttributes(array(
			'type'  => 'submit',
			'value' => 'Save',
		));

		$this->add($title);
		$this->add($titleField);
		$this->add($class);
		$this->add($submit);

		$inputFilter = new InputFilter();
		$inputFilter->add(new Input('title'));
		$inputFilter->add(new Input('titleField'));
		$inputFilter->add(new Input('class'));
		$this->setInputFilter($inputFilter);
	}
	
}
