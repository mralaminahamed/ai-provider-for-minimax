# Architecture

How the plugin is put together and how a request flows through it.

## Provider identity

| | |
|---|---|
| Provider ID | `minimax` |
| Base URL | `https://api.minimax.io/v1` |
| API style | OpenAI-compatible (`/chat/completions`, `/models`) |
| Settings page | `options-general.php?page=minimax-settings` |
| Option key | `minimax_settings` |

## File layout

```
includes/
  Provider/
    MiniMaxProvider.php                # Registers provider ID "minimax", base URL
    MiniMaxTextGenerationModel.php      # OpenAI-compatible text generation
  Metadata/
    MiniMaxModelMetadataDirectory.php   # Live model discovery + transient cache + fallback list
  Settings/
    MiniMaxSettings.php                # WP admin settings page (logic only)
templates/
  admin/
    settings-page.php                  # <form> wrapper
    section-general.php                # Section description
    field-model.php                    # Model <select>
    field-temperature.php              # Temperature <input>
    field-max-tokens.php               # Max tokens <input>
    field-top-p.php                    # Top P <input>
    field-presence-penalty.php         # Presence penalty <input>
    field-frequency-penalty.php        # Frequency penalty <input>
```

Settings logic lives in PHP; all markup lives in `templates/admin/` so the two never mix.

## Request flow

```
AiClient::prompt(…)->usingProvider('minimax')->generateTextResult()
  └─ MiniMaxProvider                    resolves provider + base URL + credentials
       └─ MiniMaxTextGenerationModel    POST {base}/chat/completions (OpenAI-compatible)
            └─ returns GenerateTextResult
```

Model discovery is a separate path:

```
MiniMaxModelMetadataDirectory::listModelMetadata()
  └─ GET {base}/models   (when an API key is present)
       ├─ success → cache in transient (1 h) and return live list
       └─ failure → cache empty (5 min) and return the built-in fallback list
```

See [MODELS.md](MODELS.md) for the discovery/caching details and the full fallback catalogue.

## Credential resolution

The API key is resolved in priority order (first hit wins):

1. `MINIMAX_API_KEY` environment variable
2. `connectors_ai_minimax_api_key` option (WordPress 7.0+ Connectors screen)
3. `wp_ai_client_credentials['minimax']['api_key']` (legacy AI Client credentials option)

`MiniMaxSettings::has_api_key()` centralises this check; it is reused by the credential filters described in [USAGE.md](USAGE.md).

## Runtime dependency: the AI Client SDK

The plugin builds on the [WordPress PHP AI Client](https://github.com/WordPress/php-ai-client) (`WordPress\AiClient\*`). WordPress 7.0+ bundles this SDK in core, so the shipped plugin **excludes** its own copy via a Composer `replace` — the classes are provided at runtime by core. See [DEVELOPMENT.md](DEVELOPMENT.md) for how the SDK is still made available to PHPStan and PHPUnit without shipping it.
