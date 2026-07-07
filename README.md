# Snippet Admin Pro for Perfmatters

Enhanced admin tools for managing [Perfmatters](https://perfmatters.io/) code snippets — bulk location editing, a sortable Location column, and OR/AND condition logic — integrated into Perfmatters’ native **Code → Snippets** screens.

## Requirements

- WordPress 5.8+
- PHP 8.1+
- Perfmatters (required for snippet management features)

## Features

- **Location column** — See where each snippet runs at a glance in the snippets list (sortable).
- **Bulk location editing** — Change the run location for multiple snippets at once from the bulk actions bar.
- **Snippet duplication** — Duplicate one or many snippets from the list (row action or bulk button). Copies are created inactive with the same code, location, and conditions.
- **Condition logic (OR / AND)** — Control how Include rules, Exclude rules, and user conditions combine on the single-snippet editor.

## Usage

1. Install and activate the plugin.
2. Go to **Settings → Perfmatters → Code → Snippets**.

### Snippets List — Location Column

The snippets table includes a **Location** column (after Type) showing where each snippet runs. Click the column header to sort by location.

Supported locations match Perfmatters (e.g. Frontend Header, Frontend Footer, Admin Header, Admin Footer, plus PHP and HTML-specific options).

### Snippets List — Bulk Location Editing

1. Select snippets using Perfmatters’ checkboxes (same as bulk Activate/Delete).
2. Use **Change Location To** in the bulk actions bar, pick a location, and click **Apply to Selected**.

The location dropdown only shows options Perfmatters supports for the **selected snippet types** (same rules as the single-snippet editor). Select snippets first; mixed types show only locations valid for all selected types.

### Snippets List — Snippet Duplication

Duplicate snippets from the Perfmatters snippets list in two ways:

1. **Row action** — Click **Duplicate** under a snippet name (next to Edit / Export / Delete).
2. **Bulk button** — Select snippets, then click **Duplicate Selected** in the bulk actions bar.

Duplicates are saved as **inactive** copies with the same code, location, conditions, tags, and other metadata. The copy name appends `Copy` (or `Copy 2`, `Copy 3`, etc. when duplicating an existing copy).

### Snippet Editor — Condition Logic (OR / AND)

Open a snippet from **Settings → Perfmatters → Code → Snippets**. A **Condition Logic** panel appears below Perfmatters’ Include / Exclude / Users sections:

| Setting | Default (Perfmatters-native) | Enhanced option |
|--------|------------------------------|-----------------|
| Include Rules | Match **any** row (OR) | Match **all** rows (AND) |
| Exclude Rules | Exclude when **any** matches | Exclude only when **all** match |
| Include vs. users | **Both** sections required | **Either** section can enable the snippet |

Logic settings are saved in the snippet’s `conditions` meta as a hidden `sapfp:logic` row (JSON in the exclude list). They survive plugin deactivation like any other snippet field.

**Note:** Custom logic is **evaluated while Snippet Admin Pro is active** (runtime shim). With the plugin off, Perfmatters falls back to its built-in rules (include rows are OR; all sections are AND). Reset logic to defaults before deactivating if you rely on non-default behavior.

## Persistence (Safe to Deactivate or Uninstall)

All snippet changes are written **directly into Perfmatters’ own snippet files** via `Perfmatters\PMCS\Snippet::update()`. Location, condition logic, and other metadata live in each snippet’s `.php` docblock — the same storage Perfmatters uses when you save a snippet manually.

This plugin does **not** store snippet data in `wp_options`, custom tables, or separate files. If you deactivate or remove Snippet Admin Pro, your snippets and their settings remain exactly as Perfmatters left them.

## Changelog

See [CHANGELOG.md](CHANGELOG.md) or [GitHub Releases](https://github.com/PineDigitalCo/snippet-admin-pro-perfmatters/releases).

## Development

```bash
composer install
composer lint
composer test
composer run dist-zip
```

## Releasing

See [RELEASING.md](RELEASING.md). In Cursor: **Ship Snippet Admin Pro** or **Release Snippet Admin Pro** (`standalone-wp-plugin-ship` / `standalone-wp-plugin-release` skills).

## License

GPL-2.0-or-later

## Software Use Disclaimer

- *Use at Your Own Risk:* All software, plugins, themes, code snippets, and tools provided or recommended are offered "as is" without any warranties, express or implied. You assume full responsibility for any risks associated with downloading, installing, configuring, or using the software.
- *Limitation of Liability:* In no event shall we (or any contributors, affiliates, or licensors) be liable for any direct, indirect, incidental, special, consequential, or exemplary damages, including but not limited to loss of data, business interruption, or any other losses arising from your use (or inability to use) the software, even if advised of the possibility of such damages.

## AI Disclaimer
- This plugin is 100% vibe-coded, and the code has not been reviewed.
- Coding standards have been followed with effort and to the best of my ability.

## Coding Standards
- PHPCS enforces WordPress Coding Standards unless the project documents exceptions.
- Use clear names, small focused classes/functions, and consistent file organization.
- Document public APIs, hooks, filters, and non-obvious behavior.
- Keep user-facing strings translatable.

### Security
- Check user capabilities before privileged actions.
- Verify nonces (or equivalent) on requests that change state.
- Sanitize input; escape output.
- Use prepared statements for dynamic database queries.
- Validate structured data (JSON, settings arrays, file uploads) before storing or acting on it.
- Avoid exposing sensitive data in AJAX responses, logs, or client-side scripts.

### Performance
- Load assets only where they are needed.
- Avoid global admin or frontend hooks that affect unrelated screens.
- Scope CSS and JavaScript to the plugin’s own markup/containers.
- Register features conditionally when they are disabled or not in context.
- Be deliberate about database queries, caching, and third-party library loading.

### Architecture
- Extend WordPress and other plugins through hooks and filters — avoid editing vendor or core code.
- Keep features modular and loosely coupled where possible.
- Prefer explicit configuration and migration paths over implicit side effects.
- Document extension points for other developers.

### Pull Requests
- Branch from the default branch.
- Run composer lint locally — fix any PHPCS or PHPStan failures.
- Keep changes focused and describe behavior changes clearly.
- Include manual test steps for anything user-facing (admin, frontend, AJAX, REST).
- Call out security, performance, or breaking-change impact in the PR description.

### Releases
Follow the project’s documented release process. In general:

- PHPCS and PHPStan pass.
- Version numbers are updated consistently.
- Distribution packages exclude dev-only files and tooling.
- Changes are tagged and documented.

## Affiliate Disclaimer
- This plugin is not affiliated with, endorsed by, or officially connected to the Perfmatters team in any way. Perfmatters is a trademark of its respective owner.
