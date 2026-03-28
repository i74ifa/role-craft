<?php

namespace I74ifa\RoleCraft\Tests\Feature;

use I74ifa\RoleCraft\Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleCraftSyncCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (!class_exists('App\Models\SyncModel')) {
            eval('namespace App\Models; class SyncModel extends \Illuminate\Database\Eloquent\Model { protected $table = "sync_models"; }');
        }
    }

    public function test_it_fails_if_role_does_not_exist_without_create_option()
    {
        $this->artisan('role-craft:sync', ['name' => 'non-existent'])
            ->expectsOutput('Role not found, if you want create it by me, please use --create option')
            ->assertExitCode(0);
    }

    public function test_it_can_create_a_role_and_sync_all_permissions()
    {
        Permission::create(['name' => 'perm1', 'guard_name' => 'web']);
        Permission::create(['name' => 'perm2', 'guard_name' => 'web']);

        $this->artisan('role-craft:sync', [
            'name' => 'manager',
            '--create' => true,
            '--all' => true,
        ])
        ->expectsOutput('Synced all permissions to manager role 🫡')
        ->assertExitCode(0);

        $role = Role::where('name', 'manager')->first();
        $this->assertNotNull($role);
        $this->assertTrue($role->hasPermissionTo('perm1'));
        $this->assertTrue($role->hasPermissionTo('perm2'));
    }

    public function test_it_can_sync_specific_models_to_a_role()
    {
        Role::create(['name' => 'editor', 'guard_name' => 'web']);
        Permission::create(['name' => 'sync_models.view', 'guard_name' => 'web']);
        
        config()->set('role-craft.default_role_permissions', ['view']);
        config()->set('role-craft.separator', '.');

        $this->artisan('role-craft:sync', [
            'name' => 'editor',
            '--models' => ['SyncModel'],
        ])
        ->expectsOutput('Synced sync_models.view to editor role')
        ->expectsOutput('Synced Finished.')
        ->assertExitCode(0);

        $role = Role::where('name', 'editor')->first();
        $this->assertTrue($role->hasPermissionTo('sync_models.view'));
    }
}
