# Usage

## With WordPress (automatic)

The provider registers itself on the `init` hook. Once the plugin is active and an API key is set, no further wiring is needed.

```php
use WordPress\AiClient\AiClient;

$result = AiClient::prompt('Explain quantum computing')
    ->usingProvider('minimax')
    ->generateTextResult();

echo $result->toText();
```

## As a standalone Composer package

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

## API key

Set the key on **Settings > MiniMax**, on the WordPress 7.0+ **Settings > Connectors** screen, or via environment variable. The environment variable takes priority over the database setting:

```bash
export MINIMAX_API_KEY=your-api-key
```

Get a key at [platform.minimax.io/user-center/basic-information/interface-key](https://platform.minimax.io/user-center/basic-information/interface-key). Full resolution order is in [ARCHITECTURE.md](ARCHITECTURE.md#credential-resolution).

## Generation options

Defaults are configurable on the settings page and can be overridden per request through the AI Client prompt builder.

| Option | Range | Default |
|---|---|---|
| Default Model | any available model | `MiniMax-M3` |
| Temperature | 0.0–2.0 | 0.7 |
| Max Tokens | 1–200,000 | 4096 |
| Top P | 0.0–1.0 | 1.0 |
| Presence Penalty | -2.0–2.0 | 0.0 |
| Frequency Penalty | -2.0–2.0 | 0.0 |

## Credential filters

So the WordPress AI admin page does not show a false "no valid connector" warning when the key is stored outside the standard flat option, the plugin hooks two filters:

- `wpai_has_ai_credentials` — returns `true` when `MiniMaxSettings::has_api_key()` finds a key (env var, Connectors option, or legacy credentials option).
- `wpai_pre_has_valid_credentials_check` — short-circuits the validity check to `true` when a key is confirmed present.

Both are thin wrappers over the credential resolution described in [ARCHITECTURE.md](ARCHITECTURE.md#credential-resolution).
