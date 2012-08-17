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
use Zend\Form\Annotation\AnnotationBuilder;
use Zend\Code\Annotation\Parser;

class CrudController extends AbstractActionController
{
	protected $entityManager;
	
    public function indexAction()
	{
		$repo = $this->getEntityManager()->getRepository('Wiss\Entity\Model');
		$model = $repo->findOneBy(array('slug' => $this->params('name')));
		
		$entityClass = $model->getEntityClass();
		$entities = $this->getEntityManager()->getRepository($entityClass)->findAll();
		
		$labelGetter = 'get' . ucfirst($model->getTitleField());
		
		return compact('model', 'entities', 'labelGetter');
	}
		
	public function editAction()
	{
		$repo = $this->getEntityManager()->getRepository('Wiss\Entity\Model');
		$model = $repo->findOneBy(array('slug' => $this->params('name')));
		
		$entityClass = $model->getEntityClass();
		$entity = $this->getEntityManager()->find($entityClass, $this->params('id'));
		
		
		$listener = new \Wiss\Form\Annotation\ElementAnnotationsListener;
		$builder = new AnnotationBuilder();
//		$builder->getEventManager()->attachAggregate($listener);
		
        $parser = new Parser\DoctrineAnnotationParser();
		$parser->registerAnnotation('Wiss\Form\Mapping\Text');
		$builder->getAnnotationManager()->attach($parser);
		
		
		$form = $builder->createForm($entityClass);
//		$form = $builder->createForm('Wiss\Entity\User');
		\Zend\Debug\Debug::dump($form); exit;
							
		return compact('model', 'entity', 'form');
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
