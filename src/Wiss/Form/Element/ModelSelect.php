<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Wiss\Form\Element;

use Zend\Form\ElementInterface;
use Zend\Form\Element;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

class ModelSelect extends Element\Select implements ElementInterface, ServiceLocatorAwareInterface
{	
    protected $serviceLocator;
    
    protected $modelName;
    protected $modelKey = 'id';
    protected $modelLabel = 'title';

    
    /**
     * Set options for an element. Accepted options are:
     * - label: label to associate with the element
     * - label_attributes: attributes to use when the label is rendered
     *
     * @param  array|\Traversable $options
     * @return Element|ElementInterface
     * @throws Exception\InvalidArgumentException
     */
    public function setOptions($options)
    {
        foreach($options as $key => $option) {
            
            switch(strtolower($key)) {
                case 'modelname': 
                    $this->setModelName($option); 
                    break;
                case 'modelkey': 
                    $this->setModelKey($option); 
                    break;
                case 'modellabel': 
                    $this->setModelLabel($option); 
                    break;
            }
        }
        return parent::setOptions($options);
    }
    
    public function getModelName() {
        return $this->modelName;
    }

    public function setModelName($modelName) {
        $this->modelName = $modelName;
    }        

    public function getValueOptions() 
    {
        if($this->valueOptions) {
            return $this->valueOptions;
        }
                
        $em = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
        $model = $em->getRepository('Wiss\Entity\Model')->findOneBy(array(
            'slug' => $modelName,
        ));
        $entities = $em->getRepository($model->getEntityClass())->findAll();
                
        $valueOptions = array();
        foreach($entities as $entity) {
            $getterKey = 'get' . ucfirst($this->modelKey);
            $getterLabel = 'get' . ucfirst($this->modelLabel);
            $valueOptions[$entity->$getterKey()] = $entity->$getterLabel();
        }
        $this->setValueOptions($valueOptions);
        
        return $this->valueOptions;
    }
    
    public function getModelKey() {
        return $this->modelKey;
    }

    public function setModelKey($modelKey) {
        $this->modelKey = $modelKey;
    }

    public function getModelLabel() {
        return $this->modelLabel;
    }

    public function setModelLabel($modelLabel) {
        $this->modelLabel = $modelLabel;
    }

    public function getServiceLocator() {
		return $this->serviceLocator;
	}

	public function setServiceLocator(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator) {
		$this->serviceLocator = $serviceLocator;
	}
}
