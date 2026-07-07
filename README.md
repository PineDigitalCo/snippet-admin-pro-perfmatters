# Snippet Admin Pro for Perfmatters

Enhanced admin tools for managing [Perfmatters](https://perfmatters.io/) code snippets, integrated into the Perfmatters **Code → Snippets** screen.

## Requirements

- WordPress 5.8+
- PHP 8.1+
- Perfmatters (required for snippet management features)

## Usage

1. Install and activate the plugin.
2. Go to **Settings → Perfmatters → Code → Snippets**.
3. Select snippets using Perfmatters’ checkboxes (same as bulk Activate/Delete).
4. Use **Change location to** in the bulk actions bar, pick a location, and click **Apply to selected**.

The location dropdown only shows options Perfmatters supports for the **selected snippet types** (same rules as the single-snippet editor). Select snippets first; mixed types show only locations valid for all selected types.

The snippets table includes a **Location** column (after Type) showing where each snippet runs.

Supported locations match Perfmatters (e.g. Frontend Header, Frontend Footer, Admin Header, Admin Footer, plus PHP and HTML-specific options).

### Condition logic (OR / AND)

On a single snippet editor screen (**Settings → Perfmatters → Code → Snippets → open a snippet**), a **Condition logic** panel appears below Perfmatters’ Include / Exclude / Users sections:

| Setting | Default (Perfmatters-native) | Enhanced option |
|--------|------------------------------|-----------------|
| Include rules | Match **any** row (OR) | Match **all** rows (AND) |
| Exclude rules | Exclude when **any** matches | Exclude only when **all** match |
| Include vs. users | **Both** sections required | **Either** section can enable the snippet |

Logic settings are saved in the snippet’s `conditions` meta as a hidden `sapfp:logic` row (JSON in the exclude list). They survive plugin deactivation like any other snippet field.

**Note:** Custom logic is **evaluated while Snippet Admin Pro is active** (runtime shim). With the plugin off, Perfmatters falls back to its built-in rules (include rows are OR; all sections are AND). Reset logic to defaults before deactivating if you rely on non-default behavior.

## Persistence (safe to deactivate or uninstall)

All snippet changes are written **directly into Perfmatters’ own snippet files** via `Perfmatters\PMCS\Snippet::update()`. Location and other metadata live in each snippet’s `.php` docblock — the same storage Perfmatters uses when you save a snippet manually.

This plugin does **not** store snippet data in `wp_options`, custom tables, or separate files. If you deactivate or remove Snippet Admin Pro, your snippets and their locations remain exactly as Perfmatters left them.

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

## Affiliate Disclaimer

- This plugin is not affiliated with, endorsed by, or officially connected to the Perfmatters team in any way. Perfmatters is a trademark of its respective owner.
