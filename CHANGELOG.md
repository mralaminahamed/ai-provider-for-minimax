# Changelog

## [1.6.0] - 2026-09-07

### Added

- Text to speech. MiniMax's eight `speech-*` models are now offered as text-to-speech models, served from `POST /v1/t2a_v2` — an endpoint that is not OpenAI-shaped in any respect, so the request is built and the response parsed here rather than inherited. Choose a voice through `outputSpeechVoice`, a format through `outputMimeType` (MP3, WAV, FLAC or Opus), and inline audio or an expiring URL through `outputFileType`. Speed, volume, pitch, emotion, sample rate, bitrate and language boost are reachable through custom options or the new `minimax_convert_text_to_speech_params` filter. The official WordPress AI provider for OpenAI declares this capability but does not implement it.
- Only the synchronous endpoint is implemented, and its 10,000-character limit is refused before the request rather than discovered as MiniMax's "invalid input parameters". The asynchronous task API and the WebSocket stream both need machinery this plugin does not have.
- The plugin now autoloads its own classes from `includes/autoload.php` and ships no `vendor/` directory. There was never a runtime dependency to justify Composer here — `composer.json` requires `php` and `ext-json` and nothing else — and the three official WordPress AI provider plugins do the same.

- A **Test connection** button on the settings page. The page reported "MiniMax is connected" as soon as a key was present anywhere, without ever asking MiniMax about it — a key that had been revoked, mistyped or copied from the wrong account read as connected, and the first sign of trouble was a generation failing somewhere else. The button asks `GET /v1/models`, which requires authentication and generates nothing, so verifying costs neither tokens nor money. A network failure is reported as "could not be checked" rather than as a bad key, since a firewalled site has learned nothing about its credentials. The verdict is cached for five minutes and dropped whenever a key is changed.
- An uninstall handler. Deleting the plugin now removes the `minimax_settings` option and the `minimax_models_cache` transient, which it used to leave behind for good. Credentials are deliberately left alone: `connectors_ai_minimax_api_key` is written by the WordPress Connectors screen and `wp_ai_client_credentials` is shared by every AI provider on the site, so removing either would delete a row this plugin did not create — and in the second case, other providers' keys along with its own.
### Changed

- The settings page no longer claims the provider is connected on the strength of a key existing. It says a key is configured, notes that this is not the same as one that works, and offers to check.
- The default-model dropdown lists text models only. It is labelled "for text generation" and listed everything the directory knew about, so `image-01` was already offered as a text model; the speech models would have added eight more.
- The release build no longer installs and packages a production `vendor/` directory, because nothing under it ships any more.

- Declared as tested against WordPress 7.1.
### Fixed

- The chosen default model now decides which model the AI Client reaches for. The setting has been stored since 1.0.0 and never read: picking one wrote a row to `wp_options` and changed no request that followed. The catalogue is now returned with that model first, which is what the AI Client reads — it keeps matching models in catalogue order and, when the caller names neither a model nor a preference, uses the first. Nothing is filtered, so a caller who names another model still gets it.
- A copy installed from git no longer activates and silently does nothing. It used to look for Composer's autoloader, find no `vendor/`, and return without registering a provider or saying why.
- Refreshed the translation template, which still carried the old provider description.
- Generation requests are now addressed to MiniMax. The AI Client hands the plugin a path relative to the provider's base URL and expects an absolute URL back; the plugin returned the path unchanged, so every text and image request named no host and could not be sent. The three official WordPress AI providers all resolve the path through their provider's `url()`, and this now does the same.

## [1.5.0] - 2026-08-09

### Added

