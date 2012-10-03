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
use Doctrine\ORM\EntityManager;
use Wiss\Entity\Model;
use Wiss\Form\Model as ModelForm;

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
    public function installAction() {
        // Get the class from the url params
        $class = $this->params('class');
        $class = str_replace('-', '\\', $class);

        // Get the title based on the class
        $title = explode('\\', $class);
        $title = end($title);

        // Find the model with this class
        $em = $this->getEntityManager();
        $model = $em->getRepository('Wiss\Entity\Model')->findOneBy(array(
            'entityClass' => $class
                ));

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
			$form->isValid();

            if ($form->isValid()) {
		
                // Add the model form and controller class
                $data = $form->getData();
				$data += array(
                    'form_class' => $this->generateForm($form),
                    'controller_class' => $this->generateController($form)
                );

                // Create the model
                $model = $this->createModel($data);

                // Create the routes and navigation
                $this->createRoutes($model);
                $this->createNavigation($model, $data);

                // Show a flash message
                $this->flashMessenger()->addMessage('The model is now installed');

                // Redirect
                $this->redirect()->toRoute('wiss/model/export', array(
                    'name' => $model->getSlug()
                ));
            }
			else {
				

		\Zend\Debug\Debug::dump($form->getMessages(), 'Errors'); exit;
			}
        }

        return compact('title', 'form');
    }

    /**
     *
     * @return boolean 
     */
    public function exportAction() {
        // Build the config
        $em = $this->getEntityManager();
        $em->getRepository('Wiss\Entity\Navigation')->export();
        $em->getRepository('Wiss\Entity\Route')->export();

        // Redirect
        $this->redirect()->toRoute('wiss/model');

        return false;
    }
    
    /**
     * 
     * @return \Zend\View\Model\ViewModel
     */
    public function elementConfigAction()
    {
        $formClass = $this->getRequest()->getQuery('form-class');

        $form = $this->getServiceLocator()->get($formClass);
        
        $viewModel = new ViewModel(array(
            'form' => $form,
        ));
                
        return $viewModel;
    }

    /**
     *
     * @param array $data 
     * @return Model
     */
    public function createModel(Array $data) {
        $em = $this->getEntityManager();
        // Create a new model
        $model = new Model;
        $model->setTitle($data['title']);
        $model->setEntityClass($data['entity_class']);
        $model->setTitleField($data['title_field']);
        $model->setFormClass($data['form_class']);
        $model->setControllerClass($data['controller_class']);
		$model->setFormConfig($data['elements']);
		
        // Save this new model
        $em->persist($model);
        $em->flush();

        return $model;
    }

    /**
     * 
     * @param ModelForm $form
     * @return string
     */
    public function generateForm(ModelForm $form) {
        $data = $form->getData();
        $elementData = $data['elements'];
        $className = substr($data['entity_class'], 1 + strrpos($data['entity_class'], '\\'));

        // Create the body for in the __construct method
        $body = sprintf('parent::__construct(\'%s\');', $className) . PHP_EOL . PHP_EOL;
        $body .= '$this->setHydrator(new ClassMethodsHydrator());' . PHP_EOL;
        $body .= '$this->setAttribute(\'class\', \'form-horizontal\');' . PHP_EOL . PHP_EOL;

        // Add the elements 
        foreach ($elementData as $name => $element) {

			$vars = urldecode($element['configuration']);
			parse_str($vars, $output);
			
            if (!$element['type'] || !isset($element['element-config'])) {
                continue;
            }
			
			$config = $output['element-config'];

            // Create the element method
            $body .= '// ' . $name . PHP_EOL;
            $body .= '$this->add(array(' . PHP_EOL;
            $body .= sprintf('  \'name\' => \'%s\',', $name) . PHP_EOL;
            $body .= sprintf('  \'type\' => \'%s\',', $element['type']) . PHP_EOL;
            $body .= '	\'attributes\' => array(' . PHP_EOL;
            $body .= sprintf('    \'label\' => \'%s\',', $config['label']) . PHP_EOL;
            $body .= ')));' . PHP_EOL . PHP_EOL;
        }

        // Create the submit method
        $body .= '// submit' . PHP_EOL;
        $body .= '$this->add(array(' . PHP_EOL;
        $body .= sprintf('  \'name\' => \'%s\',', 'submit') . PHP_EOL;
        $body .= sprintf('  \'type\' => \'%s\',', 'Zend\Form\Element\Submit') . PHP_EOL;
        $body .= '	\'attributes\' => array(' . PHP_EOL;
        $body .= sprintf('    \'value\' => \'%s\',', 'Save') . PHP_EOL;
        $body .= sprintf('    \'class\' => \'%s\',', 'btn btn-primary') . PHP_EOL;
        $body .= ')));' . PHP_EOL . PHP_EOL;

        // Set the names for file generation
        $namespace = 'Application\Form';
        $folder = 'module/Application/src/Application/Form';
        $filename = sprintf('%s/%s.php', $folder, $className);

        // Build the file holding the php class
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

        // Create the folder if it does not exist yet
        @mkdir($folder, 0755);

        // Generate the file and save it to disk
        $generator = FileGenerator::fromArray($fileData);
        $generator->write();

        // Return the classname to be used later
        return $namespace . '\\' . $className;
    }

    /**
     *
     * @param ModelForm $form
     * @return string 
     */
    public function generateController(ModelForm $form) {
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
     * @param Model $model 
     */
    public function createRoutes(Model $model) {
        // Build the config, starting from router.routes
        $config['router']['routes'] = array(
            $model->getSlug() => array(
                'type' => 'Literal',
                'may_terminate' => true,
                'options' => array(
                    'route' => '/' . $model->getSlug(),
                    'defaults' => array(
                        'controller' => $model->getControllerClass(),
                        'action' => 'index',
                    ),
                ),
                'child_routes' => array(
                    'create' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/create',
                            'defaults' => array(
                                'action' => 'create',
                            ),
                        )
                    ),
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
                    ),
                    'delete' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/delete/:id',
                            'defaults' => array(
                                'action' => 'delete',
                            ),
                            'constraints' => array(
                                'id' => '[0-9]+',
                            ),
                        )
                    )
                )
            )
        );

        // Import the config thru the Page entity repository
        $em = $this->getEntityManager();
        $repo = $em->getRepository('Wiss\Entity\Route');
        $repo->import($config);
    }

    /**
     *
     * @param Model $model
     */
    public function createNavigation(Model $model) {
        // Build the config, starting from navigation
        $config['navigation'] = array(
            $model->getSlug() => array(
                'label' => $model->getTitle(),
                'route' => $model->getSlug(),
                'pages' => array(
                    'create' => array(
                        'label' => 'Create',
                        'route' => $model->getSlug() . '/create',
                    ),
                    'edit' => array(
                        'label' => 'Edit',
                        'route' => $model->getSlug() . '/edit',
                    ),
                )
            )
        );

        // Import the config thru the Navigation entity repository
        $em = $this->getEntityManager();
        $repo = $em->getRepository('Wiss\Entity\Navigation');
        $repo->import($config);
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
            $scanner = new \Zend\Code\Scanner\FileScanner($file->getPathname());

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
