=== AI Provider for MiniMax ===
Donate link: https://alaminahamed.com/donate
Contributors: mralaminahamed
Tags: ai, minimax, llm, text generation, artificial intelligence
Requires at least: 6.7
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.3.0
License: GPL-2.0-or-later
License URI: https://spdx.org/licenses/GPL-2.0-or-later.html

MiniMax AI provider for WordPress AI Client. Access MiniMax-M2 and M1 series models for high-performance text generation.

== Description ==

This plugin integrates [MiniMax](https://www.minimax.io/) as an AI provider for the WordPress AI Client. It enables access to MiniMax's high-performance AI models through the OpenAI-compatible API.

This plugin is an independent, third-party integration and is not affiliated with, endorsed by, or sponsored by MiniMax. "MiniMax" is the name of the third-party service this plugin connects to.

**Features:**

* Seamless integration with the WordPress AI Client plugin
* Dynamic model discovery from the MiniMax API with hourly caching
* Support for MiniMax-M2 and M1 series models including highspeed variants
* Secure API key management via WordPress settings or environment variable
* Full generation parameter control: temperature, max tokens, top P, presence penalty, and frequency penalty
* Fallback to a hardcoded model list when the API is unavailable

**Supported Models (fallback list):**

MiniMax-M2.7, MiniMax-M2.7 Highspeed, MiniMax-M2.5, MiniMax-M2.5 Highspeed, MiniMax-M2.1, MiniMax-M2.1 Highspeed, MiniMax-M2, MiniMax-M1, MiniMax-Text-01

When an API key is configured, the live model list is fetched directly from the MiniMax API.

**Requirements:**

* WordPress 7.0 or higher (the AI Client SDK is built into WordPress core)
* A [MiniMax](https://www.minimax.io/) account and API key

**Settings:**

Go to **Settings > MiniMax** to configure:

* **API Key** — your MiniMax API key (or set the `MINIMAX_API_KEY` environment variable)
* **Default Model** — the model used when no explicit model is requested
* **Temperature** — controls output randomness (0.0–2.0, default 1.0)
* **Max Tokens** — maximum tokens in the generated response (default 2048)
* **Top P** — nucleus sampling threshold (0.0–1.0, default 1.0)
* **Presence Penalty** — penalises repeated topics (-2.0–2.0, default 0.0)
* **Frequency Penalty** — penalises repeated tokens (-2.0–2.0, default 0.0)

== Installation ==

= As a WordPress Plugin =

1. Download the plugin zip file
2. Go to **Plugins > Add New > Upload Plugin** in your WordPress admin
3. Upload the zip and click **Install Now**
4. Activate **AI Provider for MiniMax**
5. Go to **Settings > MiniMax** and enter your API key

**Note:** WordPress 7.0 includes the AI Client SDK natively — no additional AI Client plugin is required. If you are running an older WordPress version, you must install the WordPress AI Client plugin separately first.

= Manual Installation =

1. Upload the `alamin-ai-provider-for-minimax` folder to `/wp-content/plugins/`
2. Follow steps 4–5 above

= As a Composer Package =

`composer require mralaminahamed/ai-provider-for-minimax`

== Frequently Asked Questions ==

= What is MiniMax? =

MiniMax is an AI company that provides high-performance language models, including the MiniMax-M2 series known for their coding and text generation capabilities. Learn more at [minimax.io](https://www.minimax.io/).

= Do I need a separate AI Client plugin? =

Not on WordPress 7.0 or higher — the AI Client SDK is built into WordPress core. On older versions you will need the WordPress AI Client plugin.

= Where do I get an API key? =

Sign up at [platform.minimax.io](https://platform.minimax.io/user-center/basic-information/interface-key) and generate an API key from the interface key section.

= Is my API key stored securely? =

Your API key is stored in the WordPress options table using WordPress's standard options API. For higher security, set the `MINIMAX_API_KEY` environment variable on your server instead — this bypasses the database entirely.

= What happens if the MiniMax API is unreachable? =

The plugin falls back to a hardcoded list of 9 MiniMax models so the AI Client continues to function.

= What generation parameters are supported? =

Temperature, max tokens, top P, presence penalty, frequency penalty, stop sequences, system instruction, and function declarations are all declared as supported options.

== External Services ==

This plugin connects to the **MiniMax API** (`https://api.minimax.io/v1`) to:

1. Retrieve the list of available AI models (cached for 1 hour via WordPress transients)
2. Send text generation requests using your configured AI model

**Service:** MiniMax
**API endpoint:** `https://api.minimax.io/v1`
**When data is sent:** When generating AI text responses or refreshing the model list
**Data sent:** Your API key (via Authorization header) and the text prompt/conversation
**Terms of Service:** [platform.minimax.io/protocol/terms-of-service](https://platform.minimax.io/protocol/terms-of-service)
**Privacy Policy:** [platform.minimax.io/protocol/privacy-policy](https://platform.minimax.io/protocol/privacy-policy)

No data is sent to the MiniMax API until you enter an API key and a WordPress feature triggers a text generation request.

== Screenshots ==

1. The MiniMax settings page where you configure your API key, default model, and generation parameters.

== Changelog ==

= 1.3.0 =
* Added Top P, Presence Penalty, and Frequency Penalty settings fields to the admin settings page
* Declared full SupportedOptions coverage: temperature, top P, presence penalty, frequency penalty, stop sequences, system instruction, function declarations, and max tokens
* Extracted all admin HTML markup to `templates/admin/` for cleaner separation of logic and presentation
* Renamed plugin class directory from `src/` to `includes/` per WordPress plugin conventions
* Removed AI Client SDK from Composer production dependencies — WordPress 7.0+ provides it natively at runtime
* Requires at least: updated to reflect WordPress 7.0 native AI Client support

= 1.2.1 =
* Fixed connector showing as "Connected" before any API key is entered — provider availability now correctly checks for a configured API key

= 1.2.0 =
* Added provider logo displayed on the WordPress Connectors page
* Fixed false "no valid connector" warning on the AI admin page when API key is set via the Connectors page
* Expanded test suite from 33 to 44 tests covering all model IDs, highspeed variants, provider logo path, and settings edge cases

= 1.1.0 =
* Updated fallback model list to include all current MiniMax text generation models: M2.7, M2.7 Highspeed, M2.5, M2.5 Highspeed, M2.1, M2.1 Highspeed, M2, M1, and Text-01
* Added Domain Path header field to plugin file
* Improved plugin file header field ordering and alignment per WordPress.org standard
* Added file-level PHPDoc block to plugin bootstrap file
* Updated tested up to WordPress 7.0

= 1.0.0 =
* Initial release
* MiniMax provider registration with WordPress AI Client
* Dynamic model discovery with transient caching and fallback list
* Settings page for API key and default model configuration
* Support for `MINIMAX_API_KEY` environment variable

== Upgrade Notice ==

= 1.3.0 =
Adds Top P, Presence Penalty, and Frequency Penalty settings. No database changes or manual steps required. Requires WordPress 7.0 or higher.

= 1.2.0 =
Fixes a false "no valid connector" warning on the AI admin page. No database changes required.

= 1.1.0 =
Expanded fallback model list to 9 models. No database changes or manual steps required.

= 1.0.0 =
Initial release. No upgrade steps required.
