<?php

namespace Wiss\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="wiss_content")
 */
class Content
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
	protected $title;

	/**
	 *
     * @ORM\ManyToOne(targetEntity="Page")
	 */
	protected $page;
	
	/**
	 *
     * @ORM\ManyToOne(targetEntity="Zone")
	 */
	protected $zone;
	
	/**
	 *
     * @ORM\ManyToOne(targetEntity="Block", inversedBy="content")
	 */
	protected $block;
	
	/**
	 *
     * @ORM\Column(type="array")
	 */
	protected $defaults = array();
		
    /**
     *
     * @ORM\Column(type="integer")
     */
    protected $position = 0;

    /**
     * 
     */
    public function __construct()
    {
        $this->defaults = new ArrayCollection();
    }
    
	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
	}
		
	public function getTitle() {
		return $this->title;
	}

	public function setTitle($title) {
		$this->title = $title;
	}

	public function getPage() {
		return $this->page;
	}

	public function setPage(Page $page) {
		$this->page = $page;
	}
	
	public function getZone() {
		return $this->zone;
	}

	public function setZone(Zone $zone) {
		$this->zone = $zone;
	}

	public function getBlock() {
		return $this->block;
	}

	public function setBlock(Block $block) {
		$this->block = $block;
	}
	
	public function getDefaults() {
		return $this->defaults;
	}

	public function setDefaults($defaults) {
		$this->defaults = $defaults;
	}
    
    public function getPosition() {
        return $this->position;
    }

    public function setPosition($position) {
        $this->position = $position;
    }
    
}
