<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'router' => array(
        'routes' => array(
            'page-wildcard' => array(
                'type'    => 'Wildcard',
                'options' => array(
                    'route'    => '*',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Wiss\Controller',
                        'controller'    => 'page-content',
                        'action'        => 'route',
                    ),
                ),                
            ),
			'page' => array(
				'type'    => 'Literal',
				'options' => array(
					'route'    => '/pages',
					'defaults' => array(
                        '__NAMESPACE__' => 'Wiss\Controller',
                        'controller' => 'page',
                        'action' => 'index',
					),
				),
				'may_terminate' => true,
				'child_routes' => array(	
					'default' => array(
						'type'    => 'Segment',
						'options' => array(
							'route'    => '/[:action]',
							'constraints' => array(
								'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
							),
							'defaults' => array(
								'action' => 'index',
							),
						),
					),				
					'view' => array(
						'type'    => 'Segment',
						'options' => array(
							'route'    => '/view/[:id]',
							'constraints' => array(
								'id' => '[0-9-]*',
							),
							'defaults' => array(
								'action' => 'view',
							),
						),
					),
				)
			),
            'module' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/modules',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Wiss\Controller',
                        'controller'    => 'module',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/[:controller[/:action]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                            ),
                        ),
                    ),
                ),
            ),
            'install' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/install',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Wiss\Controller',
                        'controller'    => 'index',
                        'action'        => 'install',
                    ),
                ),
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Wiss\Controller\Index'			=> 'Wiss\Controller\IndexController',
            'Wiss\Controller\Module'		=> 'Wiss\Controller\ModuleController',
            'Wiss\Controller\Page'			=> 'Wiss\Controller\PageController',
            'Wiss\Controller\PageContent'	=> 'Wiss\Controller\PageContentController',
        ),
    ),
    'view_manager' => array(
        'template_map' => array(
            'page-content/zone' => __DIR__ . '/../view/wiss/page-content/zone.phtml',
		),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
	'doctrine' => array(
        'driver' => array(
            'orm_default' => array(
                'drivers' => array(
                    'Wiss' => 'wiss_driver'
				)
            ),
			'wiss_driver' => array(
                'paths' => array(__NAMESPACE__ . '/Entity'),
            ),
        ),
    )
);
