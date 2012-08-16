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

class ModelController extends AbstractActionController
{
	protected $entityManager;
	
    public function indexAction()
    {
		$models = $this->getEntityManager()->getRepository('Wiss\Entity\Model')->findAll();
		
		return compact('models');
    }
		
	public function installAction()
	{
		$class = $this->params('class');
		$class = str_replace('-', '\\', $class);
		$title = explode('\\', $class);
		$title = end($title);
		
		$em = $this->getEntityManager();
		$model = $em->getRepository('Wiss\Entity\Model')->findOneBy(array(
			'entityClass' => $class
		));
		
		if(!$model) {
			
			// Create a new model
			$model = new \Wiss\Entity\Model;
			$model->setTitle($title);
			$model->setEntityClass($class);
			$model->setTitleField('title');
			$em->persist($model);
			
			// Insert the model in the navigation
			$routeList = $em->getRepository('Wiss\Entity\Route')->findOneBy(array(
				'name' => 'model/list'
			));
			$navigation = new \Wiss\Entity\Navigation;
			$navigation->setParent($this->getParentNavigation());
			$navigation->setRoute($routeList);
			$navigation->setLabel($title);
			$navigation->setParams(array(
				'class' => $class
			));			
			$em->persist($navigation);
			
			$em->flush();
			
		}
		
		// Redirect
		$this->redirect()->toRoute('model/list', array(
			'name' => $model->getSlug()
		));
		
		return false;
	}
	
	/**
	 *
	 * @return Wiss\Entity\Navigation 
	 */
	public function getParentNavigation()
	{
		$navigation = $this->getEntityManager()->getRepository('Wiss\Entity\Navigation')->findOneBy(array(
			'name' => 'cms'
		));		
		
		return $navigation;
	}
	
	public function uninstalledAction()
	{
		$scanned = $this->getScannedEntities();
		$models = $this->getInstalledModels();
		
		// Unset each model that already is installed
		foreach($models as $model) {
			unset($scanned[$model->getEntityClass()]);
		}
		
		return compact('scanned');
	}
	
	/**
	 *
	 * @return Doctrine\ORM\Collection
	 */
	public function getInstalledModels()
	{		
		$models = $this->getEntityManager()->getRepository('Wiss\Entity\Model')->findAll();
		return $models;
	}
	
	public function getScannedEntities()
	{
		$config = $this->getServiceLocator()->get('applicationconfig');
		$paths = $config['module_listener_options']['module_paths'];
		$drivers = $this->getEntityManager()->getConfiguration()->getMetadataDriverImpl()->getDrivers();
		$entities = array();
							
		foreach($paths as $basepath) {
					
			foreach($drivers as $namespace => $driver) {

				foreach($driver->getPaths() as $path) {

					$filePattern = '%s/%s/src/%s%s';
					$file = sprintf($filePattern, $basepath, $namespace, $namespace, $path);
					
					if(!file_exists($file)) {
						continue;
					}
					
					$directory = new \DirectoryIterator($file);
					foreach($directory as $file) {
						
						if($file->isDot() || $file->isDir()) {
							continue;
						}
									
						try {							
							
							$scanner = new \Zend\Code\Scanner\FileScanner($file->getPathname());
							foreach($scanner->getClassNames() as $class) {
								
								try {
									$entity = $this->getEntityManager()->getRepository($class);
									$entities[$class] = $entity;
								}
								catch(\Exception $e) {
									
								}
							}
							
						}
						catch(\Exception $e) {
						}

					}
				}
				
			}
			
		}
		
		return $entities;
	}
	
	public function listAction()
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
