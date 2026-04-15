# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
# Run all tests
composer test

# Run a single test file
./vendor/bin/phpunit tests/phpunit/Provider/MiniMaxProviderTest.php

# Run a single test method
./vendor/bin/phpunit --filter test_provider_has_correct_base_url

# Lint (check only)
composer phpcs

# Lint (auto-fix)
composer phpcbf

# Static analysis
composer phpstan
```

## Architecture

Dual-purpose codebase: works as a standalone Composer package **and** a WordPress plugin. Entry point is `plugin.php`; `src/` contains all logic.

### Class hierarchy (SDK pattern)

All classes extend from `wordpress/wp-ai-client` (SDK):

```
AbstractApiProvider  (SDK)
  └── MiniMaxProvider              # registers provider ID "minimax", base URL https://api.minimax.io/v1

AbstractOpenAiCompatibleTextGenerationModel  (SDK)
  └── MiniMaxTextGenerationModel   # adds MiniMax-Provider header, createRequest override

ModelMetadataDirectoryInterface  (SDK)
  └── MiniMaxModelMetadataDirectory  # fetches models from API, caches via WP transients (1hr success,
                                      # 5min on failure), falls back to hardcoded model list
```

`MiniMaxSettings` — standalone WP settings page. Registers wp-admin options page at `options-general.php?page=minimax-settings`, stores settings under option key `minimax_settings`.

### Bootstrap flow (WordPress)

1. `plugin.php` defines `MINIMAX_PLUGIN_FILE` constant and loads `vendor/autoload.php`
2. `init` hook (priority 5): calls `register_provider()` → registers `MiniMaxProvider` with `AiClient::defaultRegistry()`
3. `init` hook (priority 5): calls `init_settings()` → `MiniMaxSettings::init()` wires up `admin_menu` and `admin_init` hooks

### API key resolution (priority order)

1. `MINIMAX_API_KEY` environment variable
2. WordPress option `wp_ai_client_credentials['minimax']['api_key']`

### Coding standards

- WordPress Coding Standards (`phpcs.xml.dist`) — text domain `ai-provider-for-minimax`
- `declare(strict_types=1)` on every file
- Namespace root: `AlAminAhamed\MiniMaxAiProvider\`
- PHPStan at `level: max` (WP function stubs via `szepeviktor/phpstan-wordpress`)
- All output escaped with `esc_html()`, `esc_attr()`, `esc_url()`; all text wrapped with `__()` / `esc_html__()`
