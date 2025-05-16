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

    /*
    |--------------------------------------------------------------------------
    | Default Role Model
    |--------------------------------------------------------------------------
    |
    | This option to set the default guard in base Models/ Path
    |
    */
    'guard' => 'web',

    /*
    |--------------------------------------------------------------------------
    | Depth of the Models
    |--------------------------------------------------------------------------
    |
    | This option to set what depth of the models you want to create
    | set 0 to get subdirectories models
    |
    */
    'models_depth' => 0,

    /*
    |--------------------------------------------------------------------------
    | Use Sub-directory name to permission name
    |--------------------------------------------------------------------------
    |
    | This option to set if you want to use sub-directory name to permission name
    |
    */
    'subdirectory_permission_name' => false,

    /*
    |--------------------------------------------------------------------------
    | Models Path
    |--------------------------------------------------------------------------
    |
    | This option to set the models path from base path
    |
    */
    'models_path' => 'app/Models',
];