- Saved settings are now applied to generation requests. Temperature, max tokens and top_p had been stored since 1.0.0 and never read — nothing outside the settings class touched `minimax_settings`. A caller's own value still wins; the saved values fill in what was left unset.
- Thinking mode. MiniMax-M3 can be told to answer without reasoning first, which is faster and cheaper. The M2 series always reasons and ignores the setting, so it is only sent when set to `disabled`.
- Service tier, for opting in to priority routing at 1.5x the standard cost.
- `minimax_generate_text_params` filter, for anything this plugin does not model.
- Image input on MiniMax-M3. Vision has worked since the first release — the AI Client turns an image message part into an `image_url` content part — but was never declared, so the AI Client routed image prompts elsewhere. Declared per model: the M2 series is text only.
- Custom options. Arbitrary passthrough parameters, which the SDK's base class already merged into the request body but which no caller could reach, because the option was not declared. All three official WordPress AI providers offer this.
- Image generation. `image-01` is now offered as an image model, served from MiniMax's own `/v1/image_generation` endpoint, which is not OpenAI-compatible: different path, `aspect_ratio` instead of `size`, `base64` instead of `b64_json`, and a response shaped as an object of lists rather than a list of objects. Aspect ratio, orientation, candidate count and URL-vs-inline output are supported; `seed`, `prompt_optimizer` and `subject_reference` are reachable through the new `minimax_generate_image_params` filter. MiniMax reports API errors with HTTP 200 and a `base_resp` object, so those are turned into exceptions rather than empty results.
- Chat history. The models are now declared as supporting multi-turn conversations, not just single prompts. They always could (the OpenAI-compatible endpoint takes a `messages` array and the AI Client already sends one), but the capability was never declared and the AI Client routes on the declaration, so conversation requests went to other providers. Every official WordPress AI provider declares this.

### Changed

- PHP namespace is now `MiniMax\MiniMaxAiProvider\` (was `AlAminAhamed\MiniMaxAiProvider\`).
- Class names dropped their `MiniMax` prefix, which the namespace already carries: `Provider`, `Settings`, `ModelMetadataDirectory`, `TextGenerationModel`, `ProviderAvailability`.
- Max tokens accepts up to 1,000,000, matching MiniMax-M3's context window. The previous ceiling of 200,000 was the M2 limit.

- **Restructured the bootstrap.** The plugin file is now an entry point — constants, autoloader, boot — and all wiring moved into an `AI_Provider_For_MiniMax` singleton in `class-ai-provider-for-minimax.php`, so every hook the plugin registers is visible in one file. Matches the layout used across this author's other plugins.
- Added `MINIMAX_VERSION`, `MINIMAX_URL` and `MINIMAX_PATH` constants; only `MINIMAX_PLUGIN_FILE` existed before.

### Removed

- Presence penalty and frequency penalty settings. MiniMax documents both as ignored on its OpenAI-compatible endpoint, so the controls could not affect anything. Saved values are left in the database untouched.
- `presencePenalty`, `frequencyPenalty` and `stopSequences` are no longer declared as supported model options. The AI Client reads those declarations to decide which model can satisfy a request, so declaring them routed callers here and had their request quietly ignored.

## [1.4.0] - 2026-07-21

### Added

- Connection status on the settings page — indicates whether a MiniMax API key is configured, with a link to the Connectors screen to add one.
- A "Connectors" quick link on the Plugins screen, next to the existing "Settings" link.

### Changed

- Synced the built-in fallback model list to the official MiniMax catalogue — added `MiniMax-M3` and removed the retired `MiniMax-M1` and `MiniMax-Text-01` (8 models total).
- A default model (`MiniMax-M3`) is now selected out of the box instead of an empty choice, so generation works before the live model list loads.

### Fixed

- Autoloader collision with WordPress core's bundled AI Client. When the plugin's copy of `wordpress/php-ai-client` registered the `WordPress\AiClient\` namespace before core did, any request that triggered the AI Client hit a `TypeError`. The package is now excluded from the shipped plugin via a Composer `replace`; WordPress core provides those classes at runtime.
- Restored PHPStan, PHPUnit, and CI, which the change above had broken by removing `WordPress\AiClient\*` from `vendor/`. Those classes are now loaded for static analysis and the test suite only, from a dev-only side install (`tools/ai-client/`), and are never registered on the plugin's runtime autoloader.

## [1.2.1] - 2026-05-21

### Fixed

- Connector no longer shows as "Connected" on the WordPress Connectors page before any API key is entered — replaced `ListModelsApiBasedProviderAvailability` with a custom availability class that checks for an actual API key (env var, WP Connectors option, or legacy credentials option)

## [1.2.0] - 2026-05-21

### Added

- Provider logo SVG displayed on the WordPress Connectors page

### Fixed

- Declared credential availability via `wpai_has_ai_credentials` and `wpai_pre_has_valid_credentials_check` filters so the WordPress AI admin page no longer shows a false "no valid connector" warning when an API key is configured via the Connectors page

### Changed

- Expanded test suite from 33 to 44 tests covering full model list, provider logo path, highspeed variants, settings edge cases, and all 9 model IDs

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
