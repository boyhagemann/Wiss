<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Wiss\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class NavigationController extends AbstractActionController
{
	protected $entityManager;
	
    public function indexAction()
    {
        $controller = $this;
		$repo = $this->getEntityManager()->getRepository('Wiss\Entity\Navigation');
//		$tree = $repo->childrenHierarchy(
//			null, /* starting from root nodes */
//			false, /* load all children, not only direct */
//			array(
//				'rootOpen' => '<ul class="tree">',
//				'decorate' => true,    
//				'nodeDecorator' => function($node) use($repo, $controller) {
//					
//					$node = $repo->find($node['id']);
//					$label = $node->getLabel();
//					
//					if(!$node->getParent()) {
//						return sprintf('<a href="">%s</a>', $label);
//						return $label;
//					}
//					else {						
//						$id = $node->getRoute()->getPage()->getId();
//						$url = $controller->url()->fromRoute('wiss/page/content', array('id' => $id));
//						return sprintf('<h3><a href="%s">%s</a></h3>', $label, $url, $label);
//					}
//					
//				}
//			)
//		);
                
        $tree = $repo->children();
        
//        \Zend\Debug\Debug::dump($tree); exit;
            
		return compact('tree');
    }
			
	/**
	 *
	 * @param \Doctrine\ORM\EntityManager $entityManager 
	 */
	public function setEntityManager(\Doctrine\ORM\EntityManager $entityManager)
	{
		$this->entityManager = $entityManager;
	}
	
	/**
	 *
	 * @return type 
	 */
	public function getEntityManager()
	{
		return $this->entityManager;
	}
}
