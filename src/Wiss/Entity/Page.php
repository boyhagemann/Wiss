<?php

namespace Wiss\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="Wiss\EntityRepository\Page")
 * @ORM\Table(name="wiss_page")
 */
class Page
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
	protected $title;
	
	/**
	 * 
     * @ORM\Column
	 */
	protected $name;

	/**
	 * 
     * @ORM\OneToOne(targetEntity="Route", inversedBy="page")
	 */
	protected $route;
	
    /**
     * @ORM\ManyToOne(targetEntity="Layout")
     */
    protected $layout;
		
	
    /**
     * @ORM\OneToMany(targetEntity="Content", mappedBy="page")
     */
    protected $content;
	
	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function getCreated() {
		return $this->created;
	}

	public function getUpdated() {
		return $this->updated;
	}

	public function getTitle() {
		return $this->title;
	}

	public function setTitle($title) {
		$this->title = $title;
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

	public function setRoute(Route $route) {
		$this->route = $route;
	}

	public function getLayout() {
		return $this->layout;
	}

	public function setLayout(Layout $layout) {
		$this->layout = $layout;
	}	
	
	public function getContent() {
		return $this->content;
	}

	public function setContent($content) {
		$this->content = $content;
	}
	
}
