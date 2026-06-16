=== AI Provider for MiniMax ===
Contributors:      mralaminahamed
Tags:              ai, minimax, llm, connector, artificial-intelligence
Requires at least: 7.0
Tested up to:      7.0
Stable tag:        1.3.0
Requires PHP:      7.4
License:           GPL-2.0-or-later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

MiniMax provider for the WordPress AI Client.

== Description ==

This plugin provides [MiniMax](https://www.minimax.io/) integration for the WordPress AI Client. It enables WordPress sites to use MiniMax's high-performance AI models for text generation and other AI capabilities through an OpenAI-compatible API.

This plugin is an independent, third-party integration and is not affiliated with, endorsed by, or sponsored by MiniMax.

**Features:**

* Text generation with MiniMax M2 and M1 series models
* Automatic model discovery from the MiniMax API with hourly caching
* Fallback to a hardcoded model list when the API is unavailable
* Full generation parameter control: temperature, max tokens, top P, presence penalty, frequency penalty, stop sequences, system instruction, and function declarations
* Settings page for default model and generation parameters
* API key configured via **Settings > Connectors** or the `MINIMAX_API_KEY` environment variable

**Supported Models (fallback list):**

MiniMax-M2.7, MiniMax-M2.7 Highspeed, MiniMax-M2.5, MiniMax-M2.5 Highspeed, MiniMax-M2.1, MiniMax-M2.1 Highspeed, MiniMax-M2, MiniMax-M1, MiniMax-Text-01

When an API key is configured, the live model list is fetched directly from the MiniMax API.

**Requirements:**

* PHP 7.4 or higher
* WordPress 7.0 or higher

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/alamin-ai-provider-for-minimax/`.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Enter your MiniMax API key via **Settings > Connectors**.
4. Go to **Settings > MiniMax** to configure the default model and generation parameters.

== Frequently Asked Questions ==

= How do I get a MiniMax API key? =

Visit [platform.minimax.io](https://platform.minimax.io/user-center/basic-information/interface-key) to create an account and generate an API key from the interface key section.

= Where do I enter my API key? =

Go to **Settings > Connectors** in your WordPress admin and enter the key there. Alternatively, set the `MINIMAX_API_KEY` environment variable on your server.

= Does this plugin work without the PHP AI Client? =

WordPress 7.0 and higher include the AI Client SDK natively — no additional plugin is required.

= What happens if the MiniMax API is unreachable? =

The plugin falls back to a hardcoded list of 9 MiniMax models so text generation continues to work.

= What generation parameters are supported? =

Temperature, max tokens, top P, presence penalty, frequency penalty, stop sequences, system instruction, and function declarations.

== External Services ==

This plugin connects to the **MiniMax API** (`https://api.minimax.io/v1`) to:

1. Retrieve the list of available AI models (cached for 1 hour via WordPress transients)
2. Send text generation requests using your configured model

**Service:** MiniMax
**API endpoint:** `https://api.minimax.io/v1`
**When data is sent:** When generating AI text or refreshing the model list
**Data sent:** Your API key (via Authorization header) and the text prompt/conversation
**Terms of Service:** [platform.minimax.io/protocol/terms-of-service](https://platform.minimax.io/protocol/terms-of-service)
**Privacy Policy:** [platform.minimax.io/protocol/privacy-policy](https://platform.minimax.io/protocol/privacy-policy)

No data is sent to the MiniMax API until you enter an API key and a WordPress feature triggers a text generation request.

== Screenshots ==

1. Settings > MiniMax screen showing default model selection and generation parameter configuration.
2. Settings > Connectors screen where you enter your MiniMax API key.

== Changelog ==

= 1.3.0 - 2026-06-16 =

**Added**
- Top P, Presence Penalty, and Frequency Penalty settings fields on the admin settings page.
- Full SupportedOptions coverage: temperature, top P, presence penalty, frequency penalty, stop sequences, system instruction, function declarations, and max tokens.

**Changed**
- Extracted all admin HTML markup to `templates/admin/` for cleaner separation of logic and presentation.
- Renamed plugin class directory from `src/` to `includes/` per WordPress plugin conventions.
- Removed AI Client SDK from Composer production dependencies — WordPress 7.0+ provides it natively at runtime.
- Updated `Requires at least` to 7.0.

= 1.2.1 - 2026-05-01 =

**Fixed**
- Connector showing as "Connected" before any API key is entered — provider availability now correctly checks for a configured API key.

= 1.2.0 - 2026-04-01 =

**Added**
- Provider logo displayed on the WordPress Connectors page.
- Expanded test suite from 33 to 44 tests covering all model IDs, highspeed variants, provider logo path, and settings edge cases.

**Fixed**
- False "no valid connector" warning on the AI admin page when API key is set via the Connectors page.

= 1.1.0 - 2026-03-01 =

**Added**
- Updated fallback model list to include all current MiniMax text generation models: M2.7, M2.7 Highspeed, M2.5, M2.5 Highspeed, M2.1, M2.1 Highspeed, M2, M1, and Text-01.
- Domain Path header field to plugin file.

**Changed**
- Improved plugin file header field ordering and alignment per WordPress.org standard.
- Added file-level PHPDoc block to plugin bootstrap file.
- Updated tested up to WordPress 7.0.

= 1.0.0 - 2026-01-01 =

* Initial release.
* MiniMax provider registration with WordPress AI Client.
* Dynamic model discovery with transient caching and fallback list.
* Settings page for default model configuration.
* Support for `MINIMAX_API_KEY` environment variable.

== Upgrade Notice ==

= 1.3.0 =
Adds Top P, Presence Penalty, and Frequency Penalty settings. No database changes required. Requires WordPress 7.0 or higher.

= 1.2.1 =
Fixes connector showing "Connected" before an API key is entered. No database changes required.

= 1.2.0 =
Fixes a false "no valid connector" warning on the AI admin page. No database changes required.
