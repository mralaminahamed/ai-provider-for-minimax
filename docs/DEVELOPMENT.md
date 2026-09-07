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

The plugin autoloads its own classes from `includes/autoload.php` and has no runtime dependency — `composer.json` requires `php` and `ext-json` and nothing else — so **nothing under `vendor/` ships**. `.distignore` excludes `vendor/`, `composer.json` and `composer.lock` along with `tools/`, `tests/`, `docs/` and all `*.md` (readme.txt is canonical for WP.org). The AI Client SDK is excluded by the `replace` above, and provided by core at runtime.

This matches the three official WordPress AI provider plugins, which ship a hand-written `src/autoload.php` and no vendor directory at all.

## CI

`ci.yml` has four jobs: **lint** (PHPCS), **analyze** (PHPStan), **test** (PHPUnit across PHP 7.4–8.3), and **test-core-sdk**.

### Why `test-core-sdk` exists

`tools/ai-client` requires `wordpress/php-ai-client: ^1.2`, which resolves to the newest release — currently 1.4.0. **WordPress core ships an older one.** A call written against the newer SDK therefore passes every other job and fatals on a real site.

That job pins the version core ships (`CORE_AI_CLIENT_VERSION` in `ci.yml`) and runs the suite against it. The difference is not academic: `EmbeddingGenerationModelInterface` arrived in 1.4.0 and does not exist in 1.3.1, which is why the official OpenAI provider guards its embedding model with `interface_exists()`.

**When core updates its bundled copy, bump `CORE_AI_CLIENT_VERSION`.** Read the current value from any WordPress install:

```bash
grep "const VERSION" wp-includes/php-ai-client/src/AiClient.php
```

To check the same thing locally:

```bash
composer require --working-dir=tools/ai-client wordpress/php-ai-client:1.3.1
vendor/bin/phpunit
composer install --working-dir=tools/ai-client   # back to the newest
```

### Workflow state

`ci.yml` is currently **disabled** in the repository (`gh workflow list --all` shows `disabled_manually`). It was switched off on 2026-07-12 after a red run whose cause — PHPStan and PHPUnit not seeing `WordPress\AiClient\*` at all — was fixed later by the `tools/ai-client` side install described above. A fresh clone of `trunk` now passes lint, analyze and test.

Re-enable with:

```bash
gh workflow enable ci.yml --repo mralaminahamed/ai-provider-for-minimax
```

`svn-deploy.yml` is active and triggers on **tag push** (`*`). It strips a leading `v`, verifies the tag equals the plugin header version, then lints, runs PHPStan, packages, deploys to WordPress.org SVN, and creates a GitHub release.

A plain push to `trunk` runs nothing that deploys — only pushing a tag ships a release.
