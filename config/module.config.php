<?php

return array(
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
