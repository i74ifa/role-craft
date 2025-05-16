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

        $role = Role::where('name', config('role-craft.default_role'))->first() ?: Role::create(['name' => config('role-craft.default_role'), 'guard_name' => config('role-craft.guard')]);
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
                    $permissions[] = Permission::create(['name' => $permissionName, 'guard_name' => config('role-craft.guard')]);
                    $this->info('created permission ' . $permissionName);
                }
            }
        }

        $role->syncPermissions($permissions);
        $this->comment('created and assign to role. 🫡');
    }
}
