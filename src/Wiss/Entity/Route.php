<?php

namespace Wiss\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="Wiss\EntityRepository\Route")
 */
class Route
{
	/**
	 * 
	 * @ORM\ID
	 * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
	 */
	protected $id;

    /**
     * @var datetime $created
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $created;
	
    /**
     * @var datetime $updated
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    private $updated;
	
	/**
	 * 
     * @ORM\Column
	 */
	protected $name;

	/**
	 * 
     * @ORM\Column
	 */
	protected $route;
	
    /**
     * @ORM\OneToOne(targetEntity="Page", mappedBy="route")
     */
    private $page;
	
	/**
	 * 
     * @ORM\Column(type="array")
	 */
	protected $defaults;
	
	/**
	 * 
     * @ORM\Column(type="array")
	 */
	protected $constraints;

	/**
     * @ORM\ManyToOne(targetEntity="Page", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $parent;	

    /**
     * @ORM\OneToMany(targetEntity="Page", mappedBy="parent")
     */
    protected $children;

	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function getCreated() {
		return $this->created;
	}

	public function setCreated($created) {
		$this->created = $created;
	}

	public function getUpdated() {
		return $this->updated;
	}

	public function setUpdated($updated) {
		$this->updated = $updated;
	}

	public function getName() {
		return $this->name;
	}

	public function setName($name) {
		$this->name = $name;
	}

	public function getRoute() {
		return $this->route;
	}

	public function setRoute($route) {
		$this->route = $route;
	}

	public function getPage() {
		return $this->page;
	}

	public function setPage(Page $page) {
		$this->page = $page;
	}

	public function getDefaults() {
		return $this->defaults;
	}

	public function setDefaults(Array $defaults) {
		$this->defaults = $defaults;
	}

	public function getConstraints() {
		return $this->constraints;
	}

	public function setConstraints(Array $constraints) {
		$this->constraints = $constraints;
	}
	public function getParent() {
		return $this->parent;
	}

	public function setParent($parent) {
		$this->parent = $parent;
	}

	public function getChildren() {
		return $this->children;
	}

	public function setChildren($children) {
		$this->children = $children;
	}

}
