# role-craft – AI Agent Usage Guide

This document is a concise, agent-friendly reference for the `i74ifa/role-craft` Laravel package. It is intended for any AI coding assistant (Claude Code, Cursor, Cline, Aider, Copilot, Codex, etc.) and is also useful as a quick reference for human developers.

If your assistant supports per-project rules files, point it at this file. Examples:

- **Cursor**: add `@docs/AGENT_GUIDE.md` (in vendor) to `.cursor/rules/*.mdc`.
- **Cline / Continue / Windsurf**: reference this path in their rules file.
- **Claude Code**: copy this file to `.claude/skills/role-craft/SKILL.md` with a frontmatter block, or reference it from `CLAUDE.md`.
- **GitHub Copilot**: link to it from `.github/copilot-instructions.md`.
- **Codex (OpenAI)**: reference it from `AGENTS.md`.

---

## What the package does

`i74ifa/role-craft` scans `app/Models/` and, for every Eloquent model, generates a set of `spatie/laravel-permission` permissions (and a default role) automatically. Two artisan commands drive everything: `role-craft:generate` and `role-craft:sync`.

## Compatibility

| Package version | Laravel        | Spatie Permission | PHP  |
| :-------------- | :------------- | :---------------- | :--- |
| **v2.x**        | 12.0+ / 13.0+  | 7.0+              | 8.4+ |
| **v1.x**        | 10.0+ / 11.0+  | 6.x               | 8.2+ |

## Installation

```bash
composer require i74ifa/role-craft
php artisan vendor:publish --tag=role-craft-config   # optional
```

Requires `spatie/laravel-permission` to be installed and migrated first.

---

## Config reference (`config/role-craft.php`)

| Key | Type | Default | Meaning |
| :-- | :--- | :------ | :------ |
| `default_permissions` | `string[]` | `['create','view','update','delete','view_any','create_any','update_any','delete_any']` | Action verbs appended to every model when generating. |
| `default_role` | `string` | `'super-admin'` | Role created by `role-craft:generate` and given every permission. |
| `default_role_permissions` | `string[]` | same 8 verbs | Fallback list when a model does not define `$prefix_model_permissions`. |
| `separator` | `string` | `'.'` | Joins segments in permission name: `{depth}{sep}{table}{sep}{action}`. |
| `guard` | `string` | `'web'` | Guard used when creating permissions/roles. |
| `models_depth` | `int` | `0` | `0` = recursive (all subdirs). `>0` = only files exactly that deep under `models_path`. |
| `subdirectory_permission_name` | `bool` | `false` | If true, prefixes permission names with the subdirectory path. |
| `models_path` | `string` | `'app/Models'` | Base directory to scan. Relative to `base_path()`. |
| `included_models` | `string[]` | `[]` | Whitelist. If empty, all models are included. If non-empty, **only** matching FQCNs are scanned (everything else is excluded). Supports `fnmatch` wildcards. Applies to directory scans only. |
| `excluded_models` | `string[]` | `[]` | Blacklist. FQCNs to skip. Applied **after** `included_models`. Supports `fnmatch` wildcards. Applies to directory scans only. |

---

## Permission naming

```
{subdirectory?}{separator}{table_name}{separator}{action}
```

Examples:

- `App\Models\User` → `users.create`, `users.view`, …
- `App\Models\Billing\Invoice` → `billing.invoices.create`, … (when the subdirectory segment is captured)

### Per-model overrides

Declare public properties on the model:

```php
class Post extends Model
{
    // Replaces the table-name segment of the permission name
    public $prefix_permissions = 'blog_posts';

    // Replaces the default action list for this model
    public $prefix_model_permissions = ['create', 'view', 'publish', 'archive'];
}
```

### Helpers (`I74ifa\RoleCraft\Helpers\ModelHelper`)

- `getAll($abstract = false, $path = null, $depth = null)` — scan & return FQCNs, honors `included_models` (whitelist) and `excluded_models` (blacklist).
- `getModel('User')` — resolves shorthand to `App\Models\User`.
- `getTable($model)`, `getPrefixPermission($model)`, `getPermissions($model)`, `getDepth($model, $sep)`.

