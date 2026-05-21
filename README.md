# Role-Craft for spatie/laravel-permission

[![Latest Version on Packagist](https://img.shields.io/packagist/v/i74ifa/role-craft.svg?style=flat-square)](https://packagist.org/packages/i74ifa/role-craft)
[![Total Downloads](https://img.shields.io/packagist/dt/i74ifa/role-craft.svg?style=flat-square)](https://packagist.org/packages/i74ifa/role-craft)
![GitHub Actions](https://github.com/i74ifa/role-craft/actions/workflows/main.yml/badge.svg)

## Installation

```bash
composer require i74ifa/role-craft
```

## Version Compatibility

| Version  | Laravel       | Spatie Permission | PHP  |
| :------- | :------------ | :---------------- | :--- |
| **v2.x** | 12.0+ / 13.0+ | 7.0+              | 8.4+ |
| **v1.x** | 10.0+ / 11.0+ | 6.x               | 8.2+ |

### optional

publish config

```bash
php artisan vendor:publish --tag=role-craft-config
```

### AI agent guide

This package ships an agent-neutral usage guide for AI coding assistants at:

```
vendor/i74ifa/role-craft/docs/AGENT_GUIDE.md
```

It covers every config key, command flag, per-model override, naming rule, and common recipe in one place. It is plain Markdown — readable by humans and by any agent (Cursor, Cline, Aider, GitHub Copilot, Codex, Claude Code, etc.).

To make your agent pick it up automatically, reference the path from whichever rules file your tool uses, for example:

- `AGENTS.md` (Codex / generic) — link or include the file.
- `.cursor/rules/*.mdc` (Cursor) — `@vendor/i74ifa/role-craft/docs/AGENT_GUIDE.md`.
- `.github/copilot-instructions.md` (GitHub Copilot) — link to the file.
- `CLAUDE.md` or `.claude/skills/role-craft/SKILL.md` (Claude Code) — link to or copy the file.
- `.clinerules`, `.windsurfrules`, `.continuerules` — reference the file path.

## Usage

### Generate role and Permissions

```bash
php artisan role-craft:generate
```

this command will be generate all permissions and `role-craft.default_role` will be created
if you want to change default role name, you can change it in config/role-craft.php after publish config

### Sync permissions

```bash
php artisan role-craft:sync manager --all
```

this will be sync all permissions to `manager` role if it exists
if not exists you want to use `--create` option

```bash
php artisan role-craft:sync manager --create
```

if you want to sync some role from models use `--models` option

```bash
php artisan role-craft:sync manager --models=User --models=Post

# OR Custom Directory
php artisan role-craft:sync manager --models=App\Models\Directory\User
```

Generate role if not exists

```bash
php artisan role-craft:sync manager --create
```

### Including models (whitelist)

Some projects don't need permissions for every model in `app/Models`. To restrict scanning to a curated set, fill `included_models` in `config/role-craft.php`. When the list is non-empty, role-craft **only** processes models matching one of the patterns — everything else is treated as "excluded all":

```php
// config/role-craft.php

'included_models' => [
    App\Models\User::class,          // exact class
    'App\Models\Billing\*',          // whole subtree
    '*\Public\*',                    // any "Public" folder anywhere
],
```

When `included_models` is empty (the default), every Eloquent model under `models_path` is included.

### Excluding models (blacklist)

If you want to skip one or more models, add them to the `excluded_models` array. Excludes are applied **after** the include filter, so you can whitelist a subtree and then carve a few classes out of it:

```php
// config/role-craft.php

'excluded_models' => [
    App\Models\User::class,          // exact class
    'App\Models\Internal\*',         // every model under app/Models/Internal
    '*\Pivot\*',                     // any "Pivot" folder anywhere
],
```

Notes:

- Both filters use `fnmatch` on the fully-qualified class name (leading backslashes are normalized).
- The filters are only applied during directory scans. Passing `--models=Foo` explicitly still includes `Foo` — user intent wins.
- Order of operations: `included_models` first (if non-empty), then `excluded_models`.

### Per-model customization

Override the permission name or action list on a specific model by declaring public properties on it:

```php
class Post extends Model
{
    // Replaces the table-name segment in the permission name
    public $prefix_permissions = 'blog_posts';

    // Replaces the default action list for this model
    public $prefix_model_permissions = ['create', 'view', 'publish', 'archive'];
}
```

## License

This package is released under the [MIT license](https://github.com/i74ifa/role-craft/blob/main/LICENSE).
