# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this package is

`i74ifa/role-craft` is a Laravel package that auto-generates `spatie/laravel-permission` permissions (and a default role) from Eloquent models found under `app/Models/`. It ships two artisan commands and a small set of helpers — no runtime services. PHP 8.4+, Laravel 12/13, Spatie Permission 7+.

Distributed as a Composer library; consumers `composer require i74ifa/role-craft` and optionally `php artisan vendor:publish --tag=role-craft-config`.

## Commands

```bash
composer test                       # vendor/bin/phpunit (all suites)
composer test-coverage              # HTML coverage report → ./coverage
vendor/bin/phpunit --testsuite Unit
vendor/bin/phpunit --testsuite Feature
vendor/bin/phpunit --filter ModelHelperExclusionTest          # one class
vendor/bin/phpunit --filter test_wildcard_matches_subdirectory  # one method
```

There is no lint/format step configured. The test bootstrap uses an in-memory SQLite database via Orchestra Testbench — no external DB needed.

## Architecture

The package is intentionally tiny. The "big picture" is in three files:

- **`src/RoleCraftServiceProvider.php`** — registers the two artisan commands and publishes config under tag `role-craft-config`. Uses `mergeConfigFrom`, so new config keys ship as safe defaults without forcing a republish.
- **`src/Console/Commands/RoleCraftCommand.php` (`role-craft:generate`)** — the bootstrapper. Resolves the role (creates if missing), resolves the model list (`--models=...` shorthand or `ModelHelper::getAll()`), then for each model × each action creates the permission if missing. **Gotcha**: it calls `$role->syncPermissions($createdPermissions)` with the *newly created* permissions only — a second run with nothing new effectively empties the role. Treat `generate` as a one-shot bootstrap; use `role-craft:sync` for idempotent role assignment.
- **`src/Console/Commands/RoleCraftSyncCommand.php` (`role-craft:sync`)** — the idempotent assignment tool. `--all` short-circuits to `Permission::all()`. Without it, the same model-resolution path is used (`--models=` or directory scan).

Everything else funnels through **`src/Helpers/ModelHelper.php`**:

- `getAll($abstract, $path, $depth)` — Symfony Finder scan of `models_path`, validates each candidate is a subclass of `Illuminate\Database\Eloquent\Model`, then applies `included_models` (whitelist) and `excluded_models` (blacklist) in that order.
- `matchesAny($model, $patterns)` — shared pattern matcher. Normalizes leading backslashes with `ltrim` and calls `fnmatch($pattern, $normalized, FNM_NOESCAPE)`. **`FNM_NOESCAPE` is critical** — without it `fnmatch()` treats `\` as an escape character and silently fails on namespace patterns like `App\Models\Internal\*`. `isExcluded()` is kept as a thin alias for backward compatibility.
- `getNamespaceFromPath($path)` — reconstructs FQCNs from file paths, but **hard-codes the `app/Models` segment**. If `models_path` is changed to something outside `app/Models`, scanned classes won't resolve. Keep models under `app/Models/...` (subdirs are fine).
- `getPrefixPermission($model)` / `getPermissions($model)` — read `public $prefix_permissions` and `public $prefix_model_permissions` properties from the model for per-model overrides, falling back to the model's `getTable()` and `config('role-craft.default_role_permissions')` respectively.

### Permission naming

```
{subdirectory?}{separator}{table_name}{separator}{action}
```

`getDepth($model, $sep)` derives the `{subdirectory}` segment from the model's filesystem path (only present when `subdirectory_permission_name` is true or `models_depth > 1`). The table segment comes from `getTable()` — so a model with a custom `$table` produces permissions named after that custom table.

### Config keys (`config/role-craft.php`)

The non-obvious ones:

- `included_models` — whitelist. Empty (default) = include everything. Non-empty = only matching FQCNs survive the scan ("exclude all except these"). Supports `fnmatch` wildcards.
- `excluded_models` — blacklist. Applied **after** `included_models`, so you can whitelist a subtree and still carve out specific classes.
- Both filters apply to **directory scans only**. Passing `--models=Foo` explicitly always includes `Foo` — user intent wins over config.
- `models_depth` — `0` means recursive; `>0` means exactly that depth (Finder is called with `depth($depth - 1)`).

## Tests

- `tests/TestCase.php` boots Orchestra Testbench with `RoleCraftServiceProvider` + `PermissionServiceProvider`, runs the Spatie migration against `:memory:` SQLite, and seeds package config (`default_role=admin`, separator, guard).
- Feature tests in `tests/Feature/` create dummy models on the fly via `eval('namespace App\Models; class Foo extends \Illuminate\Database\Eloquent\Model { ... }')` because no real `app/Models/` exists in the test environment. Reuse this pattern when adding new integration tests.
- Unit tests in `tests/Unit/` exercise `ModelHelper::matchesAny()` (and the legacy `isExcluded` alias) via `ReflectionMethod` since both are `protected`.

## Documentation surfaces (keep in sync)

When a public-facing change lands (new config key, command flag, behavioral change), update these in the same commit:

- `README.md` — user-facing install + usage.
- `docs/AGENT_GUIDE.md` — agent-neutral reference (Cursor, Cline, Copilot, Codex, etc.).
- `~/.claude/skills/role-craft/SKILL.md` — user-local Claude Code skill (lives outside the repo, but is the project's preferred Claude Code surface).
- `CHANGELOG.md` — under `## Unreleased`.