---

## Commands

### 1. `role-craft:generate` — bootstrap everything

```bash
php artisan role-craft:generate \
  [--guard=web] \
  [--models=User --models=Post] \
  [--role=super-admin] \
  [--path=app/Models] \
  [--depth=0]
```

Behavior:

1. Resolves the role (`--role` or `config('role-craft.default_role')`) — creates it if missing.
2. Resolves the model list (`--models=` if provided, otherwise `ModelHelper::getAll()`).
3. For every model and every action in `getPermissions($model)`, creates the permission if it doesn't already exist.
4. Calls `$role->syncPermissions($createdPermissions)` with the **newly created** permissions only.

> Because step 4 syncs only newly created permissions, a second `generate` run with no new models effectively clears the role. Treat `generate` as the bootstrapper and use `role-craft:sync` for idempotent role assignment.

### 2. `role-craft:sync` — assign permissions to a role

```bash
# Sync ALL existing permissions to a role
php artisan role-craft:sync manager --all

# Create the role first if it doesn't exist
php artisan role-craft:sync manager --create --all

# Sync only specific models (shorthand)
php artisan role-craft:sync manager --models=User --models=Post

# Sync from a fully-qualified class (when model lives in a subdir)
php artisan role-craft:sync manager --models=App\\Models\\Billing\\Invoice

# Other flags
  --guard=web
  --path=app/Models
  --depth=0
```

`--all` short-circuits and syncs every `Permission::all()` to the role. Without `--all`, you must provide either `--models=` or the command will scan the models directory.

---

## Including / excluding models

Two complementary filters control which models are picked up by a directory scan:

```php
// config/role-craft.php

// Whitelist — when non-empty, ONLY matching classes are scanned.
// "Exclude all" is the implicit default for anything not in this list.
'included_models' => [
    'App\Models\Billing\*',
    App\Models\User::class,
],

// Blacklist — applied AFTER included_models. Use to carve exceptions
// out of an otherwise-included subtree.
'excluded_models' => [
    'App\Models\Internal\*',
    '*\Pivot\*',
],
```

Rules:

- Both arrays use `fnmatch` on the fully-qualified class name (leading backslashes are normalized).
- If `included_models` is empty (default), every Eloquent model is included.
- If `included_models` is non-empty, anything not matched is dropped — this is the "exclude all except these" mode.
- `excluded_models` is then applied on top, so you can whitelist `App\Models\Billing\*` and still exclude `App\Models\Billing\Internal\*`.
- Both filters apply to directory scans only. Passing `--models=Foo` explicitly still includes `Foo` (user intent wins).

---

## Recipes

**Fresh install on a new project:**
```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
php artisan vendor:publish --tag=role-craft-config
php artisan role-craft:generate                 # creates all perms + super-admin
php artisan role-craft:sync super-admin --all   # makes the assignment idempotent
```

**Add custom actions to one model:**
```php
// app/Models/Post.php
public $prefix_model_permissions = ['create', 'view', 'publish', 'archive'];
```
Then re-run `role-craft:generate`.

**Restrict scan to a subset:**
```bash
php artisan role-craft:generate --path=app/Models/Public --depth=1
```

**Build a viewer role:**
```bash
php artisan role-craft:sync viewer --create --models=Post --models=Comment
```

---

## Gotchas

- `ModelHelper::getNamespaceFromPath()` hard-codes the `app/Models` prefix when reconstructing FQCNs. Keep models under `app/Models/...` (subdirs are fine) for reliable scanning.
- `role-craft:generate` only assigns *newly created* permissions to the role. Re-running on a stable set assigns nothing. Use `sync` for idempotent assignment.
- `ModelHelper::getAll()` requires the class to be a subclass of `Illuminate\Database\Eloquent\Model`. Abstract base classes and traits are silently skipped.
- Permission names use `getTable($model)`, so models with a custom `$table` get permissions named after the custom table.
