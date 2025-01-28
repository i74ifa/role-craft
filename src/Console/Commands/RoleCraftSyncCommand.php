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
    protected $signature = 'role-craft:sync {name} {--create} {--models=*} {--all}';
    // {name} : role name
    // {--force?} : force sync
    // {--recreate?} : recreate role and permissions

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
        $models = $this->option('models');
        $isAll = $this->option('all');

        $role = Role::where('name', $roleName)->first() ?? ($create ? Role::create(['name' => $roleName]) : null);

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
}