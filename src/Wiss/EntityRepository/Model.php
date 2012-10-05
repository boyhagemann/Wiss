<?php

namespace Wiss\EntityRepository;

use Zend\Code\Generator\FileGenerator;
use Zend\Code\Generator\PropertyGenerator;
use Gedmo\Sluggable\Util\Urlizer;
use Wiss\Form\Model as ModelForm;
use Wiss\Entity\Model;

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
    public function createFromArray(Array $data) {
        $em = $this->getEntityManager();
        // Create a new model
        $model = new Model;
        $model->setTitle($data['title']);
        $model->setEntityClass($data['entity_class']);
        $model->setTitleField($data['title_field']);
		$model->setFormConfig($data['elements']);
		
        // Save this new model
        $em->persist($model);
        $em->flush();

        return $model;
    }
	
    /**
     *
     * @param Model $model
     */
    public function generateNavigation(Model $model) {
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
     *
     * @param Model $model 
     */
    public function generateRoutes(Model $model) {
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
     * @param ModelForm $form
     * @return string 
     */
    public function generateController(ModelForm $form) {
        $data = $form->getData();
        $className = substr($data['entity_class'], 1 + strrpos($data['entity_class'], '\\'));

        $namespace = 'Application\Controller';
        $folder = 'module/Application/src/Application/Controller';
        $filename = sprintf('%s/%sController.php', $folder, $className);

        $slug = Urlizer::urlize($data['title']);

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
                    array('modelName', $slug, PropertyGenerator::FLAG_PROTECTED),
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
