<?php

namespace Wiss\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Wiss\EntityRepository\Module")
 * @ORM\Table(name="wiss_module")
 * @ORM\HasLifecycleCallbacks
 */
class Module
{
	/**
	 * 
	 * @ORM\ID
	 * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
	 */
	protected $id;

	/**
	 * 
     * @ORM\Column
	 */
	protected $name;

	/**
	 * 
     * @ORM\Column
	 */
	protected $title;
    
	/**
	 * 
     * @ORM\Column(type="boolean")
	 */
	protected $locked = false;    
    
	/**
	 *
	 * @ORM\OneToMany(targetEntity="Model", mappedBy="module")
	 */
	protected $models;	
	
	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
	}
	public function getName() {
		return $this->name;
	}

	public function setName($name) {
		$this->name = $name;
	}
    
    public function getTitle() {
        return $this->title;
    }

    public function setTitle($title) {
        $this->title = $title;
    }
    
    public function getModels() {
        return $this->models;
    }

    public function setModels($models) {
        $this->models = $models;
    }
    
    public function isLocked() {
        return $this->locked;
    }

    public function setLocked($locked) {
        $this->locked = $locked;
    }

    /**
     * Build the name of the model based on the title
     * 
     * @ORM\PrePersist
     */
    public function canonicalizeName()
    {
        // Make the name camelCased
        $filter = new \Zend\Filter\Word\SeparatorToCamelCase();
        $filter->setSeparator(' ');
        
        // Set the name
        $this->name = $filter->filter($this->title);
    }
}
