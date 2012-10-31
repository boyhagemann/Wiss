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
			'page-wildcard' => array(
				'type' => 'Wildcard',
				'options' => array(
					'route' => '*',
					'defaults' => array(
						'__NAMESPACE__' => 'Wiss\Controller',
						'controller' => 'pageContent',
						'action' => 'route',
					),
				),
			),
			'wiss' => array(
				'type' => 'Literal',
				'options' => array(
					'route' => '/wiss',
					'defaults' => array(
						'__NAMESPACE__' => 'Wiss\Controller',
						'controller' => 'index',
						'action' => 'index',
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
									'route' => '/create/[:class]',
									'constraints' => array(
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
								'may_terminate' => true,
								'child_routes' => array(
									'create' => array(
										'type' => 'Segment',
										'options' => array(
											'route' => '/create/[:class]',
											'constraints' => array(
												'class' => '[A-Z][a-zA-Z0-9_-]*',
											),
											'defaults' => array(
												'action' => 'create-element',
											),
										),
									),
									'configure' => array(
										'type' => 'Segment',
										'options' => array(
											'route' => '/configure/[:id]',
											'constraints' => array(
												'element-id' => '[0-9]*',
											),
											'defaults' => array(
												'action' => 'configure-element',
											),
										),
									),
								)
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
							'default' => array(
								'type' => 'Segment',
								'options' => array(
									'route' => '/[:action]',
									'constraints' => array(
										'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
									),
									'defaults' => array(
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
                    'structure' => array(
                        'label' => 'Structure',
                        'route' => 'wiss/navigation',
                    ),
					'page' => array(
						'label' => 'Pages',
						'route' => 'wiss/page',
						'pages' => array(
							'properties' => array(
								'label' => 'Properties',
								'route' => 'wiss/page/properties'
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
						'pages' => array(
							'installed' => array(
								'label' => 'Installed',
								'route' => 'wiss/module',
							),
							'uninstalled' => array(
								'label' => 'Uninstalled',
								'route' => 'wiss/module/default',
								'params' => array(
									'action' => 'uninstalled'
								)
							),
						)
					),
					'models' => array(
						'label' => 'Models',
						'route' => 'wiss/model',
						'pages' => array(
							'installed' => array(
								'label' => 'Installed',
								'route' => 'wiss/model',
							),
							'uninstalled' => array(
								'label' => 'Uninstalled',
								'route' => 'wiss/model/default',
								'params' => array(
									'action' => 'uninstalled'
								),
								'pages' => array(
									'create' => array(
										'label' => 'Create',
										'route' => 'wiss/model/create'
									),
								)
							),
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
			'Wiss\Controller\PageContent' => 'Wiss\Controller\PageContentController',
			'Wiss\Controller\Model' => 'Wiss\Controller\ModelController',
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
	'model-elements' => array(
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
					'js/jquery-1.7.2.min.js',
					'js/bootstrap.min.js',
					'js/jstree/jquery.jstree.js',
					'js/site.js',
				),
				'css/compiled.css' => array(
					'css/bootstrap.min.css',
					'js/jstree/themes/default/style.css',
					'css/style.css',
				)
			)
		)
	),
);
