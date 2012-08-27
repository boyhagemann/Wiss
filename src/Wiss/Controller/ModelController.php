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
use Zend\Code\Generator\FileGenerator;

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
		
	/**
	 *
	 * @return array 
	 */
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
//		if($model) {
//			
//			// Show a flash message
//			$this->flashMessenger()->addMessage('The model is already installed');
//				
//			// Redirect
//			$this->redirect()->toRoute('cms/content/' . $model->getSlug());
//			
//			return false;			
//		}
		
		// Get data from entity annotations
		$data = $this->getDataFromAnnotations($class);
		$data += array(
			'title' => $title,
			'entity_class' => $class,
		);
		
		// Create the form
		$form = new \Wiss\Form\Model($em);
		$form->setData($data);
		
		if($this->getRequest()->isPost()) {
			
			$form->setData($this->getRequest()->getPost());
			
			if($form->isValid()) {				
				
				// Add the model form and controller class
				$data = $form->getData() + array(
					'form_class'		=> $this->createForm($form),
					'controller_class'	=> $this->createController($form)
				);
				
				// Create the new model
				$model = $this->createModel($data);

				// Create navigation for the model
				$this->createRoutes($model);

				// Create navigation for the model
				$this->createNavigation($model, $data);
				
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
	 * @return boolean 
	 */
	public function exportAction()
	{		
		// Build the config
		$em = $this->getEntityManager();
		$em->getRepository('Wiss\Entity\Navigation')->export();		
		$em->getRepository('Wiss\Entity\Page')->export();

		// Redirect
		$this->redirect()->toRoute('model');

		return false;
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
		$model->setFormClass($data['form_class']);
		$model->setControllerClass($data['controller_class']);
		$em->persist($model);
		$em->flush();
		
		return $model;
	}
	
	/**
	 * 
	 * @param \Wiss\Form\Model $form
	 * @return string
	 */
	public function createForm(\Wiss\Form\Model $form)
	{
		$data = $form->getData();
		$elements = $data['elements'];
		$className = substr($data['entity_class'], 1 + strrpos($data['entity_class'], '\\'));
					
		// Create the body for in the __construct method
		$body = sprintf('parent::__construct(\'%s\');', $className) . PHP_EOL . PHP_EOL;
		$body .= '$this->setHydrator(new ClassMethodsHydrator());' . PHP_EOL;
		$body .= '$this->setAttribute(\'class\', \'form-horizontal\');' . PHP_EOL . PHP_EOL;
				
		// Add the elements 
		foreach($elements as $name => $element) {
			
			if(!$element['type']) {
				continue;
			}
			
			$body .= '// ' . $name . PHP_EOL;
			$body .= '$this->add(array(' . PHP_EOL;
			$body .= sprintf('  \'name\' => \'%s\',', $name) . PHP_EOL;
			$body .= sprintf('  \'type\' => \'%s\',', $element['type']) . PHP_EOL;
			$body .= '	\'attributes\' => array(' . PHP_EOL;
			$body .= sprintf('    \'label\' => \'%s\',', $element['label']) . PHP_EOL;
			$body .= ')));' . PHP_EOL . PHP_EOL;
		}
		
		
			
		$body .= '// submit' . PHP_EOL;
		$body .= '$this->add(array(' . PHP_EOL;
		$body .= sprintf('  \'name\' => \'%s\',', 'submit') . PHP_EOL;
		$body .= sprintf('  \'type\' => \'%s\',', 'Zend\Form\Element\Submit') . PHP_EOL;
		$body .= '	\'attributes\' => array(' . PHP_EOL;
		$body .= sprintf('    \'value\' => \'%s\',', 'Save') . PHP_EOL;
		$body .= sprintf('    \'class\' => \'%s\',', 'btn btn-primary') . PHP_EOL;
		$body .= ')));' . PHP_EOL . PHP_EOL;
		
		
		
		$namespace = 'Application\Form'; 
		$folder = 'module/Application/src/Application/Form';
		$filename = sprintf('%s/%s.php', $folder, $className);
		
		$fileData = array(
			'filename' => $filename,
			'namespace' => $namespace,
			'uses' => array(
				array('Zend\Form\Form'),
				array('Zend\StdLib\Hydrator\ClassMethods', 'ClassMethodsHydrator'),
			),
			'class' => array(
				'name' => $className,
				'extendedclass' => 'Form',
				'methods' => array(
					array(
						'name' => '__construct',
						'parameters' => array(),
						'flags' => null,
						'body' => $body,
					)
				)
			),
		);				
		
		@mkdir($folder, 0755);
		$generator = FileGenerator::fromArray($fileData);
		$generator->write();
		
		return $namespace . '\\' . $className;
	}
	
	/**
	 *
	 * @param \Wiss\Form\Model $form
	 * @return string 
	 */
	public function createController(\Wiss\Form\Model $form)
	{
		$data = $form->getData();
		$className = substr($data['entity_class'], 1 + strrpos($data['entity_class'], '\\'));
		
		$namespace = 'Application\Controller';		
		$folder = 'module/Application/src/Application/Controller';
		$filename = sprintf('%s/%sController.php', $folder, $className);
		
		$slug = \Gedmo\Sluggable\Util\Urlizer::urlize($data['title']);
		
		$fileData = array(
			'filename' => $filename,
			'namespace' => $namespace,
			'uses' => array(
				array('Wiss\Controller\CrudController', 'EntityController'),
			),
			'class' => array(
				'name' => $className . 'Controller',
				'extendedclass' => 'EntityController',
				'properties' => array(
					array('modelName', $slug, \Zend\Code\Generator\PropertyGenerator::FLAG_PROTECTED),
				)
			),
		);		
		
		@mkdir($folder, 0755);
		$generator = FileGenerator::fromArray($fileData);
		$generator->write();
		
		return $namespace . '\\' . $className;
	}
	
	/**
	 *
	 * @param \Wiss\Entity\Model $model 
	 */
	public function createRoutes(\Wiss\Entity\Model $model)
	{
		$em = $this->getEntityManager();
		$repo = $em->getRepository('Wiss\Entity\Page');
		$config = array(		
			$model->getSlug() => array(
				'type' => 'Literal',
				'may_terminate' => true,
				'options' => array(					
					'route'    => '/manage/' . $model->getSlug(),
					'defaults' => array(
						'controller' => $model->getControllerClass(),
						'action' => 'index',
					),
				),
				'child_routes' => array(
					'edit' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/edit/:id',
							'defaults' => array(
								'action' => 'edit',
							),
							'constraints' => array(
								'id' => '[0-9]+',
							),
						)
					)
				)
			)
		);
		
		$total['router']['routes'] = $config;
		$repo->import($total);
		
	}
	
	/**
	 *
	 * @param \Wiss\Entity\Model $model
	 * @return \Wiss\Entity\Model
	 */
	public function createNavigation(\Wiss\Entity\Model $model)
	{			
		$em = $this->getEntityManager();
		$repo = $em->getRepository('Wiss\Entity\Navigation');
		$config = array(
			$model->getSlug() => array(
				'label' => $model->getTitle(),
				'route' => $model->getSlug(),
				'pages' => array(
					'edit' => array(
						'label' => 'Edit',
						'route' => $model->getSlug() . '/edit',
					)
				)
			)
		);
		
		$total['navigation'] = $config;
		$repo->import($total);
		
//		// Insert the list navigation
//		$route = $em->getRepository('Wiss\Entity\Route')->findOneBy(array('name' => 'crud'));
//		$navigation = new \Wiss\Entity\Navigation;
//		$navigation->setParent($this->getContentNavigation());
//		$navigation->setRoute($route);
//		$navigation->setLabel($data['title']);
//		$navigation->setParams(array('name' => $model->getSlug()));			
//		$em->persist($navigation);
//
//		// Insert the edit navigation
//		$route2 = $em->getRepository('Wiss\Entity\Route')->findOneBy(array('name' => 'crud/edit'));
//		$navigation2 = new \Wiss\Entity\Navigation;
//		$navigation2->setParent($navigation);
//		$navigation2->setRoute($route2);
//		$navigation2->setLabel('Properties');
//		$navigation2->setParams(array('name' => $model->getSlug()));			
//		$em->persist($navigation2);
		
	}
	
	/**
	 *
	 * @param string $class
	 * @return array 
	 */
	public function getDataFromAnnotations($class)
	{		
        $parser = new Parser\DoctrineAnnotationParser();
		$parser->registerAnnotation('Wiss\Annotation\Overview');
		
        $annotationManager = new AnnotationManager();
		$annotationManager->attach($parser);
		
        $reflection  = new ClassReflection($class);
        $annotations = $reflection->getAnnotations($annotationManager);
		
		foreach($annotations as $annotation) {
			
			if($annotation instanceof \Wiss\Annotation\Overview) {
				return array(
					'title_field' => $annotation->getTitleField()
				);
			}
		}	
		
		return array();
	}
		
	/**
	 *
	 * @return array 
	 */
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
