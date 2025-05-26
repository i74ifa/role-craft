<?php

namespace I74ifa\RoleCraft\Console\Commands;

use I74ifa\RoleCraft\Helpers\ModelHelper;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleCraftCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'role-craft:generate
    {--guard= : guar name to generate}
    {--models=* : models to generate}
    {--role= : role name}
    {--path= : path models}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate, Insert Roles';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $guardName = $this->getGuard();
        $models = $this->getModels();
        $roleName = $this->option('role') ?: config('role-craft.default_role');
        $role = Role::where('name', $roleName)->first() ?:
            Role::create(['name' => $roleName, 'guard_name' => $guardName]);

        if (empty($models)) {
            $this->error('No models found');
            return;
        }

        $permissions = [];
        $separator = config('role-craft.separator');
        $modelDepth = config('role-craft.models_depth') > 1;
        $this->info('Creating permissions for models...');
        foreach ($models as $model) {
            $table = ModelHelper::getTable($model);

            // get depth of the model
            $depth = ModelHelper::getDepth($model, $separator);

            foreach (ModelHelper::getPermissions($model) as $permission) {
                $permissionName = $depth ? sprintf('%s%s%s%s%s', $depth, $separator, $table, $separator, $permission) : sprintf('%s%s%s', $table, $separator, $permission);

                if (!Permission::where('name', $permissionName)->exists()) {
                    $permissions[] = Permission::create(['name' => $permissionName, 'guard_name' => $guardName]);
                    $this->info('created permission ' . $permissionName);
                }
            }
        }

        $role->syncPermissions($permissions);
        $this->comment('created and assign to role. 🫡');
    }

    protected function getModels()
    {
        $models = $this->option('models');

        if ($models) {
            $models = array_map(function ($model) {
                return ModelHelper::getModel($model);
            }, $models);
        } else {
            $models = ModelHelper::getAll(abstract: true, path: $this->option('path') ?? null);
        }

        return array_filter($models);
    }

    protected function getGuard()
    {
        $guard = $this->option('guard');

        if ($guard) {
            return $guard;
        }

        return config('role-craft.guard');
    }
}
