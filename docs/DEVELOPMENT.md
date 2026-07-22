# Development

## Prerequisites

- PHP 7.4+
- Composer

```bash
composer install
```

## Scripts

```bash
composer test        # PHPUnit suite
composer phpcs       # WordPress Coding Standards lint
composer phpcbf      # auto-fix lint issues
composer phpstan     # static analysis
composer release     # build the distributable zip into release/
```

## The AI Client SDK and the `replace` guard

The plugin depends on `WordPress\AiClient\*` from the [WordPress PHP AI Client](https://github.com/WordPress/php-ai-client). WordPress 7.0+ ships this SDK in core, so the plugin must **not** register its own copy — if it did, both copies would try to declare the same namespace and a request that touched the AI Client would hit a `TypeError`.

To prevent that, `composer.json` declares:

```json
"replace": { "wordpress/php-ai-client": "*" }
```

This keeps the SDK out of the shipped `vendor/` entirely; core provides it at runtime.

### Dev-only side install (for PHPStan + PHPUnit)

The `replace` above also removes the SDK classes that static analysis and the test suite need. Those tools get the SDK from a **dev-only side install** that never ships and is never on the plugin's runtime autoloader:

```
tools/ai-client/
  composer.json     # requires wordpress/php-ai-client
  composer.lock     # committed; vendor/ is gitignored
```

Wiring:

- `composer stubs:install` runs `composer install --working-dir=tools/ai-client`.
- `composer test` and `composer phpstan` both prepend `@stubs:install`, so the SDK is auto-provisioned before they run — no manual step, no CI changes.
- PHPStan loads it via `bootstrapFiles` in `phpstan.neon.dist`.
- PHPUnit loads it via a guarded `require_once` in `tests/bootstrap.php`.

## Release / zip hygiene

`composer release` runs `composer install --no-dev --optimize-autoloader`, so only the plugin's own classmap is in `vendor/`. `.distignore` keeps development-only files out of the zip — including `tools/`, `tests/`, `docs/`, and all `*.md` (readme.txt is canonical for WP.org). The AI Client SDK is excluded by the `replace` above.

## CI

- `ci.yml` (lint/analyze/test) is intentionally disabled — run the checks locally.
- `svn-deploy.yml` is active and triggers on **tag push** (`*`). It strips a leading `v`, verifies the tag equals the plugin header version, then lints, runs PHPStan, packages, deploys to WordPress.org SVN, and creates a GitHub release.

A plain push to `trunk` runs nothing that deploys — only pushing a tag ships a release.
