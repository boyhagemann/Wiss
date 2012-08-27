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
use Zend\Form\Form;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

class Install extends Form
{		
    public function __construct()
    {		
		parent::__construct('install');
				
        $this->setAttribute('class', 'form-horizontal');
		
		// User
		$this->add(array(
			'name' => 'user',
			'type' => 'Zend\Form\Element\Text',
			'attributes' => array(
				'label' => 'Username'
			),
		));
				
		
		// Password
		$this->add(array(
			'name' => 'password',
			'type' => 'Zend\Form\Element\Text',
			'attributes' => array(
				'label' => 'Password',
				'required' => false,
			),
		));
		
		// Table
		$this->add(array(
			'name' => 'dbname',
			'type' => 'Zend\Form\Element\Text',
			'attributes' => array(
				'label' => 'DB table'
			),
		));
		
		// Submit
		$this->add(array(
			'name' => 'submit',
			'type' => 'Zend\Form\Element\Submit',
			'attributes' => array(
				'value' => 'Install',
				'class' => 'btn btn-primary',
			),
		));
		
		
		$inputFilter = new InputFilter();
		$inputFilter->add(new Input('user', array(
			'required' => true,
		)));
		
		$password = new Input('password', array(
			'required' => false,
			'allowEmpty' => true,
		));
		$password->setAllowEmpty(true);
		
		$inputFilter->add($password);
		$inputFilter->add(new Input('dbname', array(
			'required' => true,
		)));
		
		$this->setInputFilter($inputFilter);
	}
	
}
