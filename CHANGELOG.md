# Changelog

## [Unreleased]

### Added

- Connection status on the settings page — indicates whether a MiniMax API key is configured, with a link to the Connectors screen to add one.
- A "Connectors" quick link on the Plugins screen, next to the existing "Settings" link.

### Changed

- Synced the built-in fallback model list to the official MiniMax catalogue — added `MiniMax-M3` and removed the retired `MiniMax-M1` and `MiniMax-Text-01` (8 models total).
- A default model (`MiniMax-M3`) is now selected out of the box instead of an empty choice, so generation works before the live model list loads.

### Fixed

- Autoloader collision with WordPress core's bundled AI Client. When the plugin's copy of `wordpress/php-ai-client` registered the `WordPress\AiClient\` namespace before core did, any request that triggered the AI Client hit a `TypeError`. The package is now excluded from the shipped plugin via a Composer `replace`; WordPress core provides those classes at runtime.
- Restored PHPStan, PHPUnit, and CI, which the change above had broken by removing `WordPress\AiClient\*` from `vendor/`. Those classes are now loaded for static analysis and the test suite only, from a dev-only side install (`tools/ai-client/`), and are never registered on the plugin's runtime autoloader.

## [1.2.1] - 2026-05-21

### Fixed

- Connector no longer shows as "Connected" on the WordPress Connectors page before any API key is entered — replaced `ListModelsApiBasedProviderAvailability` with a custom availability class that checks for an actual API key (env var, WP Connectors option, or legacy credentials option)

## [1.2.0] - 2026-05-21

### Added

- Provider logo SVG displayed on the WordPress Connectors page

### Fixed

- Declared credential availability via `wpai_has_ai_credentials` and `wpai_pre_has_valid_credentials_check` filters so the WordPress AI admin page no longer shows a false "no valid connector" warning when an API key is configured via the Connectors page

### Changed

- Expanded test suite from 33 to 44 tests covering full model list, provider logo path, highspeed variants, settings edge cases, and all 9 model IDs

## [1.1.0] - 2026-05-21

### Changed

- Updated fallback model list to include all current MiniMax text generation models: M2.7, M2.7 Highspeed, M2.5, M2.5 Highspeed, M2.1, M2.1 Highspeed, M2, M1, and Text-01
- Added `Domain Path` header field to plugin file
- Improved plugin file header field ordering and alignment per WordPress.org standard
- Added file-level PHPDoc block to plugin bootstrap file
- Updated tested up to WordPress 7.0

## [1.0.0] - 2026-05-11

### Added

- Initial release
- MiniMax AI provider integration for the WordPress AI Client
- Dynamic model discovery from MiniMax API with transient caching (1 hour TTL; 5 min on error)
- Fallback hardcoded model list when API is unavailable
- WordPress admin settings page (Settings > MiniMax) for default model, temperature, and max tokens
- API key resolution via `MINIMAX_API_KEY` environment variable or WordPress AI Client credentials
- Support for MiniMax-M2 series models via OpenAI-compatible API
