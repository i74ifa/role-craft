<?php

namespace I74ifa\RoleCraft;

use I74ifa\RoleCraft\Console\Commands\RoleCraftCommand;
use I74ifa\RoleCraft\Console\Commands\RoleCraftSyncCommand;
use Illuminate\Support\ServiceProvider;

class RoleCraftServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/role-craft.php' => config_path('role-craft.php'),
            ], 'role-craft-config');

            $this->commands($this->getCommands());
        }
    }


    protected function getCommands(): array 
    {
        $commands = [
            RoleCraftCommand::class,
            RoleCraftSyncCommand::class,
        ];

        $aliases = [];
        foreach ($commands as $command) {
            if (!class_exists($command)) {
                continue;
            }

            $aliases[] = $command;
        }

        return array_merge($commands, $aliases);
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/role-craft.php', 'role-craft');

        // Register the main class to use with the facade
        $this->app->singleton('role-craft', function () {
            return new RoleCraft;
        });
    }
}
