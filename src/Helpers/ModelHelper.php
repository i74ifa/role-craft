<?php

namespace I74ifa\RoleCraft\Helpers;

use ReflectionClass;
use Symfony\Component\Finder\Finder;

class ModelHelper
{
    public static function getAll($abstract = false, $path = null)
    {
        $depth = $path ? config('role-craft.models_depth') : 0;
        $path = $path ?: config('role-craft.models_path', 'app/Models');
        $modelsPath = base_path($path);

        $finder = new Finder();
        $models = [];

        // Get depth from configuration

        $finder->files()->in($modelsPath)->name('*.php');

        if ($depth > 0) {
            $finder->depth($depth - 1);
        }

        foreach ($finder as $file) {
            $model = self::getNamespaceFromPath($file->getPathname());
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
        if (class_exists($model)) {
            $reflection = new \ReflectionClass($model);
            $fileName = $reflection->getFileName();
            $appPath = app_path();

            $relativePath = str_replace([$appPath, '\\'], ['', '/'], $fileName);

            if (preg_match('#/Models/(.+)/[^/]+\.php$#', $relativePath, $matches)) {
                return strtolower(str_replace('/', $separator, $matches[1]));
            }

            return null;
        } else {
            $model = str_replace('\\', '/', $model);
            if (preg_match('#Models/(.+)/[^/]+\.php$#', $model, $matches)) {
                return strtolower(str_replace('/', $separator, $matches[1]));
            }

            return null;
        }
    }

    protected static function getNamespaceFromPath($path)
    {
        $pos = strpos($path, 'App/Models');

        if ($pos === false) {
            return null;
        }
        $relativePath = substr($path, $pos);
        $withoutExtension = pathinfo($relativePath, PATHINFO_DIRNAME) . '/' . pathinfo($relativePath, PATHINFO_FILENAME);
        $namespace = str_replace('/', '\\', $withoutExtension);

        return $namespace;
    }
}
