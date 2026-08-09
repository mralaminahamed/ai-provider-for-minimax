# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
# Run all tests
composer test

# Run a single test file
./vendor/bin/phpunit tests/phpunit/Provider/ProviderTest.php

# Run a single test method
./vendor/bin/phpunit --filter test_provider_has_correct_base_url

# Lint (check only)
composer phpcs

# Lint (auto-fix)
composer phpcbf

# Static analysis
composer phpstan

# Build production release (strips dev deps, optimises autoloader)
composer install --no-dev --no-interaction --prefer-dist -o
```

## Reference Repositories

| Repository | URL |
|---|---|
| Base SDK | https://github.com/WordPress/wp-ai-client |
| Reference Provider | https://github.com/WordPress/ai-provider-for-anthropic |
| OpenAI Provider | https://github.com/WordPress/ai-provider-for-openai |
| Google Provider | https://github.com/WordPress/ai-provider-for-google |

## Directory Structure

```
alamin-ai-provider-for-minimax/
├── alamin-ai-provider-for-minimax.php        # Entry point: constants, autoloader, boot
├── class-ai-provider-for-minimax.php   # Main class: every hook the plugin registers
├── includes/
│   ├── Availability/
│   │   └── ProviderAvailability.php
│   ├── Metadata/
│   │   └── ModelMetadataDirectory.php
│   ├── Models/
│   │   └── TextGenerationModel.php
│   ├── Provider/
│   │   └── Provider.php
│   └── Settings/
│       └── Settings.php
├── templates/
│   └── admin/
│       ├── field-frequency-penalty.php
│       ├── field-max-tokens.php
│       ├── field-model.php
│       ├── field-presence-penalty.php
│       ├── field-temperature.php
│       ├── field-top-p.php
│       ├── section-general.php
│       └── settings-page.php
├── assets/
│   └── images/
│       └── minimax.svg
├── .wordpress-org/          # WP.org assets (icon, banner, screenshots)
├── composer.json
├── composer.lock
└── readme.txt
```

## Architecture

Dual-purpose codebase: works as a standalone Composer package **and** a WordPress plugin. Entry point is `alamin-ai-provider-for-minimax.php`; all PHP logic lives under `includes/`; admin UI templates live under `templates/admin/`.

### Class hierarchy (SDK pattern)

All classes extend from `wordpress/wp-ai-client` (provided by WordPress core on WP 7.0+):

```
AbstractApiProvider  (SDK)
  └── Provider              # provider ID "minimax", base URL https://api.minimax.io/v1

AbstractOpenAiCompatibleTextGenerationModel  (SDK)
  └── TextGenerationModel   # adds MiniMax-Provider header via createRequest()

ModelMetadataDirectoryInterface  (SDK)
  └── ModelMetadataDirectory  # fetches /v1/models, caches via WP transients
                                      # (1hr on success, 5min on failure),
                                      # falls back to hardcoded model list when API unavailable

ProviderAvailabilityInterface  (SDK)
  └── ProviderAvailability    # checks all 3 API key sources (see below)
```

`Settings` — standalone WP settings page (not in SDK hierarchy). Option key: `minimax_settings`. Settings page: `options-general.php?page=minimax-settings`.

### Bootstrap flow (WordPress)

1. `alamin-ai-provider-for-minimax.php` defines `MINIMAX_VERSION`, `MINIMAX_PLUGIN_FILE`, `MINIMAX_URL` and `MINIMAX_PATH`, then loads `vendor/autoload.php` — returning early rather than fataling if it is absent, which is the case for a git checkout with no `composer install`
2. `ai_provider_for_minimax()` returns the `AI_Provider_For_MiniMax` singleton and `init()` registers every hook
3. `init` hook (priority 5): `register_provider()` → registers `Provider` with `AiClient::defaultRegistry()`
4. `init` hook (priority 5): `init_settings()` → `Settings::init()` → wires up `admin_menu` and `admin_init`
5. `wpai_has_ai_credentials` / `wpai_pre_has_valid_credentials_check` → `declare_credentials()` / `declare_valid_credentials()`

Priority 5 is deliberate: anything generating text on `init` needs the provider
in the registry before it asks.

`class-ai-provider-for-minimax.php` holds the wiring and nothing else — every hook the plugin
registers is visible in one file. The work lives in `includes/`.

### API key resolution (priority order)

All three sources are checked by `ProviderAvailability::isConfigured()`, the `wpai_has_ai_credentials` filter, and `get_api_key()` in `ModelMetadataDirectory`:

1. `MINIMAX_API_KEY` environment variable
2. WordPress option `connectors_ai_minimax_api_key` (WP 7.0+ Connectors page)
3. WordPress option `wp_ai_client_credentials['minimax']['api_key']` (legacy)

**Important:** all three sources must be consistent across `ProviderAvailability`, `ModelMetadataDirectory::get_api_key()`, and the `wpai_has_ai_credentials` / `wpai_pre_has_valid_credentials_check` filter callbacks in the plugin bootstrap.

### Required AbstractApiProvider methods

```php
protected static function baseUrl(): string
protected static function createModel(ModelMetadata $model_metadata, ProviderMetadata $provider_metadata): ModelInterface
protected static function createProviderMetadata(): ProviderMetadata
protected static function createProviderAvailability(): ProviderAvailabilityInterface
protected static function createModelMetadataDirectory(): ModelMetadataDirectoryInterface
```

### Required ModelMetadataDirectoryInterface methods

```php
public function listModelMetadata(): array           // returns ModelMetadata[]
public function hasModelMetadata(string $model_id): bool
public function getModelMetadata(string $model_id): ModelMetadata  // throws InvalidArgumentException
```

### ModelMetadata constructor

```php
new ModelMetadata(
    $model_id,                                       // string
    $model_name,                                     // string
    [ CapabilityEnum::textGeneration() ],            // array<CapabilityEnum>
    [                                                // array<SupportedOption>
        new SupportedOption( OptionEnum::temperature() ),
        new SupportedOption( OptionEnum::maxTokens() ),
        new SupportedOption( OptionEnum::topP() ),
        new SupportedOption( OptionEnum::presencePenalty() ),
        new SupportedOption( OptionEnum::frequencyPenalty() ),
        new SupportedOption( OptionEnum::stopSequences() ),
        new SupportedOption( OptionEnum::systemInstruction() ),
        new SupportedOption( OptionEnum::functionDeclarations() ),
    ]
)
```

## Coding Standards

- WordPress Coding Standards (`phpcs.xml.dist`) — text domain `alamin-ai-provider-for-minimax`
- `declare(strict_types=1)` on every PHP file
- Namespace root: `MiniMax\MiniMaxAiProvider\`
- PHPStan at `level: max` (WP function stubs via `szepeviktor/phpstan-wordpress`)
- All output escaped: `esc_html()`, `esc_attr()`, `esc_url()`
- All strings wrapped: `__()` / `esc_html__()`
- Templates set variables then `require` the template file — no logic inside template files

## Common Mistakes to Avoid

- **Do NOT** add `wordpress/php-ai-client` to Composer production deps — the SDK is provided by WordPress core (WP 7.0+)
- **Do NOT** use `TextGenerationCapability` class — use `CapabilityEnum::textGeneration()`
- **Do NOT** omit the `connectors_ai_minimax_api_key` option check from any code that reads the API key
- **Do NOT** downgrade GitHub Actions versions — current baseline: `actions/checkout@v6`, `actions/cache@v5`, `actions/upload-artifact@v7`, `actions/download-artifact@v8`, `softprops/action-gh-release@v3`
- **Do NOT** hardcode only a subset of `SupportedOption` entries — declare all options the API actually supports

## CI / Release Workflows

### Release pipeline (`.github/workflows/svn-deploy.yml`)

Five-job DAG triggered on any tag push:

```
meta ──┬── lint ─────┐
       └── phpstan ───┴── package ── deploy
