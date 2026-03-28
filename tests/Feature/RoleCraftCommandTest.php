<?php

namespace I74ifa\RoleCraft\Tests\Feature;

use I74ifa\RoleCraft\Tests\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleCraftCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Define a dummy model in App\Models namespace
        if (!class_exists('App\Models\TestModel')) {
            eval('namespace App\Models; class TestModel extends \Illuminate\Database\Eloquent\Model { protected $table = "test_models"; }');
        }
    }

    public function test_it_can_generate_permissions_for_a_specific_model()
    {
        config()->set('role-craft.default_role_permissions', ['view', 'create']);
        config()->set('role-craft.separator', '.');
        config()->set('role-craft.default_role', 'admin');

        $this->artisan('role-craft:generate', [
            '--models' => ['TestModel'],
        ])
            ->expectsOutput('Creating permissions for models...')
            ->expectsOutput('created permission test_models.view')
            ->expectsOutput('created permission test_models.create')
            ->expectsOutput('created and assign to role. 🫡')
            ->assertExitCode(0);

        $this->assertTrue(Permission::where('name', 'test_models.view')->exists());
        $this->assertTrue(Permission::where('name', 'test_models.create')->exists());

        $role = Role::where('name', 'admin')->first();
        $this->assertNotNull($role);
        $this->assertTrue($role->hasPermissionTo('test_models.view'));
    }

    public function test_it_fails_if_no_models_provided_and_none_found()
    {
        $this->artisan('role-craft:generate', [
            '--models' => [],
            '--path' => 'database'
        ])
            ->expectsOutput('No models found')
            ->assertExitCode(0);
    }
}
