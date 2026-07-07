# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.4.0] - 2026-07-07

### Added

- **Condition Logic** panel on the Perfmatters snippet editor (`Include` / `Exclude` / `Users` sections):
  - Include Rules: match **any (OR)** or **all (AND)**
  - Exclude Rules: exclude when **any** matches or only when **all** match
  - Include + Users: require **both (AND)** or **either section (OR)**
- OR/AND settings persist in the snippet file via a `sapfp:logic` sentinel row (Perfmatters-native `conditions` meta).
- Runtime evaluation shim so logic works while Snippet Admin Pro is active.
- GitHub Actions CI and release workflows; `RELEASING.md` ship playbook.

### Changed

- Condition Logic UI uses compact WordPress `button` / `button-primary` / `button-secondary` styles.
- Title Case labels across Condition Logic and bulk location controls.

## [0.3.3] - UNRELEASED

### Changed

- Bulk location dropdown now lists only Perfmatters-valid locations for the **selected snippet types** (same rules as the Perfmatters snippet editor), not every location at once.
- Mixed-type selections show only **common** locations; incompatible combinations show a clear message.

## [0.3.2] - UNRELEASED

### Added

- Sortable **Location** column (click header to toggle A→Z / Z→A). Sort is preserved across bulk location updates via URL state.

## [0.3.1] - UNRELEASED

### Added

- **Location** column on the Perfmatters snippets list (after Type).

## [0.3.0] - UNRELEASED

### Changed

- Bulk location editing now lives on **Settings → Perfmatters → Code → Snippets** (removed separate Tools page).
- Uses Perfmatters’ existing snippet checkboxes and bulk actions bar.

### Added

- Persistence documentation: all changes write to Perfmatters snippet files only (no plugin-owned storage).

## [0.2.0] - UNRELEASED

### Added

- Bulk location editor for Perfmatters snippets (Tools → Snippet Admin Pro).
- Search and type filters on the snippet list.
- Validation so locations are only applied when valid for each snippet type.

## [0.1.0] - UNRELEASED

### Added

- Initial plugin scaffold with Perfmatters detection and Tools admin shell.
- Development tooling: PHPStan, PHPCS, PHPUnit, distribution ZIP build.

[0.4.0]: https://github.com/PineDigitalCo/snippet-admin-pro-perfmatters/releases/tag/v0.4.0
[0.3.3]: https://github.com/PineDigitalCo/snippet-admin-pro-perfmatters/releases/tag/v0.3.3
[0.3.2]: https://github.com/PineDigitalCo/snippet-admin-pro-perfmatters/releases/tag/v0.3.2
[0.3.1]: https://github.com/PineDigitalCo/snippet-admin-pro-perfmatters/releases/tag/v0.3.1
[0.3.0]: https://github.com/PineDigitalCo/snippet-admin-pro-perfmatters/releases/tag/v0.3.0
[0.2.0]: https://github.com/PineDigitalCo/snippet-admin-pro-perfmatters/releases/tag/v0.2.0
[0.1.0]: https://github.com/PineDigitalCo/snippet-admin-pro-perfmatters/releases/tag/v0.1.0