```

| Job | Purpose | Blocks |
|---|---|---|
| `meta` | Validate tag == plugin header version; detect prerelease (`alpha`/`beta`/`rc`) | all |
| `lint` | Sensitive-file scan + PHP syntax lint + PHPCS | `package` |
| `phpstan` | PHPStan level max, 2G memory | `package` |
| `package` | `composer --no-dev --classmap-authoritative` + distignore rsync into `dist/<slug>/` + clean-dist guard + zip | `deploy` |
| `deploy` | 10up SVN (`BUILD_DIR: dist/<slug>`, `ASSETS_DIR: .wordpress-org`) + GH release with prerelease flag + job summary | — |

**Action versions (verified 2026-06):** `checkout@v6`, `cache@v5`, `upload-artifact@v7`, `download-artifact@v8`, `setup-php@v2`, `10up/action-wordpress-plugin-deploy@stable`, `softprops/action-gh-release@v3`

**Per-job Composer caches** — `composer-lint-*`, `composer-phpstan-*` (separate keys prevent cross-job cache poisoning).

**Distributable guard** — `package` job exits 1 if `.git`, `.github`, `tests`, `CLAUDE.md`, `phpcs*.xml*`, `.DS_Store` etc appear inside `dist/<slug>/` after the rsync step.

### Asset/readme sync (`.github/workflows/svn-readme-assets-update.yml`)

Triggered on push to `trunk` when `.wordpress-org/**` or `readme.txt` changes, or manually via `workflow_dispatch`. Uses `10up/action-wordpress-plugin-asset-update@stable`.

### CI (`.github/workflows/ci.yml`)

Three jobs run on push/PR to `trunk`/`main`: `lint` (PHPCS), `analyze` (PHPStan), `test` (PHPUnit across PHP 7.4–8.3 matrix with MySQL service). Cache keys are per-PHP-version (`composer-${{ runner.os }}-php${{ matrix.php }}-*`).

### Tagging a release

```bash
# 1. Bump version in plugin .php header and readme.txt Stable tag
# 2. Add changelog entry to readme.txt == Changelog ==
# 3. Commit and push to trunk
git add alamin-ai-provider-for-minimax.php readme.txt
git commit -m "chore: bump version to X.Y.Z"
git push

# 4. Tag and push — this triggers svn-deploy.yml
git tag X.Y.Z
git push origin X.Y.Z
```

## Key SDK Classes

The SDK is provided by **WordPress core** (WP 7.0+) — not bundled via Composer. These classes are autoloaded by WordPress at runtime:

| Class | Namespace |
|---|---|
| `ModelMetadata` | `WordPress\AiClient\Providers\Models\DTO` |
| `SupportedOption` | `WordPress\AiClient\Providers\Models\DTO` |
| `CapabilityEnum` | `WordPress\AiClient\Providers\Models\Enums` |
| `OptionEnum` | `WordPress\AiClient\Providers\Models\Enums` |
| `ModelMetadataDirectoryInterface` | `WordPress\AiClient\Providers\Contracts` |
| `ProviderAvailabilityInterface` | `WordPress\AiClient\Providers\Contracts` |
| `ProviderMetadata` | `WordPress\AiClient\Providers\DTO` |
| `AbstractApiProvider` | `WordPress\AiClient\Providers\ApiBasedImplementation` |
| `AbstractOpenAiCompatibleTextGenerationModel` | `WordPress\AiClient\Providers\OpenAiCompatibleImplementation` |
| `ApiKeyRequestAuthentication` | `WordPress\AiClient\Providers\Http\DTO` |
| `AiClient` | `WordPress\AiClient` |
