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
    protected $signature = 'role-craft:generate';

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
        $models = ModelHelper::getAll(true);

        if (empty($models)) {
            $this->error('No models found');
            return;
        }

        $role = Role::where('name', config('role-craft.default_role'))->first() ?: Role::create(['name' => config('role-craft.default_role')]);
        $permissions = [];
        foreach ($models as $model) {
            $table = ModelHelper::getTable($model);

            foreach (ModelHelper::getPermissions($model) as $permission) {
                $permissionName = sprintf('%s%s%s', $table, config('role-craft.separator'), $permission);
                
                if (!Permission::where('name', $permissionName)->exists()) {
                    $permissions[] = Permission::create(['name' => $permissionName]);
                    $this->info('created permission ' . $permissionName);
                }
            }
        }

        $role->syncPermissions($permissions);
        $this->comment('created and assign to role. 🫡');
    }
}