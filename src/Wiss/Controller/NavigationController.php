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
	
    /**
     * 
     * @return array
     */
    public function indexAction()
    {
        $em = $this->getEntityManager();
		$repo = $em->getRepository('Wiss\Entity\Navigation');                
        $options = array(
            'decorate' => true,
            'rootOpen' => '<ul>',
            'rootClose' => '</ul>',
            'childOpen' => function($node) {
                $class = ($node['__children'] ? 'folder' : '');
                $li = sprintf('<li id="%d" class="%s">', $node['id'], $class);
                return $li;
            },
            'childClose' => '</li>',
            'nodeDecorator' => function($node) use ($repo) {
                $model = $repo->find($node['id']);
                $route = $model->getRoute();
                if(!$route) {
                    return $node['label'];
                }
                
                $url = $this->url()->fromRoute('wiss/page/content', array('id' => $route->getPage()->getId()));
                return sprintf('<a href="%s">%s</a>', $url, $node['label']);
            }
        );
        $tree = $repo->childrenHierarchy( null, false, $options);
        
		return compact('tree');
    }    
    
	/**
	 *
	 * @return \Zend\View\Model\JsonModel 
	 */
    public function moveAction()
    {
        $viewModel = new \Zend\View\Model\JsonModel;
        $viewModel->setVariables(array());
        return $viewModel;
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
