# CLAUDE.md

Guidance for Claude Code (claude.ai/code) working in this repository.

Every command and path below was checked against the tree on 2026-08-09. If one
turns out to be wrong, fix the code or fix this file — do not work around it
silently.

## What this plugin is

A provider for the WordPress AI Client: it teaches WordPress how to talk to
MiniMax, and nothing else. It does not add AI features to a site — the plugins
that consume the AI Client do that. This plugin's whole job is to describe
MiniMax accurately and forward requests to it.

**That makes accuracy the product.** `ModelMetadata` is not documentation; the
AI Client reads it to decide which provider can satisfy a request. Declaring a
capability or an option the API does not honour routes real work here and gets
it silently ignored — the failure mode is a wrong answer, not an error. Before
adding either, read MiniMax's reference and confirm the parameter is honoured
rather than merely accepted.

That rule has already been broken twice, in both directions:

- v1.4.0 declared `presencePenalty`, `frequencyPenalty` and `stopSequences`,
  which MiniMax documents as **ignored**. Callers who needed stop sequences
  were routed here and quietly given output that ignored them.
- Every version up to 1.4.0 omitted `chatHistory`, which MiniMax has always
  supported, so conversation requests were routed elsewhere.

## Commands

```bash
# Run all tests
composer test

# Run a single test file
./vendor/bin/phpunit tests/phpunit/Provider/ProviderTest.php

# Run a single test method
./vendor/bin/phpunit --filter test_provider_has_correct_base_url

# Lint (check only)
composer phpcs

# Lint (auto-fix)
composer phpcbf

# Static analysis
composer phpstan

# Build production release (strips dev deps, optimises autoloader)
composer install --no-dev --no-interaction --prefer-dist -o
```

## Reference Repositories

