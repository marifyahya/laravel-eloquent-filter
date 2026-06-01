# Release Notes

## Unreleased

- Removed built-in field-level `LIKE` syntax to keep the core package simple.
- Field-level `LIKE` filters should be implemented with custom filter methods, classes, or callbacks.
- Removed unused global config defaults.
- Added PHPDoc to the `HasEloquentFilter` trait.
- Fixed normalized relation existence keys for camelCase relation names.
- Improved README and wiki documentation for exact, search, and custom field-level `LIKE` filters.

## v1.2.0

- Added model-level key normalization with `$normalizeFilterKeys`.
- Added query-level override support for `normalize_keys`.
- Improved README documentation for model properties and production usage.

## v1.1.0

- Added `normalize_keys` support for camelCase request keys.
- Added documentation and tests for key normalization.

## v1.0.1

- Improved package metadata.
- Added Packagist and license badges.

## v1.0.0

- Initial stable release.
- Added whitelisted filtering, searching, sorting, date ranges, soft delete filters, relation filtering, filter aliases, and custom filters.
