# AI Provider for MiniMax

An independent, third-party MiniMax provider for the [WordPress PHP AI Client](https://github.com/WordPress/php-ai-client) SDK. Works as both a Composer package and a WordPress plugin. Not affiliated with, endorsed by, or sponsored by MiniMax.

## Requirements

- PHP 7.4 or higher
- WordPress 7.0 or higher (AI Client SDK is included in WordPress core)
  - On older WordPress releases, the [WordPress AI Client](https://wordpress.org/plugins/wp-ai-client/) plugin must be installed separately

## Installation

### As a WordPress Plugin

1. Download the plugin zip
2. Go to **Plugins > Add New > Upload Plugin** in your WordPress admin
3. Upload and activate

### As a Composer Package

```bash
composer require mralaminahamed/ai-provider-for-minimax
```

## Configuration

### WordPress Admin

Go to **Settings > MiniMax** to configure:

| Setting | Description | Default |
|---|---|---|
| API Key | Your MiniMax API key | — |
| Default Model | Model used when none is specified | First available |
| Temperature | Output randomness (0.0–2.0) | 1.0 |
| Max Tokens | Maximum response length | 2048 |
| Top P | Nucleus sampling threshold (0.0–1.0) | 1.0 |
| Presence Penalty | Penalise repeated topics (-2.0–2.0) | 0.0 |
| Frequency Penalty | Penalise repeated tokens (-2.0–2.0) | 0.0 |

### Environment Variable

`MINIMAX_API_KEY` takes priority over the database setting:

```bash
export MINIMAX_API_KEY=your-api-key
```

Get your API key at [platform.minimax.io/user-center/basic-information/interface-key](https://platform.minimax.io/user-center/basic-information/interface-key).

## Usage

### With WordPress (automatic)

The provider registers itself on the `init` hook. No manual setup required beyond entering your API key.

```php
use WordPress\AiClient\AiClient;

$result = AiClient::prompt('Explain quantum computing')
    ->usingProvider('minimax')
    ->generateTextResult();

echo $result->toText();
```

### As a Standalone Composer Package

```php
use WordPress\AiClient\AiClient;
use AlAminAhamed\MiniMaxAiProvider\Provider\MiniMaxProvider;

$registry = AiClient::defaultRegistry();
$registry->registerProvider(MiniMaxProvider::class);

putenv('MINIMAX_API_KEY=your-api-key');

$result = AiClient::prompt('Explain quantum computing')
    ->usingProvider('minimax')
    ->generateTextResult();

echo $result->toText();
```

## Supported Models

Models are discovered dynamically from the MiniMax API (cached for 1 hour). The fallback list includes 9 models:

- MiniMax-M2.7, MiniMax-M2.7 Highspeed
- MiniMax-M2.5, MiniMax-M2.5 Highspeed
- MiniMax-M2.1, MiniMax-M2.1 Highspeed
- MiniMax-M2
- MiniMax-M1
- MiniMax-Text-01

## Architecture

```
includes/
  Provider/
    MiniMaxProvider.php              # Registers provider ID "minimax", base URL https://api.minimax.io/v1
    MiniMaxTextGenerationModel.php   # OpenAI-compatible text generation
  Metadata/
    MiniMaxModelMetadataDirectory.php  # API model discovery + transient cache
  Settings/
    MiniMaxSettings.php              # WP admin settings page (logic only)
templates/
  admin/
    settings-page.php                # <form> wrapper
    section-general.php              # Section description
    field-model.php                  # Model <select>
    field-temperature.php            # Temperature <input>
    field-max-tokens.php             # Max tokens <input>
    field-top-p.php                  # Top P <input>
    field-presence-penalty.php       # Presence penalty <input>
    field-frequency-penalty.php      # Frequency penalty <input>
```

Settings page: `options-general.php?page=minimax-settings`
Option key: `minimax_settings`

## Development

```bash
# Install dependencies
composer install

# Run tests
composer test

# Lint
composer phpcs

# Auto-fix lint issues
composer phpcbf

# Static analysis
composer phpstan

# Build release zip
composer release
```

The release script runs `composer install --no-dev --optimize-autoloader` so only the plugin's own classmap is in the vendor directory — the AI Client SDK is excluded entirely (it is provided by WordPress 7.0+ at runtime).

## License

GPL-2.0-or-later
