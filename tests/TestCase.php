<?php

namespace I74ifa\RoleCraft\Tests;

use I74ifa\RoleCraft\RoleCraftServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\Permission\PermissionServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase($this->app);
    }

    protected function getPackageProviders($app)
    {
        return [
            RoleCraftServiceProvider::class,
            PermissionServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        config()->set('permission.column_names.model_morph_key', 'model_id');

        // Package specific config
        config()->set('role-craft.default_role', 'admin');
        config()->set('role-craft.separator', '.');
        config()->set('role-craft.guard', 'web');
    }

    protected function setUpDatabase($app)
    {
        $app['db']->connection()->getSchemaBuilder()->create('users', function ($table) {
            $table->increments('id');
            $table->string('email');
            $table->softDeletes();
        });

        // Run migrations from spatie/laravel-permission
        $migration = include __DIR__ . '/../vendor/spatie/laravel-permission/database/migrations/create_permission_tables.php.stub';
        $migration->up();
    }
}