| Repository | Read it for |
|---|---|
| [wp-ai-client](https://github.com/WordPress/wp-ai-client) | The SDK. `CapabilityEnum` and `OptionEnum` define everything a provider may declare |
| [ai-provider-for-anthropic](https://github.com/WordPress/ai-provider-for-anthropic) | The minimal shape — text only, plus a custom authentication class |
| [ai-provider-for-openai](https://github.com/WordPress/ai-provider-for-openai) | A second modality: `OpenAiImageGenerationModel`, and `textToSpeechConversion` |
| [ai-provider-for-google](https://github.com/WordPress/ai-provider-for-google) | The fullest example — image, combined text-and-image, and a shared trait for aspect ratio |

[MiniMax API reference](https://platform.minimax.io/docs/api-reference/text-openai-api) —
the authority for what this plugin may declare. Check it before adding any
capability or option.

## Directory Structure

```
alamin-ai-provider-for-minimax/
├── alamin-ai-provider-for-minimax.php        # Entry point: constants, autoloader, boot
├── class-ai-provider-for-minimax.php   # Main class: every hook the plugin registers
├── includes/
│   ├── Availability/
│   │   └── ProviderAvailability.php
│   ├── Metadata/
│   │   └── ModelMetadataDirectory.php
│   ├── Models/
│   │   ├── ImageGenerationModel.php
│   │   └── TextGenerationModel.php
│   ├── Provider/
│   │   └── Provider.php
│   └── Settings/
│       └── Settings.php
├── templates/
│   └── admin/
│       ├── field-frequency-penalty.php
│       ├── field-max-tokens.php
│       ├── field-model.php
│       ├── field-presence-penalty.php
│       ├── field-temperature.php
│       ├── field-top-p.php
│       ├── section-general.php
│       └── settings-page.php
├── assets/
│   └── images/
│       └── minimax.svg
├── .wordpress-org/          # WP.org assets (icon, banner, screenshots)
├── composer.json
├── composer.lock
└── readme.txt
```

## Architecture

Dual-purpose codebase: works as a standalone Composer package **and** a WordPress plugin. Entry point is `alamin-ai-provider-for-minimax.php`; all PHP logic lives under `includes/`; admin UI templates live under `templates/admin/`.

### Class hierarchy (SDK pattern)

All classes extend from `wordpress/wp-ai-client` (provided by WordPress core on WP 7.0+):

```
AbstractApiProvider  (SDK)
  └── Provider              # provider ID "minimax", base URL https://api.minimax.io/v1

AbstractOpenAiCompatibleTextGenerationModel  (SDK)
  └── TextGenerationModel   # adds MiniMax-Provider header via createRequest()

ModelMetadataDirectoryInterface  (SDK)
  └── ModelMetadataDirectory  # fetches /v1/models, caches via WP transients
                                      # (1hr on success, 5min on failure),
                                      # falls back to hardcoded model list when API unavailable

ProviderAvailabilityInterface  (SDK)
  └── ProviderAvailability    # checks all 3 API key sources (see below)
```

`Settings` — standalone WP settings page (not in SDK hierarchy). Option key: `minimax_settings`. Settings page: `options-general.php?page=minimax-settings`.

### Bootstrap flow (WordPress)

1. `alamin-ai-provider-for-minimax.php` defines `MINIMAX_VERSION`, `MINIMAX_PLUGIN_FILE`, `MINIMAX_URL` and `MINIMAX_PATH`, then loads `vendor/autoload.php` — returning early rather than fataling if it is absent, which is the case for a git checkout with no `composer install`
2. `ai_provider_for_minimax()` returns the `AI_Provider_For_MiniMax` singleton and `init()` registers every hook
3. `init` hook (priority 5): `register_provider()` → registers `Provider` with `AiClient::defaultRegistry()`
4. `init` hook (priority 5): `init_settings()` → `Settings::init()` → wires up `admin_menu` and `admin_init`
5. `wpai_has_ai_credentials` / `wpai_pre_has_valid_credentials_check` → `declare_credentials()` / `declare_valid_credentials()`

Priority 5 is deliberate: anything generating text on `init` needs the provider
in the registry before it asks.

`class-ai-provider-for-minimax.php` holds the wiring and nothing else — every hook the plugin
registers is visible in one file. The work lives in `includes/`.

### API key resolution (priority order)

All three sources are checked by `ProviderAvailability::isConfigured()`, the `wpai_has_ai_credentials` filter, and `get_api_key()` in `ModelMetadataDirectory`:

1. `MINIMAX_API_KEY` environment variable
2. WordPress option `connectors_ai_minimax_api_key` (WP 7.0+ Connectors page)
3. WordPress option `wp_ai_client_credentials['minimax']['api_key']` (legacy)

**Important:** all three sources must be consistent across `ProviderAvailability`, `ModelMetadataDirectory::get_api_key()`, and the `wpai_has_ai_credentials` / `wpai_pre_has_valid_credentials_check` filter callbacks in the plugin bootstrap.

### Required AbstractApiProvider methods

```php
protected static function baseUrl(): string
protected static function createModel(ModelMetadata $model_metadata, ProviderMetadata $provider_metadata): ModelInterface
protected static function createProviderMetadata(): ProviderMetadata
protected static function createProviderAvailability(): ProviderAvailabilityInterface
protected static function createModelMetadataDirectory(): ModelMetadataDirectoryInterface
```

### Required ModelMetadataDirectoryInterface methods

```php
public function listModelMetadata(): array           // returns ModelMetadata[]
public function hasModelMetadata(string $model_id): bool
public function getModelMetadata(string $model_id): ModelMetadata  // throws InvalidArgumentException
```

### ModelMetadata constructor

```php
new ModelMetadata(
    $model_id,                                       // string
    $model_name,                                     // string
    $this->capabilities(),                           // list<CapabilityEnum>
    $this->supported_options()                       // list<SupportedOption>
)
```

Both lists are single private methods on `ModelMetadataDirectory`, shared by the
live-fetch path and the fallback path. Keep them that way — when they were
inlined twice, the two copies drifted.

### What this provider declares, and why

| Declared | Reason |
|---|---|
| `CapabilityEnum::textGeneration()` | The obvious one |
| `CapabilityEnum::chatHistory()` | The endpoint takes a `messages` array; multi-turn has always worked |
| `CapabilityEnum::imageGeneration()` | `image-01` only, from a separate endpoint — see below |
| `OptionEnum::temperature()` | `[0, 2]`, default 1 |
| `OptionEnum::maxTokens()` | Up to M3's 1,000,000-token context; M2.x caps at 204,800 |
| `OptionEnum::topP()` | `[0, 1]`; M3 defaults 0.95, M2.x 0.9 |
| `OptionEnum::systemInstruction()` | — |
| `OptionEnum::functionDeclarations()` | Sent as `tools` |

**Deliberately not declared**, because MiniMax documents them as ignored on the
OpenAI-compatible endpoint. Do not add them back:

`presencePenalty` · `frequencyPenalty` · `stopSequences` · `candidateCount`
(`n` accepts only 1) · `logprobs` · `topK`

### Provider-specific parameters

`thinking` and `service_tier` are MiniMax's own — the SDK's OpenAI-shaped
config has nowhere to carry them, so they are added in
`TextGenerationModel::prepareGenerateTextParams()` from saved settings.

Both are sent **only when they change something**: `thinking` only as
`disabled` (M3 reasons by default, M2.x cannot be stopped), and `service_tier`
only as `priority` (which bills at 1.5x). Anything else is at best redundant
and at worst refused.

Extend through the `minimax_generate_text_params` filter rather than adding
more special cases here.

### Image generation

`image-01` is the one model served from `POST /v1/image_generation`, and that
endpoint is **not** OpenAI-compatible. `ImageGenerationModel` extends the SDK's
OpenAI-compatible image base for the plumbing they share and overrides the four
places they differ:

| OpenAI | MiniMax |
|---|---|
| `POST /images/generations` | `POST /image_generation` |
| `size: "1024x1024"` | `aspect_ratio: "16:9"` |
| `response_format: b64_json` | `response_format: base64` |
| `data: [ { b64_json: … } ]` | `data: { image_base64: [ … ] }` |

The response shape is the awkward one — an object of lists where the SDK
expects a list of objects. `normalize_response()` reshapes it rather than
duplicating the candidate parser, and returns an already-OpenAI-shaped response
untouched in case MiniMax ever converges.

**MiniMax reports API errors with HTTP 200** and a `base_resp.status_code`, so
`throwIfNotSuccessful()` is overridden. Without it an insufficient-balance
error arrives as an empty result rather than an exception.

Model metadata is chosen per model in `build_model()`: `image-01` gets image
capabilities and image options, everything else gets text. The live
`/v1/models` response does not say what a model can do, so the id is the only
signal available.

Reach `seed`, `prompt_optimizer` and `subject_reference` — none of which the AI
Client's config models — through the `minimax_generate_image_params` filter.

### Capabilities not yet built

MiniMax offers three more modalities the SDK models. Each needs its own model
class; none is OpenAI-compatible.

| Capability | MiniMax models | Precedent to copy |
|---|---|---|
| `textToSpeechConversion()` | `speech-2.8-hd`, `speech-2.8-turbo`, `speech-2.6-hd`, `speech-2.6-turbo`, `speech-02-hd`, `speech-02-turbo` | `ai-provider-for-openai` |
| `videoGeneration()` | `MiniMax-H3` — async: create task, poll, retrieve | No WordPress precedent yet |
| `musicGeneration()` | `music-3.0` | No WordPress precedent yet |

Video is the awkward one: it is a task queue, not a request/response call, and
nothing in the AI Client models polling. Speech is the natural next addition,
being synchronous with an official implementation to read.

Voice cloning and voice design have no `CapabilityEnum`, so they cannot be
declared at all.

## Coding Standards

- WordPress Coding Standards (`phpcs.xml.dist`) — text domain `alamin-ai-provider-for-minimax`
- `declare(strict_types=1)` on every PHP file
- Namespace root: `MiniMax\MiniMaxAiProvider\`
- PHPStan at `level: max` (WP function stubs via `szepeviktor/phpstan-wordpress`)
- All output escaped: `esc_html()`, `esc_attr()`, `esc_url()`
- All strings wrapped: `__()` / `esc_html__()`; a `translators:` comment goes
  immediately above the `__()` it describes, not above an enclosing
  `wp_kses()` — make-pot associates by adjacency and silently drops it otherwise
- Templates set variables then `require` the template file — no logic inside template files

### Naming

Class names carry no vendor prefix — the namespace supplies it. A class is
named for what it *is*: `Provider`, `Settings`, `ModelMetadataDirectory`,
`TextGenerationModel`, `ProviderAvailability`. This diverges from the official
providers, which prefix everything (`AnthropicProvider`), and matches the rest
of this author's plugins.

### Comments

Comments explain **why**, and earn their place where the code looks wrong but is
not. A comment restating the line above it is noise; a comment recording that
MiniMax ignores `presence_penalty` saves the next person from re-adding it.
Where a change corrects a real defect, say what the defect was.

No AI attribution anywhere — not in comments, commits, or output.

### Commits

Conventional Commits: `type(scope): description`. Branch off `trunk`, never
commit to it directly. Merge with `gh pr merge --merge` — **never `--squash`**.
Stage explicit paths; never `git add -A`.

## Common Mistakes to Avoid

- **Do NOT declare a capability or option the API does not honour.** This is the
  mistake that matters most — see *What this plugin is*. `SupportedOption` is a
  routing promise, not a feature list. An earlier version of this file said
  "declare all options the API actually supports", which was read as "declare
  everything" and put three ignored options into the metadata.
- **Do NOT** add `wordpress/php-ai-client` to Composer production deps — the SDK is provided by WordPress core (WP 7.0+)
- **Do NOT** use `TextGenerationCapability` class — use `CapabilityEnum::textGeneration()`
- **Do NOT** omit the `connectors_ai_minimax_api_key` option check from any code that reads the API key
- **Do NOT** add a setting without wiring it into
  `TextGenerationModel::prepareGenerateTextParams()`. Every setting on the
  screen was stored and never read until 1.5.0; a control that changes nothing
  is worse than a missing one.
- **Do NOT** add a root-level PHP file without adding it to `phpcs.xml.dist`
  *and* `phpstan.neon.dist`. Both list files explicitly, so a new file is
  silently unchecked.
- **Do NOT** call WordPress functions in `includes/` without a
  `function_exists()` guard. The classes are documented as usable as a plain
  Composer package, and that is only true while the guards hold.
- **Do NOT** downgrade GitHub Actions versions — current baseline: `actions/checkout@v6`, `actions/cache@v5`, `actions/upload-artifact@v7`, `actions/download-artifact@v8`, `softprops/action-gh-release@v3`

## CI / Release Workflows

### Release pipeline (`.github/workflows/svn-deploy.yml`)

Five-job DAG triggered on any tag push:

```
meta ──┬── lint ─────┐
       └── phpstan ───┴── package ── deploy
```

| Job | Purpose | Blocks |
|---|---|---|
| `meta` | Validate tag == plugin header version; detect prerelease (`alpha`/`beta`/`rc`) | all |
| `lint` | Sensitive-file scan + PHP syntax lint + PHPCS | `package` |
| `phpstan` | PHPStan level max, 2G memory | `package` |
| `package` | `composer --no-dev --classmap-authoritative` + distignore rsync into `dist/<slug>/` + clean-dist guard + zip | `deploy` |
| `deploy` | 10up SVN (`BUILD_DIR: dist/<slug>`, `ASSETS_DIR: .wordpress-org`) + GH release with prerelease flag + job summary | — |

**Action versions (verified 2026-06):** `checkout@v6`, `cache@v5`, `upload-artifact@v7`, `download-artifact@v8`, `setup-php@v2`, `10up/action-wordpress-plugin-deploy@stable`, `softprops/action-gh-release@v3`

**Per-job Composer caches** — `composer-lint-*`, `composer-phpstan-*` (separate keys prevent cross-job cache poisoning).

**Distributable guard** — `package` job exits 1 if `.git`, `.github`, `tests`, `CLAUDE.md`, `phpcs*.xml*`, `.DS_Store` etc appear inside `dist/<slug>/` after the rsync step.

### Asset/readme sync (`.github/workflows/svn-readme-assets-update.yml`)

Triggered on push to `trunk` when `.wordpress-org/**` or `readme.txt` changes, or manually via `workflow_dispatch`. Uses `10up/action-wordpress-plugin-asset-update@stable`.

### CI (`.github/workflows/ci.yml`)

Three jobs run on push/PR to `trunk`/`main`: `lint` (PHPCS), `analyze` (PHPStan), `test` (PHPUnit across PHP 7.4–8.3 matrix with MySQL service). Cache keys are per-PHP-version (`composer-${{ runner.os }}-php${{ matrix.php }}-*`).

### Tagging a release

```bash
# 1. Bump version in plugin .php header and readme.txt Stable tag
# 2. Add changelog entry to readme.txt == Changelog ==
# 3. Commit and push to trunk
git add alamin-ai-provider-for-minimax.php readme.txt
git commit -m "chore: bump version to X.Y.Z"
git push

# 4. Tag and push — this triggers svn-deploy.yml
git tag X.Y.Z
git push origin X.Y.Z
```

## Key SDK Classes

The SDK is provided by **WordPress core** (WP 7.0+) — not bundled via Composer. These classes are autoloaded by WordPress at runtime:

| Class | Namespace |
|---|---|
| `ModelMetadata` | `WordPress\AiClient\Providers\Models\DTO` |
| `SupportedOption` | `WordPress\AiClient\Providers\Models\DTO` |
| `CapabilityEnum` | `WordPress\AiClient\Providers\Models\Enums` |
| `OptionEnum` | `WordPress\AiClient\Providers\Models\Enums` |
| `ModelMetadataDirectoryInterface` | `WordPress\AiClient\Providers\Contracts` |
| `ProviderAvailabilityInterface` | `WordPress\AiClient\Providers\Contracts` |
| `ProviderMetadata` | `WordPress\AiClient\Providers\DTO` |
| `AbstractApiProvider` | `WordPress\AiClient\Providers\ApiBasedImplementation` |
| `AbstractOpenAiCompatibleTextGenerationModel` | `WordPress\AiClient\Providers\OpenAiCompatibleImplementation` |
| `ApiKeyRequestAuthentication` | `WordPress\AiClient\Providers\Http\DTO` |
| `AiClient` | `WordPress\AiClient` |
