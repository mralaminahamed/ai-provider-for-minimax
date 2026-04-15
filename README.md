# AI Provider for MiniMax

A MiniMax provider for the [PHP AI Client](https://github.com/WordPress/php-ai-client) SDK. Works as both a Composer package and a WordPress plugin.

## Requirements

- PHP 7.4 or higher
- When using with WordPress, requires WordPress 7.0 or higher
    - If using an older WordPress release, the [wordpress/php-ai-client](https://github.com/WordPress/php-ai-client) package must be installed

## Installation

### As a Composer Package

```bash
composer require mralaminahamed/ai-provider-for-minimax
```

### As a WordPress Plugin

1. Download the plugin files
2. Upload to `/wp-content/plugins/ai-provider-for-minimax/`
3. Ensure the PHP AI Client plugin is installed and activated
4. Activate the plugin through the WordPress admin

## Usage

### With WordPress

The provider automatically registers itself with the PHP AI Client on the `init` hook. Simply ensure both plugins are active and configure your API key:

```php
// Set your MiniMax API key (or use the MINIMAX_API_KEY environment variable)
putenv('MINIMAX_API_KEY=your-api-key');

// Use the provider
$result = AiClient::prompt('Hello, world!')
    ->usingProvider('minimax')
    ->generateTextResult();
```

### As a Standalone Package

```php
use WordPress\AiClient\AiClient;
use AlAminAhamed\MiniMaxAiProvider\Provider\MiniMaxProvider;

// Register the provider
$registry = AiClient::defaultRegistry();
$registry->registerProvider(MiniMaxProvider::class);

// Set your API key
putenv('MINIMAX_API_KEY=your-api-key');

// Generate text
$result = AiClient::prompt('Explain quantum computing')
    ->usingProvider('minimax')
    ->generateTextResult();

echo $result->toText();
```

## Supported Models

Available models are dynamically discovered from the MiniMax API. This includes MiniMax-M2 series models for text generation. The provider falls back to a default model list when the API is unavailable.

## Configuration

The provider uses the `MINIMAX_API_KEY` environment variable for authentication. You can set this in your environment or via PHP:

```php
putenv('MINIMAX_API_KEY=your-api-key');
```

## License

GPL-2.0-or-later
