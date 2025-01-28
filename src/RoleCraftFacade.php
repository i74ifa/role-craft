<?php

namespace I74ifa\RoleCraft;

use Illuminate\Support\Facades\Facade;

/**
 * @see \I74ifa\RoleCraft\Skeleton\SkeletonClass
 */
class RoleCraftFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'role-craft';
    }
}
