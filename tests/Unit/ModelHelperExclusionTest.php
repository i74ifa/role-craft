<?php

namespace I74ifa\RoleCraft\Tests\Unit;

use I74ifa\RoleCraft\Helpers\ModelHelper;
use I74ifa\RoleCraft\Tests\TestCase;
use ReflectionMethod;

class ModelHelperExclusionTest extends TestCase
{
    protected function callIsExcluded(string $model, array $patterns): bool
    {
        return $this->callMatchesAny($model, $patterns);
    }

    protected function callMatchesAny(string $model, array $patterns): bool
    {
        $method = new ReflectionMethod(ModelHelper::class, 'matchesAny');
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
        $this->assertTrue($reflection->hasMethod('matchesAny'), 'matchesAny() helper must exist on ModelHelper');

        $this->assertTrue(
            $this->callMatchesAny('App\Models\ExcludedExampleModel', config('role-craft.excluded_models'))
        );

        $this->assertFalse(
            $this->callMatchesAny('App\Models\IncludedExampleModel', config('role-craft.excluded_models'))
        );
    }

    public function test_isExcluded_alias_still_works_for_backward_compatibility()
    {
        $this->assertTrue(
            $this->callIsExcluded('App\Models\User', ['App\Models\User'])
        );

        $this->assertFalse(
            $this->callIsExcluded('App\Models\Post', ['App\Models\User'])
        );
    }

    public function test_included_models_empty_list_includes_everything()
    {
        $included = [];

        $this->assertFalse(
            !empty($included) && !$this->callMatchesAny('App\Models\User', $included)
        );
    }

    public function test_included_models_acts_as_whitelist_when_non_empty()
    {
        $included = ['App\Models\Public\*'];

        $this->assertTrue(
            $this->callMatchesAny('App\Models\Public\Article', $included)
        );

        $this->assertFalse(
            $this->callMatchesAny('App\Models\Internal\AuditLog', $included)
        );
    }

    public function test_included_models_supports_exact_class_match()
    {
        $included = ['App\Models\User'];

        $this->assertTrue(
            $this->callMatchesAny('App\Models\User', $included)
        );

        $this->assertFalse(
            $this->callMatchesAny('App\Models\Post', $included)
        );
    }

    public function test_included_models_supports_multiple_patterns()
    {
        $included = [
            'App\Models\User',
            'App\Models\Billing\*',
        ];

        $this->assertTrue($this->callMatchesAny('App\Models\User', $included));
        $this->assertTrue($this->callMatchesAny('App\Models\Billing\Invoice', $included));
        $this->assertFalse($this->callMatchesAny('App\Models\Post', $included));
    }

    public function test_excluded_overrides_included_for_carve_outs()
    {
        $included = ['App\Models\Billing\*'];
        $excluded = ['App\Models\Billing\Internal\*'];

        $included_match = $this->callMatchesAny('App\Models\Billing\Internal\AuditLog', $included);
        $excluded_match = $this->callMatchesAny('App\Models\Billing\Internal\AuditLog', $excluded);

        $this->assertTrue($included_match);
        $this->assertTrue($excluded_match);
    }

    public function test_get_all_respects_included_models_whitelist()
    {
        if (!class_exists('App\Models\WhitelistKeepModel')) {
            eval('namespace App\Models; class WhitelistKeepModel extends \Illuminate\Database\Eloquent\Model { protected $table = "whitelist_keep_models"; }');
        }

        if (!class_exists('App\Models\WhitelistDropModel')) {
            eval('namespace App\Models; class WhitelistDropModel extends \Illuminate\Database\Eloquent\Model { protected $table = "whitelist_drop_models"; }');
        }

        config()->set('role-craft.included_models', ['App\Models\WhitelistKeepModel']);

        $included = config('role-craft.included_models');

        $this->assertTrue($this->callMatchesAny('App\Models\WhitelistKeepModel', $included));
        $this->assertFalse($this->callMatchesAny('App\Models\WhitelistDropModel', $included));
    }
}
