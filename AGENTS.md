# AGENTS.md

## What this repository is

RuleForge for Woo Lite is a WordPress plugin (currently 0.9.0, per `readme.txt` and
`ruleforge-for-woo-lite.php`) for WooCommerce: store owners create up to 3 rules
that add fees, surcharges, or discounts at cart/checkout without code. Rules are
stored as a `fee_rule` custom post type (registered in `src/Core/class-repository.php`),
edited through a menu under WooCommerce (`src/Admin/class-menu.php`), matched
against cart context (`src/Core/class-conditions.php`, `src/Core/class-context.php`),
and applied via WooCommerce hooks (`src/Woo/class-hooks.php`). Conditions
include subtotal, products in cart, user role, billing country, payment gateway,
day of week, and first order; actions are fixed fee, fee %, and discount %
(`src/Core/class-actions.php`). The plugin loads only when WooCommerce is
present (`ruleforge-for-woo-lite.php`).

## Commands

Only what the repository shows; there is no test runner and no build task in
`composer.json` scripts beyond lint.

- Lint: `composer install`, then `vendor/bin/phpcs -q` — composer script `phpcs`
  from `composer.json`; the ruleset is `phpcs.xml.dist` (WordPress-Extra +
  WordPress-Docs, `testVersion 7.4-`). The same check runs in CI
  (`.forgejo/workflows/ci.yml`) and the release workflow
  (`.github/workflows/release.yml`).
- Build: `SLUG=ruleforge-for-woo-lite VERSION=<version> ./tools/build-zip.sh`
  (from `tools/build-zip.sh`; the release workflow
  `.github/workflows/release.yml` invokes it this way). For the WordPress.org
  SVN export used by that workflow: `SLUG=ruleforge-for-woo-lite
  VERSION=<version> ./tools/make-svn-export.sh` (from `tools/make-svn-export.sh`).
  Note both scripts default `SLUG` to a different plugin's slug, so pass `SLUG`
  and `VERSION` explicitly.
- Version bump: `php tools/bump-version.php <version> <slug>` (from
  `tools/bump-version.php`; updates `Version:` in the plugin file and
  `Stable tag:` in `readme.txt`).
- Test: none found — the repository contains no test files or test runner.
  The CI job in `.forgejo/workflows/ci.yml` only syntax-checks tracked `.php`
  (`php -l`), `.js` (`node --check`), `.json` (JSON parse) and `.sh` (`bash -n`)
  files, then runs phpcs.

## Areas

- `plugin` — `ruleforge-for-woo-lite.php`, `readme.txt`, `composer.json`, `composer.lock`, `phpcs.xml.dist`
- `src` — `src/`
- `assets` — `assets/`
- `wporg-assets` — `assets-wporg/`, `screenshot-1.png`, `screenshot-2.png`
- `tools` — `tools/`
- `workflows` — `.github/workflows/`, `.forgejo/workflows/`
- `docs` — `CHECKLIST.md`, `STEP-BY-STEP.md`
