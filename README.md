<div align="center">

<img src="assets/images/minimax.svg" alt="AI Provider for MiniMax logo" width="96" height="96">

# AI Provider for MiniMax

**MiniMax's frontier text models, inside WordPress.**
An independent [MiniMax](https://www.minimax.io/) provider for the WordPress AI Client — brings the MiniMax M3 flagship and the full M2 series to every AI-enabled plugin on your site.

[![WordPress.org version](https://img.shields.io/wordpress/plugin/v/alamin-ai-provider-for-minimax?label=WordPress.org&logo=wordpress&logoColor=white&color=21759B)](https://wordpress.org/plugins/alamin-ai-provider-for-minimax/)
[![Downloads](https://img.shields.io/wordpress/plugin/dt/alamin-ai-provider-for-minimax?label=Downloads&color=21759B)](https://wordpress.org/plugins/alamin-ai-provider-for-minimax/advanced/)
[![Tested up to](https://img.shields.io/wordpress/plugin/tested/alamin-ai-provider-for-minimax?label=Tested&logo=wordpress&logoColor=white)](https://wordpress.org/plugins/alamin-ai-provider-for-minimax/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![License](https://img.shields.io/badge/License-GPL--2.0--or--later-blue.svg)](LICENSE)
[![PRs welcome](https://img.shields.io/badge/PRs-welcome-brightgreen.svg)](https://github.com/mralaminahamed/ai-provider-for-minimax/pulls)

</div>

> Not affiliated with, endorsed by, or sponsored by MiniMax.

---

## What it does

WordPress 7.0 ships a built-in **AI Client** SDK: plugins ask it to "generate text from this prompt" without caring which AI service answers. This plugin registers **MiniMax** as one of those services, so its high-performance, cost-effective text models — the **M2 and M3 series** — become available to every AI-enabled plugin on your site.

Activate it, paste your MiniMax key, and you are generating content through frontier-quality models with strong multilingual support — no code required.

## Features

**Frontier models, one key**
- **MiniMax M3** flagship (long-context) plus the full **M2 series** — M2.7, M2.5, M2.1, and M2, each with an optional **Highspeed** variant tuned for lower latency (8 models)
- **Live model discovery** from the MiniMax API (cached for one hour), with a built-in 8-model fallback so generation keeps working if the API is briefly unreachable
- **MiniMax-M3** preselected as the default model out of the box

**Native WordPress integration**
- Registers as a first-class provider for the WordPress 7.0+ AI Client — any AI-enabled plugin uses it with **zero extra wiring**
- Enter your key on **Settings → MiniMax**, the core **Settings → Connectors** screen, or via the `MINIMAX_API_KEY` environment variable
- **Connection-status indicator** on the settings page and the provider logo on the Connectors screen
- Standard generation controls: temperature, max tokens, top&nbsp;P, and presence/frequency penalties

**Built to stay out of the way**
- **OpenAI-compatible** under the hood — text generation, system instructions, stop sequences, and function declarations
- Ships as both a **WordPress plugin** and a **Composer package** for standalone PHP use
- **No SDK bloat** — WordPress core provides the AI Client at runtime, and the plugin excludes its own copy to stay collision-safe

## Requirements

- PHP **7.4+**
- WordPress **7.0+** (the AI Client SDK ships in core)
  - On older WordPress, install the [WordPress AI Client](https://wordpress.org/plugins/wp-ai-client/) plugin separately

## Installation

| Method | How |
|---|---|
| **WordPress.org** | Search "AI Provider for MiniMax" under **Plugins → Add New**, then install and activate |
| **Upload** | Download the zip and upload it via **Plugins → Add New → Upload Plugin** |
| **Composer** | `composer require mralaminahamed/ai-provider-for-minimax` |

## Quick start

1. **Activate** the plugin.
2. Open **Settings → MiniMax** (or the core **Settings → Connectors** screen) and paste your API key. Get one at [platform.minimax.io/user-center/basic-information/interface-key](https://platform.minimax.io/user-center/basic-information/interface-key).
3. Any AI-enabled plugin can now generate text through the `minimax` provider:

```php
use WordPress\AiClient\AiClient;

echo AiClient::prompt('Explain quantum computing')
    ->usingProvider('minimax')
    ->generateTextResult()
    ->toText();
```

See [docs/USAGE.md](docs/USAGE.md) for the standalone-Composer setup and per-request options.

## Configuration

Configure defaults on **Settings → MiniMax** (option key `minimax_settings`):

| Setting | Range | Default |
|---|---|---|
| Default Model | any available model | `MiniMax-M3` |
| Temperature | 0.0–2.0 | 0.7 |
| Max Tokens | 1–1,000,000 | 4096 |
| Top P | 0.0–1.0 | 1.0 |
| Thinking | adaptive / disabled | adaptive |
| Service Tier | standard / priority | standard |

The `MINIMAX_API_KEY` environment variable takes priority over the stored key. Full resolution order is in [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md#credential-resolution).

## Supported models

When a key is configured, the live model list comes straight from the MiniMax API, so you always see the latest models. If the API is unavailable, an **8-model fallback** keeps everything working — the **MiniMax-M3** flagship plus the **M2 series** (M2.7, M2.5, M2.1, and M2), each with an optional Highspeed variant.

The complete, ID-by-ID catalogue and guidance on which model to pick are in [docs/MODELS.md](docs/MODELS.md).

## How it works

WordPress 7.0's AI Client is a provider-agnostic layer. Install one or more **provider plugins** — like this one — and any plugin built on the AI Client can generate content through whichever provider is active, switchable from the Connectors screen with no per-plugin reconfiguration. This plugin handles the MiniMax side: credential resolution, live model discovery, and the OpenAI-compatible API calls. Details in [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md).

## Documentation

| Doc | What's in it |
|---|---|
| [Usage](docs/USAGE.md) | WordPress + standalone Composer examples, API key resolution, generation options, credential filters |
| [Models](docs/MODELS.md) | How model discovery and caching work, and the full 8-model fallback catalogue |
| [Architecture](docs/ARCHITECTURE.md) | Provider identity, file layout, request flow, runtime SDK dependency |
| [Development](docs/DEVELOPMENT.md) | Build/test/lint/analysis scripts, the AI Client `replace` guard and dev-only stub install, CI and release |

Release history: [CHANGELOG.md](CHANGELOG.md).

## Development

```bash
composer install
composer test        # PHPUnit
composer phpcs       # WordPress Coding Standards
composer phpstan     # static analysis
composer release     # build the distributable zip
```

Full workflow — including the AI Client `replace` guard and the dev-only stub install that keeps PHPStan and PHPUnit green — is in [docs/DEVELOPMENT.md](docs/DEVELOPMENT.md).

## Contributing

Issues and pull requests are welcome:

- [Open a new issue](https://github.com/mralaminahamed/ai-provider-for-minimax/issues/new) to report a bug or request a feature
- [Submit a pull request](https://github.com/mralaminahamed/ai-provider-for-minimax/pulls) — see [docs/DEVELOPMENT.md](docs/DEVELOPMENT.md) for the local build and test workflow

## Support

If this plugin is useful, **star the repo** and leave a [review on WordPress.org](https://wordpress.org/support/plugin/alamin-ai-provider-for-minimax/reviews/). Questions and bug reports go in the [issue tracker](https://github.com/mralaminahamed/ai-provider-for-minimax/issues).

## License

[GPL-2.0-or-later](LICENSE) © Al Amin Ahamed
