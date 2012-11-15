<?php

namespace Wiss\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

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
     * @ORM\Column
     * @Gedmo\Slug(fields={"title"}, unique=true)
	 */
	protected $slug;
    
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
    
	/**
	 *
	 * @ORM\OneToMany(targetEntity="Page", mappedBy="module")
	 */
	protected $pages;	
	
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

    public function getSlug() {
        return $this->slug;
    }

    public function setSlug($slug) {
        $this->slug = $slug;
    }
    
    public function getModels() {
        return $this->models;
    }

    public function setModels($models) {
        $this->models = $models;
    }
    
    public function getPages() {
        return $this->pages;
    }

    public function setPages($pages) {
        $this->pages = $pages;
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
