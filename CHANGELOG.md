# Changelog

All notable changes to `role-craft` will be documented in this file.

## Unreleased

### Added
- `included_models` config key — a whitelist of fully-qualified class names (with `fnmatch` wildcards). When non-empty, only matching models are scanned, giving an "exclude all except these" mode for projects that need permissions for a curated subset of models. ([config/role-craft.php], [src/Helpers/ModelHelper.php])
- `excluded_models` config key — a blacklist of fully-qualified class names (with `fnmatch` wildcards) skipped during directory scans. Applied **after** `included_models`, so you can whitelist a subtree and still carve out specific classes. ([config/role-craft.php], [src/Helpers/ModelHelper.php])
- `ModelHelper::matchesAny()` — shared pattern-matching helper used by both filters. `ModelHelper::isExcluded()` is kept as a thin alias for backward compatibility.
- `docs/AGENT_GUIDE.md` — agent-neutral usage reference for AI coding assistants (Cursor, Cline, Copilot, Codex, Claude Code, …).
- Unit tests for the include/exclude logic (`tests/Unit/ModelHelperExclusionTest.php`).

### Fixed
- `fnmatch()` silently failing on namespace patterns containing `\` (PHP treats `\` as an escape character by default). The matcher now passes `FNM_NOESCAPE` so wildcards like `App\Models\Internal\*` and `*\Pivot\*` work as expected.

### Notes
- Both filters apply only to directory scans. Passing `--models=Foo` explicitly always includes `Foo` — user intent wins over config.
- README, `docs/AGENT_GUIDE.md`, and the Claude Code skill (`SKILL.md`) document the new keys and the order of operations.

## 1.0.0 - 201X-XX-XX

- initial release
