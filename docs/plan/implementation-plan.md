# Implementation Plan: Alamin AI Provider for MiniMax WordPress Plugin

**Author**: Al Amin Ahamed
**Date**: April 2026
**Version**: 1.0.0
**Purpose**: Provide a detailed, step-by-step plan to develop and deploy a custom AI provider plugin for **MiniMax** that integrates seamlessly with the **WordPress PHP AI Client** and **Connectors API** (available in WordPress 7.0+).

## 1. Plugin Overview

- **Plugin Name**: Alamin AI Provider for MiniMax
- **Plugin Slug**: `alamin-ai-provider-for-minimax`
- **Description**: Registers MiniMax as a native AI provider using its OpenAI-compatible API endpoint. Enables chat completions and model selection through **Settings > Connectors**.
- **Key Benefits**:
  - Automatic discovery in the WordPress AI ecosystem.
  - Secure API key management via the Connectors interface.
  - Support for high-performance MiniMax-M2 series models.
  - Competitive pricing with fast inference.
- **Target Audience**: Developers building AI-powered WordPress plugins, themes, or sites that require reliable LLM integration.

**Official MiniMax Endpoint**:
Base URL: `https://api.minimax.io/v1` (OpenAI-compatible)

## 2. Prerequisites

- WordPress 7.0 or higher (with built-in PHP AI Client and Connectors API).
- PHP 7.4 or higher.
- Composer (recommended for dependency management).
- Active MiniMax account with an API key (obtain from https://platform.minimax.io).
- Basic knowledge of WordPress plugin development.

## 3. Plugin Structure

```
alamin-ai-provider-for-minimax/
├── alamin-ai-provider-for-minimax.php                              # Main plugin file (entry point)
├── composer.json                           # Dependencies
├── src/
│   ├── autoload.php                       # Custom autoloader
│   ├── Provider/
│   │   └── MiniMaxProvider.php           # Provider class
│   ├── Models/
│   │   └── MiniMaxTextGenerationModel.php
│   └── Metadata/
│       └── MiniMaxModelMetadataDirectory.php
├── .wordpress-org/                        # Plugin assets (icon, banner)
├── README.md                              # Documentation
├── readme.txt                            # WordPress.org readme
├── LICENSE                               # GPL-2.0-or-later
├── .gitignore
├── .gitattributes
├── composer.lock
├── phpunit.xml.dist
├── phpcs.xml.dist
└── phpstan.neon.dist
```

## 4. Key Implementation Details

### 4.1 Provider ID
- `minimax`

### 4.2 Environment Variable
- `MINIMAX_API_KEY`

### 4.3 Base URL
- `https://api.minimax.io/v1`

### 4.4 Supported Models (Fallback)
When API is unavailable, these models are used:
- `MiniMax-M2.7` - Latest flagship model
- `MiniMax-M2.5` - Mid-range model
- `MiniMax-M2.1` - Budget-friendly option
- `MiniMax-M2` - Legacy model

### 4.5 Capabilities
- Text generation (primary)
- Streaming support (via OpenAI-compatible API)
- Chat completions

## 5. Architecture Pattern

The plugin follows the official WordPress AI Provider pattern:

1. **Provider class** extends `AbstractApiProvider`
2. **Model class** extends `TextGenerationModel`
3. **Metadata directory** implements `ModelMetadataDirectoryInterface`
4. **Dynamic model discovery** via `/v1/models` endpoint
5. **Caching** using WordPress transients

## 6. Installation and Activation Steps

1. Create the plugin folder and files as specified.
2. Run `composer install` inside the plugin directory.
3. Upload the plugin to `wp-content/plugins/` and activate it.
4. Navigate to **Settings → Connectors**.
5. Locate **MiniMax**, enter your API key, and save.
6. The provider is now ready for use across the site.

## 7. Usage Examples

```php
use WordPress\AiClient\AiClient;

// Basic usage
$result = AiClient::prompt( 'Explain WordPress hooks.' )
    ->usingProvider( 'minimax' )
    ->usingModel( 'MiniMax-M2.7' )
    ->generateTextResult();

// Stream response
$result = AiClient::prompt( 'Write a WordPress plugin.' )
    ->usingProvider( 'minimax' )
    ->withStreaming( true )
    ->generateTextResult();
```

## 8. API Key Configuration

### Environment Variable
```bash
export MINIMAX_API_KEY="your-api-key"
```

### WordPress Constant
```php
define( 'MINIMAX_API_KEY', 'your-api-key' );
```

### Connectors Settings
The API key can also be configured via **Settings > Connectors** in WordPress admin.

## 9. Advanced Enhancements

- [x] Dynamic model fetching using the `/v1/models` endpoint.
- [x] WordPress transients for caching model metadata.
- [ ] Add vision support for image understanding.
- [ ] Add function calling support.
- [ ] Add streaming response support.

## 10. Testing Checklist

- [x] Plugin activates without errors.
- [x] Provider appears in Settings > Connectors.
- [x] API key can be configured.
- [x] Text generation works with fallback models.
- [x] Text generation works with API-discovered models.
- [ ] Streaming response works correctly.
- [ ] Error handling (invalid key, rate limits).
- [ ] PHPCS passes with no errors.

## 11. Maintenance

- Update version and changelog for new features or model additions.
- Monitor MiniMax documentation for endpoint or model changes.
- Consider submitting the plugin to the WordPress.org repository.

## 12. References

- MiniMax Platform: https://platform.minimax.io
- MiniMax API Documentation: https://platform.minimax.io/document
- WordPress PHP AI Client: https://github.com/WordPress/wp-ai-client
- WordPress AI Provider Examples: https://github.com/WordPress/ai-provider-for-anthropic

---

**End of Document**
