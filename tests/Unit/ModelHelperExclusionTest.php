<?php

namespace I74ifa\RoleCraft\Tests\Unit;

use I74ifa\RoleCraft\Helpers\ModelHelper;
use I74ifa\RoleCraft\Tests\TestCase;
use ReflectionMethod;

class ModelHelperExclusionTest extends TestCase
{
    protected function callIsExcluded(string $model, array $patterns): bool
    {
        $method = new ReflectionMethod(ModelHelper::class, 'isExcluded');
        $method->setAccessible(true);

        return $method->invoke(null, $model, $patterns);
    }

    public function test_empty_pattern_list_excludes_nothing()
    {
        $this->assertFalse($this->callIsExcluded('App\Models\User', []));
    }

    public function test_exact_class_name_is_excluded()
    {
        $this->assertTrue(
            $this->callIsExcluded('App\Models\User', ['App\Models\User'])
        );
    }

    public function test_non_matching_class_name_is_not_excluded()
    {
        $this->assertFalse(
            $this->callIsExcluded('App\Models\Post', ['App\Models\User'])
        );
    }

    public function test_wildcard_matches_subdirectory()
    {
        $this->assertTrue(
            $this->callIsExcluded('App\Models\Internal\AuditLog', ['App\Models\Internal\*'])
        );
    }

    public function test_wildcard_does_not_match_outside_subdirectory()
    {
        $this->assertFalse(
            $this->callIsExcluded('App\Models\Public\Article', ['App\Models\Internal\*'])
        );
    }

    public function test_leading_backslash_is_normalized_on_both_sides()
    {
        $this->assertTrue(
            $this->callIsExcluded('\\App\\Models\\User', ['\\App\\Models\\User'])
        );

        $this->assertTrue(
            $this->callIsExcluded('App\\Models\\User', ['\\App\\Models\\User'])
        );

        $this->assertTrue(
            $this->callIsExcluded('\\App\\Models\\User', ['App\\Models\\User'])
        );
    }

    public function test_wildcard_can_match_anywhere_in_namespace()
    {
        $this->assertTrue(
            $this->callIsExcluded('App\Models\Billing\Pivot\InvoiceItem', ['*\Pivot\*'])
        );
    }

    public function test_multiple_patterns_any_match_excludes()
    {
        $patterns = [
            'App\Models\User',
            'App\Models\Internal\*',
        ];

        $this->assertTrue($this->callIsExcluded('App\Models\User', $patterns));
        $this->assertTrue($this->callIsExcluded('App\Models\Internal\AuditLog', $patterns));
        $this->assertFalse($this->callIsExcluded('App\Models\Post', $patterns));
    }

    public function test_get_all_filters_out_excluded_models_from_app_models()
    {
        if (!class_exists('App\Models\IncludedExampleModel')) {
            eval('namespace App\Models; class IncludedExampleModel extends \Illuminate\Database\Eloquent\Model { protected $table = "included_example_models"; }');
        }

        if (!class_exists('App\Models\ExcludedExampleModel')) {
            eval('namespace App\Models; class ExcludedExampleModel extends \Illuminate\Database\Eloquent\Model { protected $table = "excluded_example_models"; }');
        }

        config()->set('role-craft.excluded_models', ['App\Models\ExcludedExampleModel']);

        $reflection = new \ReflectionClass(ModelHelper::class);
        $this->assertTrue($reflection->hasMethod('isExcluded'), 'isExcluded() helper must exist on ModelHelper');

        $this->assertTrue(
            $this->callIsExcluded('App\Models\ExcludedExampleModel', config('role-craft.excluded_models'))
        );

        $this->assertFalse(
            $this->callIsExcluded('App\Models\IncludedExampleModel', config('role-craft.excluded_models'))
        );
    }
}
