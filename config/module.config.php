<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Wiss;

return array(
	'router' => array(
		'routes' => array(
			'wiss' => array(
				'type' => 'Literal',
				'options' => array(
					'route' => '/wiss',
					'defaults' => array(
						'__NAMESPACE__' => 'Wiss\Controller',
						'controller' => 'index',
						'action' => 'index',
                        'layout' => 'wiss',
					),
				),
				'may_terminate' => true,
				'child_routes' => array(
					'page' => array(
						'type' => 'Literal',
						'options' => array(
							'route' => '/pages',
							'defaults' => array(
								'controller' => 'page',
								'action' => 'index',
							),
						),
						'may_terminate' => true,
						'child_routes' => array(
							'create' => array(
								'type' => 'Literal',
								'options' => array(
									'route' => '/create',
									'defaults' => array(
										'action' => 'create',
									),
								),
							),
							'properties' => array(
								'type' => 'Segment',
								'options' => array(
									'route' => '/properties/[:id]',
									'constraints' => array(
										'id' => '[0-9-]*',
									),
									'defaults' => array(
										'action' => 'properties',
									),
								),
							),
							'route' => array(
								'type' => 'Segment',
								'options' => array(
									'route' => '/route/[:id]',
									'constraints' => array(
										'id' => '[0-9-]*',
									),
									'defaults' => array(
										'action' => 'route',
									),
								),
							),
							'content' => array(
								'type' => 'Segment',
								'options' => array(
									'route' => '/content/[:id]',
									'constraints' => array(
										'id' => '[0-9-]*',
									),
									'defaults' => array(
										'action' => 'content',
									),
								),
							),
						)
					),
					'content' => array(
						'type' => 'Literal',
						'options' => array(
							'route' => '/content',
							'defaults' => array(
								'controller' => 'content',
							),
						),
						'may_terminate' => false,
						'child_routes' => array(
							'configuration' => array(
								'type' => 'Segment',
								'options' => array(
									'route' => '/configuration/[:id]',
									'constraints' => array(
										'id' => '[0-9-]*',
									),
									'defaults' => array(
										'action' => 'configuration',
									),
								),
							),
							'delete' => array(
								'type' => 'Segment',
								'options' => array(
									'route' => '/delete/[:id]',
									'constraints' => array(
										'id' => '[0-9-]*',
									),
									'defaults' => array(
										'action' => 'delete',
									),
								),
							),
						)
					),
					'navigation' => array(
						'type' => 'Literal',
						'options' => array(
							'route' => '/navigation',
							'defaults' => array(
								'controller' => 'navigation',
								'action' => 'index',
							),
						)
					),
					'model' => array(
						'type' => 'Literal',
						'options' => array(
							'route' => '/models',
							'defaults' => array(
								'controller' => 'model',
								'action' => 'index',
							),
						),
						'may_terminate' => true,
						'child_routes' => array(
							'default' => array(
								'type' => 'Segment',
								'options' => array(
									'route' => '/[:action]',
									'constraints' => array(
										'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
									),
								),
							),
							'create' => array(
								'type' => 'Segment',
								'options' => array(
									'route' => '/create/[:module[/:class]]',
									'constraints' => array(
										'module' => '[A-Z][a-zA-Z0-9_-]*',
										'class' => '[A-Z][a-zA-Z0-9_-]*',
									),
									'defaults' => array(
										'action' => 'create',
									),
								),
							),
							'properties' => array(
								'type' => 'Segment',
								'options' => array(
									'route' => '/[:slug]/properties',
									'constraints' => array(
										'slug' => '[a-zA-Z][a-zA-Z0-9_-]*',
									),
									'defaults' => array(
										'action' => 'properties',
									),
								),
							),
							'elements' => array(
								'type' => 'Segment',
								'options' => array(
									'route' => '/[:slug]/elements',
									'constraints' => array(
										'slug' => '[a-zA-Z][a-zA-Z0-9_-]*',
									),
									'defaults' => array(
										'action' => 'elements',
									),
								),
							),
							'generate' => array(
								'type' => 'Segment',
								'options' => array(
									'route' => '/[:slug]/generate',
									'constraints' => array(
										'slug' => '[a-zA-Z][a-zA-Z0-9_-]*',
									),
									'defaults' => array(
										'action' => 'generate',
									),
								),
							),
							'export' => array(
								'type' => 'Segment',
								'options' => array(
									'route' => '/[:slug]/export',
									'constraints' => array(
										'slug' => '[a-zA-Z][a-zA-Z0-9_-]*',
									),
									'defaults' => array(
										'action' => 'export',
									),
								),
							),
							'element-config' => array(
								'type' => 'Segment',
								'options' => array(
									'route' => '/element-config/[:class]',
									'constraints' => array(
										'class' => '[A-Z][a-zA-Z0-9_-]*',
									),
									'defaults' => array(
										'action' => 'element-config'
									),
								),
							),
						)
					),
                    'model-element' => array(
						'type' => 'Literal',
						'options' => array(
							'route' => '/model-element',
							'defaults' => array(
								'controller' => 'modelElement',
								'action' => 'index',
							),
						),
						'may_terminate' => true,
						'child_routes' => array(
                            'create' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/create/[:model]',
                                    'constraints' => array(
                                        'model' => '[1-9][0-9]*',
                                    ),
                                    'defaults' => array(
                                        'action' => 'create',
                                    ),
                                ),
                            ),
                            'properties' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/properties/[:id]',
                                    'constraints' => array(
                                        'id' => '[1-9][0-9]*',
                                    ),
                                    'defaults' => array(
                                        'action' => 'properties',
                                    ),
                                ),
                            ),
                            'config' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/config/[:id]',
                                    'constraints' => array(
                                        'id' => '[0-9]*',
                                    ),
                                    'defaults' => array(
                                        'action' => 'config',
                                    ),
                                ),
                            ),
                        )
                    ),
					'module' => array(
						'type' => 'Literal',
						'options' => array(
							'route' => '/modules',
							'defaults' => array(
								'controller' => 'module',
								'action' => 'index',
							),
						),
						'may_terminate' => true,
						'child_routes' => array(
							'create' => array(
								'type' => 'Literal',
								'options' => array(
									'route' => '/create',
									'defaults' => array(
										'action' => 'create'
									),
								),
							),
							'install' => array(
								'type' => 'Segment',
								'options' => array(
									'route' => '/install/[:name]',
									'constraints' => array(
										'name' => '[a-zA-Z][a-zA-Z0-9_-]*',
									),
									'defaults' => array(
										'action' => 'install'
									),
								),
							),
							'export' => array(
								'type' => 'Literal',
								'options' => array(
									'route' => '/export',
									'defaults' => array(
										'action' => 'export'
									),
								),
							),
						),
					),
					'block' => array(
						'type' => 'Literal',
						'options' => array(
							'route' => '/blocks',
							'defaults' => array(
								'controller' => 'block',
								'action' => 'index',
							),
						),
						'may_terminate' => true,
						'child_routes' => array(
							'properties' => array(
								'type' => 'Segment',
								'options' => array(
									'route' => '/properties/[:id]',
									'constraints' => array(
										'id' => '[0-9]*',
									),
									'defaults' => array(
										'action' => 'properties'
									),
								),
							),
						),
					),
					'install' => array(
						'type' => 'Literal',
						'options' => array(
							'route' => '/install',
							'defaults' => array(
								'controller' => 'index',
								'action' => 'install',
							),
						),
					),
					'install-models' => array(
						'type' => 'Literal',
						'options' => array(
							'route' => '/install-models',
							'defaults' => array(
								'__NAMESPACE__' => 'Wiss\Controller',
								'controller' => 'index',
								'action' => 'install-models',
							),
						),
					),
				)
			),
		)
	),
	'navigation' => array(
		'cms' => array(
			'navigation' => array(
				'label' => 'Navigation',
				'route' => 'wiss/navigation',
				'pages' => array(
					'page' => array(
						'label' => 'Pages',
						'route' => 'wiss/navigation',
						'pages' => array(
							'properties' => array(
								'label' => 'Properties',
								'route' => 'wiss/page/properties'
							),
							'route' => array(
								'label' => 'Route',
								'route' => 'wiss/page/route'
							),
							'content' => array(
								'label' => 'Content',
								'route' => 'wiss/page/content'
							),
						)
					)
				)
			),
			'content' => array(
				'label' => 'Content',
				'route' => 'wiss/model',
			),
			'administration' => array(
				'label' => 'Administration',
				'route' => 'wiss/module',
				'pages' => array(
					'module' => array(
						'label' => 'Modules',
						'route' => 'wiss/module',
					),
					'block' => array(
						'label' => 'Blocks',
						'route' => 'wiss/block',
                        'pages' => array(
                            'properties' => array(
                                'label' => 'Properties',
                                'route' => 'wiss/block/properties',
                            )
                        )
					),
				)
			),
			'install' => array(
				'label' => 'Install',
				'route' => 'wiss/install',
			)
		)
	),
	'service_manager' => array(
		'factories' => array(
			'default' => 'Zend\Navigation\Service\DefaultNavigationFactory',
			'cms' => 'Wiss\Navigation\Service\CmsNavigationFactory'
		),
	),
	'controllers' => array(
		'invokables' => array(
			'Wiss\Controller\Index' => 'Wiss\Controller\IndexController',
			'Wiss\Controller\Module' => 'Wiss\Controller\ModuleController',
			'Wiss\Controller\Page' => 'Wiss\Controller\PageController',
			'Wiss\Controller\Content' => 'Wiss\Controller\ContentController',
			'Wiss\Controller\Model' => 'Wiss\Controller\ModelController',
			'Wiss\Controller\ModelElement' => 'Wiss\Controller\ModelElementController',
			'Wiss\Controller\Navigation' => 'Wiss\Controller\NavigationController',
			'Wiss\Controller\Crud' => 'Wiss\Controller\CrudController',
			'Wiss\Controller\Block' => 'Wiss\Controller\BlockController',
		),
	),
	'view_manager' => array(
		'template_path_stack' => array(
			__DIR__ . '/../view',
		),
	),
	'model-element-builders' => array(
		'Wiss\Model\Element\Text' => 'Text',
		'Wiss\Model\Element\Textarea' => 'Textarea',
		'Wiss\Model\Element\DatePicker' => 'DatePicker',
	),
	'doctrine' => array(
		'driver' => array(
			'wiss_driver' => array(
				'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
				'cache' => 'array',
				'paths' => array('Wiss/Entity')
			),
			'orm_default' => array(
				'drivers' => array(
					'Wiss' => 'wiss_driver'
				)
			)
		),
	),
	'asset_manager' => array(
		'resolver_configs' => array(
			'paths' => array(
				'Wiss' => __DIR__ . '/../assets',
			),
			'collections' => array(
				'js/compiled.js' => array(
//					'js/jquery-1.8.3.js',
					'js/bootstrap.min.js',
					'js/site.js',
				),
				'css/compiled.css' => array(
					'css/bootstrap.min.css',
					'css/style.css',
				)
			)
		)
	),
);
