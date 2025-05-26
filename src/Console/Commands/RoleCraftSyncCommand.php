<?php

namespace I74ifa\RoleCraft\Console\Commands;

use I74ifa\RoleCraft\Helpers\ModelHelper;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleCraftSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'role-craft:sync
            {name}
            {--create}
            {--models=* : models to sync}
            {--all : sync all permissions}
            {--guard= : guard name}
            {--path= : path models}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync permissions to role';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $roleName = $this->argument('name');
        $create = $this->option('create');
        $models = $this->getModels();
        $isAll = $this->option('all');
        $guardName = $this->option('guard') ?? config('auth.defaults.guard');

        $role = Role::where('name', $roleName)->where('guard_name', $guardName)->first() ?? ($create ? Role::create(['name' => $roleName, 'guard_name' => $guardName]) : null);

        if (!$role) {
            $this->error('Role not found, if you want create it by me, please use --create option');
            return;
        }

        if (empty($models) && !$isAll) {
            $this->error('No models found');
            return;
        }

        if ($isAll) {
            $role->syncPermissions(Permission::all());
            $this->comment('Synced all permissions to ' . $roleName . ' role 🫡');
            return;
        }
        foreach ($models as $model) {
            $model = ModelHelper::getModel($model) ?? $model;
            $table = ModelHelper::getTable($model);

            foreach (ModelHelper::getPermissions($model) as $permission) {
                $permissionName = sprintf('%s%s%s', $table, config('role-craft.separator'), $permission);
                $role->givePermissionTo($permissionName);
                $this->info('Synced ' . $permissionName . ' to ' . $roleName . ' role');
            }
        }

        $this->comment('Synced Finished.');
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
}
