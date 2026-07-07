# Releasing Snippet Admin Pro for Perfmatters

This repo’s git root is **`wp-content/plugins/snippet-admin-pro-for-perfmatters/`** (not the WordPress site root).

GitHub: [PineDigitalCo/snippet-admin-pro-perfmatters](https://github.com/PineDigitalCo/snippet-admin-pro-perfmatters)

## Quick paths

| Situation | What to do |
|-----------|------------|
| Small fix already on `main` | [Release only](#release-only) |
| Work on a branch | [PR → merge → release](#full-ship-pr--merge--release) |
| Large Perfmatters integration change | PR + extended QA, then release |

**In Cursor:** say **“Ship Snippet Admin Pro”** (full flow) or **“Release Snippet Admin Pro”** (release step only).

---

## First-time GitHub setup

If the plugin folder is not a git repo yet:

```bash
cd wp-content/plugins/snippet-admin-pro-for-perfmatters
git init
git add -A
git commit -m "Initial commit"
git branch -M main
git remote add origin git@github.com:PineDigitalCo/snippet-admin-pro-perfmatters.git
git push -u origin main
```

Create the empty repo on GitHub first if it does not exist.

---

## Full ship (PR → merge → release)

### 1. Pre-flight

```bash
cd wp-content/plugins/snippet-admin-pro-for-perfmatters
git status
composer lint
composer test
```

Fix any failures before opening a PR or tagging.

### 2. Pull request

```bash
git push -u origin your-branch-name
```

Open a PR to **`main`**. Wait until **CI is green** (PHPCS, PHPStan, PHPUnit).

### 3. Merge

Merge in GitHub or merge locally and push `main`.

### 4. Release

See [Release only](#release-only) below.

### 5. Distribute

- **GitHub:** pushing tag `vX.Y.Z` runs [Release workflow](.github/workflows/release.yml) and attaches `snippet-admin-pro-for-perfmatters-X.Y.Z.zip`.
- Upload the same zip elsewhere if you use a separate update channel.

### 6. Monitor

Smoke-test Perfmatters **Code → Snippets** (list + editor) on staging after release.

---

## Release only

From `main` with a clean intent to ship:

```bash
cd wp-content/plugins/snippet-admin-pro-for-perfmatters
git checkout main
git pull origin main
```

Bump version in **three places** (must match):

| File | Field |
|------|--------|
| `snippet-admin-pro-for-perfmatters.php` | `* Version:` header |
| `snippet-admin-pro-for-perfmatters.php` | `SAPFP_VERSION` constant |
| `composer.json` | `"version"` |

Update `CHANGELOG.md` for the new version.

Then:

```bash
composer lint
composer test
composer run dist-zip
```

Commit version files only (not `dist/*.zip`):

```bash
git add snippet-admin-pro-for-perfmatters.php composer.json CHANGELOG.md
git commit -m "Release X.Y.Z"
git tag vX.Y.Z
git push origin main
git push origin vX.Y.Z
```

The Release workflow creates the GitHub Release and uploads the zip.

---

## Local dist only (no release)

```bash
composer lint
composer test
composer run dist-zip
```

Output: `dist/snippet-admin-pro-for-perfmatters-<version>.zip`

In Cursor: **“Build dist for Snippet Admin Pro”** (uses `standalone-wp-plugin-dist` skill).

---

## Cursor skills

| Skill | Trigger examples |
|-------|------------------|
| `standalone-wp-plugin-ship` | Ship Snippet Admin Pro |
| `standalone-wp-plugin-release` | Release Snippet Admin Pro |
| `standalone-wp-plugin-dist` | Build dist, local zip only |

Located in `.cursor/skills/` in the dev workspace.

---

## What not to commit

- `dist/*.zip`
- `vendor/`
- WordPress core, themes, or other plugins outside this directory
