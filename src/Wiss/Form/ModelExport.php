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

class ModelExport extends Form
{	
	
	/**
	 * 
	 */
    public function prepareElements()
    {                		
		$this->setHydrator(new \Zend\Stdlib\Hydrator\ClassMethods());
		$this->setAttribute('class', 'form-horizontal');
		
		$fieldsetForm = new \Zend\Form\Fieldset('form');		
		
		// Generate form
		$fieldsetForm->add(array(
			'type' => 'Zend\Form\Element\Checkbox',
			'name' => 'generate_form',
			'attributes' => array(
				'class' => 'checkbox',
				'value' => 1,
				'help-block' => 'Generate a form based on the given elements',
			),
		));
		
		
		$fieldsetController = new \Zend\Form\Fieldset('controller');	
		$fieldsetModel = new \Zend\Form\Fieldset('model');	
		$fieldsetConfig = new \Zend\Form\Fieldset('configuration');	
		
		$this->add($fieldsetForm);
		$this->add($fieldsetController);
		$this->add($fieldsetModel);
		$this->add($fieldsetConfig);
		
		// Submit
		$this->add(array(
			'type' => 'Zend\Form\Element\Submit',
			'name' => 'submit',
			'attributes' => array(
				'value' => 'Export',
				'class' => 'btn btn-primary btn-large',
			),
		));

		$inputFilter = new InputFilter();
		$inputFilter->add(new Input('generate'));
		$this->setInputFilter($inputFilter);
				
	}
	
}
