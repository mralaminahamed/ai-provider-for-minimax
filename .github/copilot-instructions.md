# Copilot Instructions for AI Provider for MiniMax Plugin

## Purpose

This file provides comprehensive guidelines for using GitHub Copilot in the AI Provider for MiniMax WordPress plugin project. The plugin registers MiniMax as an AI provider for the WordPress AI Client SDK.

## Project Overview

- **Technology Stack**: WordPress Plugin, PHP 7.4+, Composer
- **Architecture**: Extends `wordpress/wp-ai-client` SDK classes; no frontend build step
- **Purpose**: Integrate the MiniMax API with WordPress via the WP AI Client provider interface

## Coding Standards

### PHP Standards

- Follow WordPress Coding Standards (WPCS) — PSR-4 autoloading with `MiniMax\MiniMaxAiProvider\` namespace
- PHP 7.4+ minimum; `declare(strict_types=1)` on every file
- Class names: PascalCase (e.g., `Provider`)
- Method/variable names: snake_case (WordPress style)
- File names: snake_case with hyphens
- PHPDoc comments for all classes, methods, and properties
- All output escaped with `esc_html()`, `esc_attr()`, `esc_url()`
- All translatable strings wrapped in `__()` / `esc_html__()`; text domain `alamin-ai-provider-for-minimax`
- PHPStan at `level: max` with `szepeviktor/phpstan-wordpress` stubs

## Class Hierarchy (SDK pattern)

All plugin classes extend from the `wordpress/wp-ai-client` SDK:

```
AbstractApiProvider  (SDK)
  └── Provider              # registers provider ID "minimax"

AbstractOpenAiCompatibleTextGenerationModel  (SDK)
  └── TextGenerationModel   # adds MiniMax-Provider header

ModelMetadataDirectoryInterface  (SDK)
  └── ModelMetadataDirectory  # API fetch + WP transient cache + fallback list
```

`Settings` — standalone WP settings page; not part of the SDK hierarchy.

## File Structure

```
alamin-ai-provider-for-minimax.php   # Plugin entry point; defines constant, loads autoloader
src/
  Provider.php                # Provider registration
  Metadata/
    ModelMetadataDirectory.php # Model list (API + fallback)
  Models/
    TextGenerationModel.php   # Text generation model
  Settings/
    Settings.php              # WP admin settings page
```

## API Key Resolution (priority order)

1. `MINIMAX_API_KEY` environment variable
2. WordPress option `wp_ai_client_credentials['minimax']['api_key']`

## Best Practices

### Code Quality

- Write clear, self-documenting code with meaningful names
- No comments unless the WHY is non-obvious
- Keep functions small and focused
- Avoid code duplication; use shared utilities

### Security & Performance

- Never expose API keys in frontend or logs
- Use WordPress nonce verification for all admin form submissions
- Sanitize and validate all user inputs
- Cache API responses via WP transients (1 hour TTL; 5 min on error)

## Development Workflow

### Setup Requirements

- PHP 7.4+ with Composer
- WordPress 6.7+ with `wordpress/wp-ai-client` installed

### Build Commands

- `composer test` — PHP unit tests
- `composer phpcs` — PHP code linting
- `composer phpcbf` — Auto-fix lint issues
- `composer phpstan` — Static analysis
- `composer release` — Build production zip

## Testing Guidelines

### PHP Testing

- Use PHPUnit + Brain Monkey for unit tests
- Test metadata directory with both API-available and API-unavailable scenarios
- Verify transient caching and fallback model list
- Test settings sanitisation edge cases

## Copilot Usage Guidelines

### When to Use Copilot

- Generating boilerplate (provider classes, metadata directories)
- Implementing common SDK patterns
- Creating PHPDoc blocks
- Writing unit test scaffolding

### When NOT to Use Copilot

- API key handling and credential storage
- WordPress security hooks (nonces, capability checks)
- WP.org compliance logic

### Review Checklist

- Verify WPCS compliance (`composer phpcs`)
- Check PHPStan passes (`composer phpstan`)
- Ensure all strings use correct text domain
- Test functionality in WordPress environment

---

For questions, contact a maintainer or refer to AGENTS.md for complete project guidelines.
