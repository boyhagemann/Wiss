<?php

namespace Wiss\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="Wiss\EntityRepository\Navigation")
 * @ORM\Table(name="wiss_navigation")
 * @Gedmo\Tree(type="nested")
 */
class Navigation
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
	protected $label;

	/**
	 * 
     * @ORM\Column
     * @Gedmo\Slug(fields={"label"}, unique=false)
	 */
	protected $name;
	
	/**
	 *
     * @ORM\ManyToOne(targetEntity="Route")
	 */
	protected $route;
	
	/**
	 * 
     * @ORM\Column(type="array")
	 */
	protected $params;
	
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
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="Navigation", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="Navigation", mappedBy="parent")
     * @ORM\OrderBy({"lft" = "ASC"})
     */
    private $children;
	
	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function getLabel() {
		return $this->label;
	}

	public function setLabel($label) {
		$this->label = $label;
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

	public function getParams() {
		return $this->params;
	}

	public function setParams($params) {
		$this->params = $params;
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
    
    public function getLevel() {
        return $this->lvl;
    }

    public function getLeft() {
        return $this->lft;
    }

    public function getRight() {
        return $this->rgt;
    }


}
