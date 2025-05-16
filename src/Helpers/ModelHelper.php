<?php

namespace I74ifa\RoleCraft\Helpers;

use Symfony\Component\Finder\Finder;

class ModelHelper
{
    public static function getAll($abstract = false)
    {
        $modelsPath = app_path('Models'); // Base models directory
        $namespace = app()->getNamespace();

        $finder = new Finder();
        $models = [];

        // Get depth from configuration
        $depth = config('role-craft.models_depth');

        $finder->files()->in($modelsPath)->name('*.php');

        if ($depth > 0) {
            $finder->depth($depth - 1);
        }

        foreach ($finder as $file) {
            // Get the relative path and convert it to a namespace
            $relativePath = str_replace(['/', '.php'], ['\\', ''], $file->getRelativePathname());
            $model = $namespace . 'Models\\' . $relativePath;

            // Check if the class exists and is a subclass of Eloquent Model
            if (class_exists($model) && is_subclass_of($model, 'Illuminate\Database\Eloquent\Model')) {
                $models[] = $model;
            }
        }

        return $abstract ? $models : $models;
    }
    public static function getModel($model)
    {
        $model = sprintf('App\Models\\%s', $model);

        return class_exists($model) ? $model : null;
    }
    public static function getTable($model)
    {
        return app($model)->getTable();
    }

    public static function getPrefixPermission($model)
    {
        return isset(app($model)->prefix_permissions) ? app($model)->prefix_permissions : static::getTable($model);
    }

    public static function getPermissions($model)
    {
        return app($model)->prefix_model_permissions ?? config('role-craft.default_role_permissions');
    }

    public static function getDepth($model, $separator)
    {
        // If it's a class name (App\Models\ERP\Central\User)
        if (class_exists($model)) {
            $reflection = new \ReflectionClass($model);
            $fileName = $reflection->getFileName();
            $appPath = app_path();

            // Get the relative path from app directory
            $relativePath = str_replace([$appPath, '\\'], ['', '/'], $fileName);

            // Extract the subdirectory between Models and the filename
            if (preg_match('#/Models/(.+)/[^/]+\.php$#', $relativePath, $matches)) {
                // Convert directory separators to the configured separator
                return strtolower(str_replace('/', $separator, $matches[1]));
            }

            return null; // In root Models directory
        }
        // If it's a path (App/Models/ERP/Central/User.php)
        else {
            // Normalize path separators
            $model = str_replace('\\', '/', $model);

            // Extract the subdirectory between Models and the filename
            if (preg_match('#Models/(.+)/[^/]+\.php$#', $model, $matches)) {
                // Convert directory separators to the configured separator
                return strtolower(str_replace('/', $separator, $matches[1]));
            }

            return null; // In root Models directory
        }
    }
}
