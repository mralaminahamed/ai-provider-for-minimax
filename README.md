<div align="center">

<img src=".wordpress-org/icon-256x256.png" alt="AI Provider for MiniMax icon" width="96" height="96">

# AI Provider for MiniMax — Developer Guide

**A MiniMax provider for the WordPress AI Client — register the models, hand over an API key, and any AI-Client consumer can call them.**

[![Version](https://img.shields.io/badge/version-1.5.0-21759b.svg)](https://github.com/mralaminahamed/ai-provider-for-minimax)
[![WordPress](https://img.shields.io/badge/WordPress-7.0%2B-21759b.svg?logo=wordpress&logoColor=white)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4.svg)](https://php.net/)
[![PHPStan](https://img.shields.io/badge/PHPStan-Level%20max-brightgreen.svg)](https://phpstan.org/)
[![Tests](https://img.shields.io/badge/tests-92-brightgreen.svg)](tests/)
[![License: GPL-2.0-or-later](https://img.shields.io/badge/License-GPL--2.0--or--later-green.svg)](LICENSE)

</div>

> This is the **contributor / technical** guide. For the public plugin listing — features, screenshots, changelog, upgrade notices — see [`readme.txt`](readme.txt).

| Requirement   | Minimum | Tested up to |
|---------------|---------|--------------|
| **WordPress** | 7.0     | 7.1          |
| **PHP**       | 7.4     | —            |

Current version **1.5.0** · License **GPL-2.0-or-later** · Tooling **Composer** · Delivered free on WordPress.org

> Not affiliated with MiniMax.

---

## What it is

The WordPress **AI Client** defines what a provider looks like; it does not ship every
provider. This plugin is the MiniMax implementation — it declares the models, reports whether
the site is configured to reach them, and performs the requests.

Nothing here is an interface of its own. Install it and MiniMax's models appear to whatever
already speaks AI Client, which is the point: the calling code does not learn a new API and
does not learn this plugin's name.

---

## Architecture

### PHP — `includes/` (PSR-4 `MiniMax\\MiniMaxAiProvider\\`)

| Dir             | Responsibility                                                        |
|-----------------|------------------------------------------------------------------------|
| `Provider/`     | The AI Client provider implementation — the entry point for requests   |
| `Models/`       | Model catalogue: identifiers and per-model capabilities                |
| `Metadata/`     | Provider metadata the AI Client displays and reasons about             |
| `Availability/` | Whether the provider is usable right now — chiefly, is a key present   |
| `Settings/`     | The API key, stored under `connectors_ai_minimax_api_key`                                    |

The split that matters is **Availability** against **Provider**: availability answers "can
this be used" without making a network call, so a consumer can enumerate providers cheaply and
only reach the network when it actually intends to.

### Repo map

```
alamin-ai-provider-for-minimax.php     Bootstrap: constants, autoloader, provider registration
includes/                  PHP (PSR-4 MiniMax\\MiniMaxAiProvider\\)
templates/                 Settings markup
assets/                    Admin CSS/JS
tools/                     Maintenance scripts
tests/phpunit/             PHPUnit (92 tests)
docs/                      Longer-form documentation
.wordpress-org/            Directory assets: icon, banners, screenshots
```

---

## Getting started

```bash
composer install       # dependencies + dev tooling
composer stubs:install # WordPress / AI Client stubs for static analysis
```

Add an API key under the plugin's settings screen. Without one the provider reports itself
unavailable rather than failing at call time.

---

## Testing

```bash
composer test                      # PHPUnit — 92 tests
composer test-f -- --filter SomeTest
```

---

## Code quality

```bash
composer phpcs         # WordPress Coding Standards
composer phpcbf        # auto-fix
composer phpstan       # static analysis, level max
composer analyze       # phpcs + phpstan
composer lint:review   # the stricter directory-review ruleset
```

---

## Internationalization

```bash
composer makepot
```

Text domain `alamin-ai-provider-for-minimax`. Translations live in `languages/`.

---

## Release

```bash
composer release
```

---

## Links

- [WordPress.org listing](https://wordpress.org/plugins/alamin-ai-provider-for-minimax/)
- [Public readme](readme.txt) — features, screenshots, changelog
- [`docs/`](docs/) — longer-form documentation

---

## Contributing · Security · License

Issues and pull requests are welcome. Please run `composer analyze` and `composer test`
before opening one.

An API key is a credential: report security issues privately rather than in a public issue.

GPL-2.0-or-later. See [`LICENSE`](LICENSE).
