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
use Zend\Code\Annotation\AnnotationManager;
use Zend\Code\Reflection\ClassReflection;

class ModelController extends AbstractActionController
{
	protected $entityManager;
	
    public function indexAction()
    {
		$models = $this->getEntityManager()->getRepository('Wiss\Entity\Model')->findAll();
		
		return compact('models');
    }
	
	public function editAction()
	{	
		
		
		$em = $this->getEntityManager();
		$repo = $em->getRepository('Wiss\Entity\Model');
		$model = $repo->find($this->params('id'));
		
		$form = new \Wiss\Form\Model;
		
		$form->bind($model);
		
		if($this->getRequest()->isPost()) {
			
			$form->setData($this->getRequest()->getPost());
			
			if($form->isValid()) {
				
				// Save the changes
				$em->persist($model);
				$em->flush();
				
				// Show a flash message
				$this->flashMessenger()->addMessage('The model is now updated');
				
				// Redirect
				$this->redirect()->toRoute('model/edit', array(
					'id' => $model->getId(),
				));
			}
			
		}
		
		return compact('model', 'form');
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
		
		// Return if a model already exists
		if($model) {
			
			// Show a flash message
			$this->flashMessenger()->addMessage('The model is already installed');
				
			// Redirect
			$this->redirect()->toRoute('crud', array(
				'name' => $model->getSlug()
			));
			
			return false;			
		}
		
		// Get data from entity annotations
		$data = $this->getDataFromAnnotations($class);
		$data += array(
			'title' => $title,
			'entity_class' => $class,
		);
		
		// Create the form
		$form = new \Wiss\Form\Model();
		$form->setData($data);
		
		if($this->getRequest()->isPost()) {
			
			$form->setData($this->getRequest()->getPost());
			
			if($form->isValid()) {
				
				// Create the new model
				$model = $this->createModel($form->getData());

				// Show a flash message
				$this->flashMessenger()->addMessage('The model is now installed');
				
				// Redirect
				$this->redirect()->toRoute('model/export', array(
					'name' => $model->getSlug()
				));
			}
		}
		
		
		
		return compact('title', 'form');
	}
	
	/**
	 *
	 * @param string $class
	 * @return array 
	 */
	public function getDataFromAnnotations($class)
	{		
        $parser = new Parser\DoctrineAnnotationParser();
		$parser->registerAnnotation('Wiss\Annotation\Model');
		
        $annotationManager = new AnnotationManager();
		$annotationManager->attach($parser);
		
        $reflection  = new ClassReflection($class);
        $annotations = $reflection->getAnnotations($annotationManager);
		
		foreach($annotations as $annotation) {
			
			if($annotation instanceof \Wiss\Annotation\Model) {
				return array(
					'title_field' => $annotation->getTitleField()
				);
			}
		}	
		
		return array();
	}
	
	/**
	 *
	 * @param array $data
	 * @return \Wiss\Entity\Model
	 */
	public function createModel(Array $data)
	{			
		$em = $this->getEntityManager();
		
		// Create a new model
		$model = new \Wiss\Entity\Model;
		$model->setTitle($data['title']);
		$model->setEntityClass($data['entity_class']);
		$model->setTitleField($data['title_field']);
		$em->persist($model);
		$em->flush();

		// Insert the model in the navigation
		$routeList = $em->getRepository('Wiss\Entity\Route')->findOneBy(array(
			'name' => 'crud'
		));
		$navigation = new \Wiss\Entity\Navigation;
		$navigation->setParent($this->getContentNavigation());
		$navigation->setRoute($routeList);
		$navigation->setLabel($data['title']);
		$navigation->setParams(array(
			'name' => $model->getSlug()
		));			
		$em->persist($navigation);
		$em->flush();
		
		return $model;
	}
	
	/**
	 *
	 * @return \Wiss\Entity\Navigation 
	 */
	public function getContentNavigation()
	{
		$navigation = $this->getNavigation('content', 1);
		
		if(!$navigation) {
			
			$route = $this->getEntityManager()->getRepository('Wiss\Entity\Route')->findOneBy(array(
				'name' => 'module'
			));
			
			// Insert navigation
			$navigation = new \Wiss\Entity\Navigation;
			$navigation->setLabel('Content');
			$navigation->setParent($this->getNavigation('cms', 0));
			$navigation->setRoute($route);
			$this->getEntityManager()->persist($navigation);		
		}
		
		return $navigation;
	}
	
	public function exportAction()
	{		
		// Build the config
		$repo = $this->getEntityManager()->getRepository('Wiss\Entity\Navigation');
		$repo->exportToConfig();		
		
		// Build the config
		$repo = $this->getEntityManager()->getRepository('Wiss\Entity\Page');
		$repo->exportRoutes();

		// Redirect
		$this->redirect()->toRoute('crud', array(
			'name' => $this->params('name')
		));

		return false;
	}
	
	/**
	 *
	 * @param $name OPTIONAL
	 * @param $level OPTIONAL
	 * @return Wiss\Entity\Navigation 
	 */
	public function getNavigation($name, $level = null)
	{
		$repo = $this->getEntityManager()->getRepository('Wiss\Entity\Navigation');
		$params = array(
			'name' => $name
		);
		if($level) {
			$params['lvl'] = $level;
		}
		
		$navigation = $repo->findOneBy($params);	
		
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
