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
use Zend\Code\Scanner\FileScanner;
use Zend\Form\Annotation\AnnotationBuilder;
use Zend\Code\Annotation\Parser;
use Zend\Code\Annotation\AnnotationManager;
use Zend\Code\Reflection\ClassReflection;
use Doctrine\ORM\EntityManager;

class ModelController extends AbstractActionController {

    /**
     *
     * @param EntityManager $entityManager
     */
    protected $entityManager;

    /**
     * Shows a list of all models that are currently installed
     *
     */
    public function indexAction() {
        $models = $this->getInstalledModels();
        return compact('models');
    }

    /**
     * Edit the models properties
     *
     */
    public function editAction() {
        // Get the model
        $em = $this->getEntityManager();
        $repo = $em->getRepository('Wiss\Entity\Model');
        $model = $repo->find($this->params('id'));

        // Get the form
        $form = new \Wiss\Form\Model;
        $form->bind($model);

        if ($this->getRequest()->isPost()) {

            $form->setData($this->getRequest()->getPost());

            if ($form->isValid()) {

                // Save the changes
                $em->persist($model);
                $em->flush();

                // Show a flash message
                $this->flashMessenger()->addMessage('The model is now updated');

                // Redirect
                $this->redirect()->toRoute('wiss/model/edit', array(
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
		$em = $this->getEntityManager();
		$repo = $em->getRepository('Wiss\Entity\Model');
				
		$class = $this->buildClassNameFromUrlParam();
		$title = $repo->buildTitleFromClass($class);
		$model = $repo->findOneByEntityClass($class);

        // Return if a model already exists
        if ($model) {

            // Show a flash message
            $this->flashMessenger()->addMessage('The model is already installed');

            // Redirect
            $this->redirect()->toRoute('wiss/content/' . $model->getSlug());

            return false;
        }

        // Get data from entity annotations
        $data = $this->getDataFromAnnotations($class);
        $data += array(
            'title' => $title,
            'entity_class' => $class,
            'element-config-url' => $this->url()->fromRoute('wiss/model/element-config'),
        );

        // Create the form
        $form = $this->getServiceLocator()->get('Wiss\Form\Model');   
		$form->setName('model');
		$form->prepareElements($data);
		$form->setData($data);

        if ($this->getRequest()->isPost()) {
			
            $form->setData($this->getRequest()->getPost());

            if ($form->isValid()) {
				
				// Merge the form values with the start data
				$data = $form->getData() + $data;
				
                // Create the model
                $model = $repo->createFromArray($data);

                // Show a flash message
                $this->flashMessenger()->addMessage('The model is now created');

                // Redirect
                $this->redirect()->toRoute('wiss/model/export', array(
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
		$em = $this->getEntityManager();
		$repo = $em->getRepository('Wiss\Entity\Model');		
		$model = $repo->findOneBySlug($this->params('name'));
		
        $form = $this->getServiceLocator()->get('Wiss\Form\ModelExport');  
		$form->prepareElements();
		
        if ($this->getRequest()->isPost()) {
			
            $form->setData($this->getRequest()->getPost());
		
            if ($form->isValid()) {	
				
				$data = $form->getData();
				
				if($data['form']) {
                    $formClass = $repo->generateForm($model);
					$model->setFormClass($formClass);
				}
				
				if($data['controller']) {
					$controllerClass = $repo->generateController($model);
					$model->setControllerClass($controllerClass);
				}
				
				if($data['model']) {
					//...//
				}
				
				// Build the config
				if($data['config']) {					
					$em->getRepository('Wiss\Entity\Navigation')->export();
					$em->getRepository('Wiss\Entity\Route')->export();					
				}
				
				// Save the model changes
				$this->getEntityManager()->persist($model);
				$this->getEntityManager()->flush();
		
				// Redirect
				$this->redirect()->toRoute('wiss/model');		
			}
			
		}

        return compact('form', 'model');
    }
	
	/**
	 * 
	 * @return string
	 */
	public function buildClassNameFromUrlParam()
	{		
        // Get the class from the url params
        $class = $this->params('class');
        $class = str_replace('-', '\\', $class);
		return $class;
	}
    
    /**
     * 
     * @return \Zend\View\Model\ViewModel
     */
    public function elementConfigAction()
    {
        $formClass = $this->getRequest()->getQuery('form-class');
        $form = $this->getServiceLocator()->get($formClass);
        
        return new ViewModel(array(
            'form' => $form,
        ));
    }


    /**
     * Read some useful information from the annotations regarding
     * list overviews
     *
     * @param string $class
     * @return array 
     */
    public function getDataFromAnnotations($class) {
        $parser = new Parser\DoctrineAnnotationParser();
        $parser->registerAnnotation('Wiss\Annotation\Overview');

        $annotationManager = new AnnotationManager();
        $annotationManager->attach($parser);

        $reflection = new ClassReflection($class);
        $annotations = $reflection->getAnnotations($annotationManager);

        foreach ($annotations as $annotation) {

            if ($annotation instanceof \Wiss\Annotation\Overview) {
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
    public function uninstalledAction() {
        // Get the scanned and installed models
        $scanned = $this->getScannedEntities();
        $models = $this->getInstalledModels();

        // Unset each model that already is installed
        foreach ($models as $model) {
            unset($scanned[$model->getEntityClass()]);
        }

        return compact('scanned');
    }

    /**
     *
     * @return Doctrine\ORM\Collection
     */
    public function getInstalledModels() {
        return $this->getEntityManager()->getRepository('Wiss\Entity\Model')->findAll();
    }

    /**
     *
     * @return array
     */
    public function getScannedEntities() {
        $em = $this->getEntityManager();
        $config = $this->getServiceLocator()->get('applicationconfig');
        $paths = $config['module_listener_options']['module_paths'];
        $drivers = $em->getConfiguration()->getMetadataDriverImpl()->getDrivers();
        $entities = array();

        // Walk thru all found paths to search for models
        foreach ($paths as $pathName => $basepath) {

            // Do not show all the Wiss models in the list. It can be confusing.
            // Only show the models found in the website application.
            if ($pathName === 'Wiss') {
                continue;
            }

            // Each path can have folders that are equal to the ones given
            // in the Doctrine drivers. See if there are folders within
            // the current path that match with a driver folder.
            foreach ($drivers as $namespace => $driver) {

                foreach ($driver->getPaths() as $path) {

                    // Build the path to the file
                    $filePattern = '%s/%s/src/%s';
                    $file = sprintf($filePattern, $basepath, $namespace, $path);
                    $entities += $this->getEntitiesByPath($file);
                }
            }
        }

        return $entities;
    }

    /**
     * 
     * @param string $path
     * @return array
     */
    public function getEntitiesByPath($path) {
        $entities = array();

        // Check if the file exists
        if (!file_exists($path)) {
            return $entities;
        }

        // Walk each file in the directory to see if there is
        // a valid entity
        $directory = new \DirectoryIterator($path);
        foreach ($directory as $file) {

            // Only use real files
            if ($file->isDot() || $file->isDir()) {
                continue;
            }

            // Start a file scanner, to check for classes inside the file
            $scanner = new FileScanner($file->getPathname());

            // Check the file for classes                            
            foreach ($scanner->getClassNames() as $class) {

                try {

                    // See if we can build an entity without throwing an exception.
                    // If no exception is thrown, then we have a valid entity
                    $entity = $this->getEntityManager()->getRepository($class);
                    $entities[$class] = $entity;
                } catch (\Exception $e) {
                    // Just skip to the next
                }
            }
        }

        return $entities;
    }

    /**
     *
     * @param EntityManager $entityManager 
     */
    public function setEntityManager(EntityManager $entityManager) {
        $this->entityManager = $entityManager;
    }

    /**
     *
     * @return EntityManager 
     */
    public function getEntityManager() {
        return $this->entityManager;
    }

}
