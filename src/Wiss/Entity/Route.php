<?php

namespace Wiss\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="Wiss\EntityRepository\Route")
 * @Gedmo\Tree(type="nested")
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
     * @Gedmo\TreeLeft
     * @ORM\Column(name="lft", type="integer")
     */
    private $lft;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     */
    private $lvl;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(name="rgt", type="integer")
     */
    private $rgt;

    /**
     * @Gedmo\TreeRoot
     * @ORM\Column(name="root", type="integer", nullable=true)
     */
    private $root;
	
	/**
     * @ORM\ManyToOne(targetEntity="Route", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $parent;	

    /**
     * @ORM\OneToMany(targetEntity="Route", mappedBy="parent")
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
	
	public function getLft() {
		return $this->lft;
	}

	public function setLft($lft) {
		$this->lft = $lft;
	}

	public function getLvl() {
		return $this->lvl;
	}

	public function setLvl($lvl) {
		$this->lvl = $lvl;
	}

	public function getRgt() {
		return $this->rgt;
	}

	public function setRgt($rgt) {
		$this->rgt = $rgt;
	}

	public function getRoot() {
		return $this->root;
	}

	public function setRoot($root) {
		$this->root = $root;
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
