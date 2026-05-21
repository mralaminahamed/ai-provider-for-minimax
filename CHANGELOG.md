# Changelog

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
