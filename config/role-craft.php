<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Permissions
    |--------------------------------------------------------------------------
    |
    | This option controls the default permissions that are given to 
    | all models when run the command.
    |
    */
    'default_permissions' => [
        'create',
        'view',
        'update',
        'delete',
        'view_any',
        'create_any',
        'update_any',
        'delete_any'
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Roles
    |--------------------------------------------------------------------------
    |
    | This option controls the default roles that are given to 
    | all models when run the command.
    |
    */
    'default_role' => 'super-admin',

    /*
    |--------------------------------------------------------------------------
    | Default Role Permissions
    |--------------------------------------------------------------------------
    |
    | this option controls the default permissions that are given to
    | all roles when run the command.
    |
    */
    'default_role_permissions' => [
        'create',
        'view',
        'update',
        'delete',
        'view_any',
        'create_any',
        'update_any',
        'delete_any'
    ],

    'separator' => '.',
];