<?php

return array(
    'asset_manager' => array(
        'resolver_configs' => array(
            'paths' => array(
                __DIR__ . '/../public',
            )
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'ZfJoacubCrud\Controller\DataGrid'  => 'ZfJoacubCrud\Controller\DataGridController'
        ),
    ),

    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
);
