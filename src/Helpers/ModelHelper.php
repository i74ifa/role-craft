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
        $finder->files()->in($modelsPath)->name('*.php');

        foreach ($finder as $file) {
            // Get the relative path and convert it to a namespace
            $relativePath = str_replace(['/', '.php'], ['\\', ''], $file->getRelativePathname());
            $model = $namespace . 'Models\\' . $relativePath;

            // Check if the class exists and is a subclass of Eloquent Model
            if (class_exists($model) && is_subclass_of($model, 'Illuminate\Database\Eloquent\Model')) {
				$models[] = $model;
            }
        }

        return $abstract ? $models : $models; // TODO:: remove model path
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
}