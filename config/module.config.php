<?php
return array(
    'router' => array(
        'routes' => array(
            'referencement-errors' => array(
                'type' => 'Segment',
                'options' => array(
                    'route'    => '/errors[/:type]',
                	'constraints' => array(
                		'type'     => '[a-zA-Z0-9_-]*',
                	),
                    'defaults' => array(
                        'controller' => 'ReferencementErrors\Controller\Index',
                        'action'     => 'index',
                    ),
                )
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'ReferencementErrors\Controller\Index' => 'ReferencementErrors\Controller\IndexController'
        ),
    ),
	'zfc_rbac' => [
		'guards' => [
            'ZfcRbac\Guard\RouteGuard' => [
                'referencement-errors' => ['*']
            ]
        ]
	],
	'referencement_errors'	=> [
     	'key'	=>	'ma cle'       	
     ]
);