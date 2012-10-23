<?php

namespace Wiss\EntityRepository;

use Zend\Code\Generator\FileGenerator;
use Zend\Code\Generator\PropertyGenerator;
use Gedmo\Sluggable\Util\Urlizer;
use Wiss\Form\ModelExport as ExportForm;

use Doctrine\ORM\Tools\EntityGenerator;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * 
 */
class Model extends \Doctrine\ORM\EntityRepository
{		
    /**
     *
     * @param array $data 
     * @return Model
     */
    public function createFromArray(Array $data)
	{
        $em = $this->getEntityManager();
        // Create a new model
        $model = new \Wiss\Entity\Model;
        $model->setTitle($data['title']);
        $model->setEntityClass($data['entity_class']);
		
        // Save this new model
        $em->persist($model);
        $em->flush();

        return $model;
    }
	
    /**
     *
     * @param \Wiss\Entity\Model $model
     */
    public function generateNavigation(\Wiss\Entity\Model $model) 
	{
        $namespace = 'wiss-' . $model->getSlug();
        
        // Build the config, starting from navigation
        $config['navigation'] = array(
            $namespace => array(
                'label' => $model->getTitle(),
                'route' => $namespace,
                'pages' => array(
                    'create' => array(
                        'label' => 'Create',
                        'route' => $namespace . '/create',
                    ),
                    'edit' => array(
                        'label' => 'Edit',
                        'route' => $namespace . '/edit',
                    ),
                )
            )
        );

        // Import the config thru the Navigation entity repository
        $em = $this->getEntityManager();
        $repo = $em->getRepository('Wiss\Entity\Navigation');
        $repo->import($config);
		$repo->export();
		
		// Bind the navigation node to the model
		if(!$model->getNode()) {
			$node = $repo->findOneBy(array('name' => $namespace));
			$model->setNode($node);
			$em->persist($model);
			$em->flush();
		}
    }
	
	
    /**
     *
     * @param \Wiss\Entity\Model $model 
     */
    public function generateRoutes(\Wiss\Entity\Model $model) 
	{
        $namespace = 'wiss-' . $model->getSlug();
        
        // Build the config, starting from router.routes
        $config['router']['routes'] = array(
            $namespace => array(
                'type' => 'Literal',
                'may_terminate' => true,
                'options' => array(
                    'route' => '/' . $namespace,
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
		$repo->export();	
    }
	
	

    /**
     *
     * @param ExportForm $form
     * @return string 
     */
    public function generateController(ExportForm $form) 
	{		
		$model = $form->getModel();
        $className = $form->get('controller_class')->getValue();
        $namespace = substr($className, 0, strrpos($className, '\\'));
		$filename = $form->get('controller_path')->getValue();

        // Create the folder if it does not exist
        if(!file_exists(dirname($filename))) {
            @mkdir(dirname($filename), 0777, true);
        }
        
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
                    array('modelName', $model->getSlug(), PropertyGenerator::FLAG_PROTECTED),
                )
            ),
        );

        $generator = FileGenerator::fromArray($fileData);
        $generator->write();

        return $className;
    }
    
    /**
     * 
     * @param ExportForm $form
     * @return string
     */
    public function generateEntity(ExportForm $form)
    {
        $model = $form->getModel();
        $class = $form->get('entity_class')->getValue();
        $namespace = substr($class, 0, strrpos($class, '\\'));
        $filename = $form->get('entity_path')->getValue();
        
        // Create the folder if it does not exist
        if(!file_exists(dirname($filename))) {
            @mkdir(dirname($filename), 0777, true);
        }
        		

		// Start a new metadata class
		$info = new ClassMetadata($class);	

		// Start a builder to add data to the metadata object
		$builder = new ClassMetadataBuilder($info);
		
		// Add the primary key
		$builder->createField('id', 'integer')->isPrimaryKey()
										   ->generatedValue()
										   ->build();
		
		// Add the model elements
		foreach($model->getElements() as $element) {
			$builder->addField($element->getName(), 'string');
		}

		// Build the entity with the generator
		$generator = new EntityGenerator();
		$generator->setUpdateEntityIfExists(true);	// only update if class already exists
		$generator->setRegenerateEntityIfExists(true);	// this will overwrite the existing classes
		$generator->setGenerateStubMethods(true);
		$generator->setGenerateAnnotations(true);
		$generator->generate(array($info), 'module/Application/src');

		
		// Export to the database       
		$classes[] = $this->getEntityManager()->getClassMetadata($class);
        $tool = new SchemaTool($this->getEntityManager());
		try {
			$tool->createSchema($classes);
		}
		catch(\Exception $e) {
			
		}
		
        // Return the classname to be used later
        return $class;
    }
		
    /**
     * 
     * @param ExportForm $form
     * @return string
     */
    public function generateForm(ExportForm $form) 
	{
        $model = $form->getModel();
        $className = $form->get('form_class')->getValue();
        $namespace = substr($className, 0, strrpos($className, '\\'));
        $filename = $form->get('form_path')->getValue();

        // Create the body for in the __construct method
        $body = sprintf('parent::__construct(\'%s\');', $className) . PHP_EOL . PHP_EOL;
        $body .= '$this->setHydrator(new ClassMethodsHydrator());' . PHP_EOL;
        $body .= '$this->setAttribute(\'class\', \'form-horizontal\');' . PHP_EOL . PHP_EOL;

        // Add the elements 
//        foreach ($model->getElements() as $element) {
        foreach (array() as $element) {

			$name = $element->getName();
			
			// Decode the element config
			$vars = urldecode($element->getConfiguration());
			parse_str($vars, $output);
			
			// Check if the element has a type or config, otherwis
			// there is nothing to do
            if (!$element['type'] || !isset($output['element-config'])) {
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


        // Create the folder if it does not exist
        if(!file_exists(dirname($filename))) {
            @mkdir(dirname($filename), 0777, true);
        }
					
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

        // Generate the file and save it to disk
        $generator = FileGenerator::fromArray($fileData);
        $generator->write();

        // Return the classname to be used later
        return $className;
    }
	
	/**
	 * 
	 * @param string $class
	 * @return string
	 */
	public function buildTitleFromClass($class)
	{
        // Get the title based on the class
        $title = explode('\\', $class);
        $title = end($title);
		return $title;
	}
	
	/**
	 * 
	 * @param string $entityClass
	 * @return Wiss\Entity\Model
	 */
	public function findOneByEntityClass($entityClass)
	{		
        // Find the model with this class
        return $this->findOneBy(array(
            'entityClass' => $entityClass,
		));
	}
	
	/**
	 * 
	 * @param string $slug
	 * @return Wiss\Entity\Model
	 */
	public function findOneBySlug($slug)
	{		
        // Find the model with this class
        return $this->findOneBy(array(
            'slug' => $slug
		));
	}
	
}
