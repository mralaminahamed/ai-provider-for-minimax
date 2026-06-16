=== AI Provider for MiniMax ===
Contributors:      mralaminahamed
Tags:              ai, minimax, llm, connector, artificial-intelligence
Requires at least: 7.0
Tested up to:      7.0
Stable tag:        1.3.1
Requires PHP:      7.4
License:           GPL-2.0-or-later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

MiniMax provider for the WordPress AI Client. Access MiniMax M2 and M1 series models for high-performance text generation.

== Description ==

This plugin provides [MiniMax](https://www.minimax.io/) integration for the WordPress AI Client. It enables WordPress sites to use MiniMax's high-performance large language models for text generation, content creation, and other AI capabilities through an OpenAI-compatible API.

This plugin is an independent, third-party integration and is not affiliated with, endorsed by, or sponsored by MiniMax.

= Why MiniMax? =

MiniMax is an AI company known for its high-performance, cost-effective language models. The **MiniMax M2 series** delivers frontier-level text generation quality — competitive with top models from OpenAI and Anthropic — while offering attractive pricing for high-volume WordPress use cases.

MiniMax models are particularly strong at:

* **Long-context tasks** — handling large documents, long conversations, and extended content generation
* **Instruction following** — precise, structured output generation suitable for automated pipelines
* **Multilingual content** — strong performance in English, Chinese, and other languages
* **Creative writing** — rich, nuanced prose generation for blogs, marketing copy, and storytelling

= Features =

* **MiniMax M2 and M1 series models** — including Highspeed variants optimised for faster responses
* **Automatic model discovery** — live model list fetched from the MiniMax API and cached hourly; falls back to a hardcoded list when offline
* **Full parameter control** — temperature, max tokens, top P, presence penalty, frequency penalty, stop sequences, system instruction, and function declarations
* **Settings page** — configure default model and generation parameters without touching code
* **API key via Connectors** — enter your key once in **Settings > Connectors**; all AI-enabled plugins share it automatically
* **Environment variable support** — `MINIMAX_API_KEY` for server-level configuration, bypassing the database entirely
* **OpenAI-compatible API** — uses the standard OpenAI API protocol for broad compatibility
* **Highspeed variants** — M2.7 Highspeed, M2.5 Highspeed, and M2.1 Highspeed for latency-sensitive applications

= What Can You Do With AI in WordPress? =

Once this provider is configured, any WordPress plugin or theme that integrates with the WordPress AI Client can use it:

* **Block editor (Gutenberg)** — AI writing assistance, rephrasing, summarising, and expanding content
* **WooCommerce** — generate product descriptions, SEO meta titles, customer review summaries, and category descriptions
* **SEO plugins** — generate meta descriptions, focus keyphrases, and Open Graph content
* **Customer support** — power AI chatbots and automate FAQ responses
* **Translation and localisation** — translate and adapt content for English, Chinese, and other markets
* **Content marketing** — generate blog post drafts, social media copy, and email newsletters
* **Image alt text** — generate accessible alt attributes for media library images

= Supported Models =

When an API key is configured, the live model list is fetched from the MiniMax API. The built-in fallback list includes:

* **MiniMax-M2.7** — latest generation, highest capability
* **MiniMax-M2.7 Highspeed** — M2.7 performance at lower latency
* **MiniMax-M2.5** — balanced performance and cost
* **MiniMax-M2.5 Highspeed** — M2.5 with faster response times
* **MiniMax-M2.1** — cost-effective mid-tier model
* **MiniMax-M2.1 Highspeed** — M2.1 with reduced latency
* **MiniMax-M2** — previous generation, widely supported
* **MiniMax-M1** — foundation model
* **MiniMax-Text-01** — specialised text generation model

= Requirements =

* PHP 7.4 or higher
* WordPress 7.0 or higher
* A [MiniMax](https://www.minimax.io/) account and API key

= How the WordPress AI Provider System Works =

WordPress 7.0 introduced a built-in AI Client SDK. Plugins and themes call a standard API (e.g. "generate text from this prompt") without knowing which AI provider is active. Provider plugins like this one register themselves with WordPress and handle the actual API calls.

This means:

1. Install this plugin → MiniMax is registered as an AI provider
2. Enter your API key in **Settings > Connectors**
3. Every AI-enabled plugin on your site can now use MiniMax automatically

You can also install multiple provider plugins and switch between them from the Connectors screen — no re-configuration of individual plugins needed.

= Settings =

Go to **Settings > MiniMax** to configure:

* **Default Model** — the model used when no explicit model is requested by a plugin
* **Temperature** — controls output randomness (0.0–2.0, default 0.7)
* **Max Tokens** — maximum tokens in the response (1–200,000, default 4096)
* **Top P** — nucleus sampling threshold (0.0–1.0, default 1.0)
* **Presence Penalty** — penalises repeated topics (-2.0–2.0, default 0.0)
* **Frequency Penalty** — penalises repeated tokens (-2.0–2.0, default 0.0)

= For Developers =

This plugin follows the official WordPress AI Provider pattern and is fully compatible with any plugin built on the WordPress AI Client SDK.

**Supported SupportedOptions:** `temperature`, `maxTokens`, `topP`, `presencePenalty`, `frequencyPenalty`, `stopSequences`, `systemInstruction`, `functionDeclarations`

**Provider ID:** `minimax`

**Base URL:** `https://api.minimax.io/v1`

**API key resolution order:**
1. `MINIMAX_API_KEY` environment variable
2. WordPress option `connectors_ai_minimax_api_key` (Settings > Connectors)
3. WordPress option `wp_ai_client_credentials['minimax']['api_key']` (legacy)

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/alamin-ai-provider-for-minimax/`.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Go to **Settings > Connectors** and enter your MiniMax API key.
4. Go to **Settings > MiniMax** to choose a default model and tune generation parameters.
5. Install any AI-enabled plugin (e.g. the [AI Experiments](https://wordpress.org/plugins/ai/) plugin) to start using AI features.

= Getting Your API Key =

1. Sign up at [platform.minimax.io](https://platform.minimax.io)
2. Go to the [Interface Key section](https://platform.minimax.io/user-center/basic-information/interface-key)
3. Create a new API key and copy it
4. Paste it into **Settings > Connectors** in your WordPress admin

= WP-CLI Installation =

`wp plugin install alamin-ai-provider-for-minimax --activate`

== Frequently Asked Questions ==

= What is MiniMax? =

MiniMax is an AI company building high-performance large language models. Their MiniMax M2 series offers frontier-level text generation capabilities with strong multilingual support and competitive pricing. Learn more at [minimax.io](https://www.minimax.io/).

= Where do I enter my API key? =

Go to **Settings > Connectors** in your WordPress admin and enter your MiniMax API key there. All AI-enabled plugins on your site will automatically use it. Alternatively, set the `MINIMAX_API_KEY` environment variable on your server.

= Do I need to install a separate AI Client plugin? =

No. WordPress 7.0 and higher include the AI Client SDK natively — no additional plugin is required.

= Which MiniMax model should I choose as the default? =

* **Best quality** — MiniMax-M2.7 for the highest capability tasks
* **Speed + quality** — MiniMax-M2.7 Highspeed or MiniMax-M2.5 Highspeed for fast responses without much quality loss
* **Cost-effective** — MiniMax-M2.1 or MiniMax-M2.1 Highspeed for high-volume, lower-cost use cases
* **General use** — MiniMax-M2.5 is a well-rounded choice for most WordPress content tasks

= What happens if the MiniMax API is unreachable? =

The plugin falls back to a hardcoded list of 9 MiniMax models so the AI Client continues to function and AI-enabled plugins stay operational.

= Can I use multiple AI provider plugins at the same time? =

Yes. WordPress lets you install multiple provider plugins. You can switch the active provider from **Settings > Connectors** at any time without reconfiguring any plugin.

= Is my API key stored securely? =

Your API key is stored in the WordPress options table, which is protected by your database credentials. For higher security, set the `MINIMAX_API_KEY` environment variable on your server — this keeps the key entirely out of the database.

= How does billing work? =

Billing is handled entirely by MiniMax. Your usage is billed according to [MiniMax's pricing](https://platform.minimax.io) based on tokens consumed. WordPress and this plugin do not charge anything.

= What generation parameters does this provider support? =

Temperature, max tokens, top P, presence penalty, frequency penalty, stop sequences, system instruction, and function declarations — the full set of options supported by the WordPress AI Client SDK.

= Is MiniMax good for multilingual content? =

Yes. MiniMax models perform well across multiple languages, particularly English and Chinese. They are a strong choice for WordPress sites targeting multilingual or Chinese-language audiences.

= Can I use this for WooCommerce product descriptions? =

Yes, if you have a WooCommerce plugin that integrates with the WordPress AI Client. Once this provider is active and your API key is set, any AI-enabled WooCommerce plugin can generate product descriptions, SEO meta, and more.

= Does this work with the Gutenberg block editor? =

Yes. Any Gutenberg plugin or block that uses the WordPress AI Client will automatically use this provider once configured.

= Do you have a developer API? =

This plugin implements the standard WordPress AI Client provider interface. Developers building plugins or themes on top of the WordPress AI Client do not need to do anything special — if this provider is active, it will be available automatically.

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

= 1.3.1 - 2026-06-16 =

**Fixed**
- API key stored via Settings > Connectors (`connectors_ai_minimax_api_key`) now correctly used for live model list fetching — previously fell back to the hardcoded model list even when the Connectors key was set.
- Settings `get_settings()` now returns all 6 default fields when the saved option is corrupt or missing; previously returned only `temperature` and `max_tokens`.

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
- Improved plugin file header field ordering per WordPress.org standard.
- Updated tested up to WordPress 7.0.

= 1.0.0 - 2026-01-01 =

* Initial release.
* MiniMax provider registration with WordPress AI Client.
* Dynamic model discovery with transient caching and fallback list.
* Settings page for default model configuration.
* Support for `MINIMAX_API_KEY` environment variable.

== Upgrade Notice ==

= 1.3.1 =
Fixes live model list not loading when API key is set via Settings > Connectors. No database changes required.

= 1.3.0 =
Adds Top P, Presence Penalty, and Frequency Penalty settings. No database changes required. Requires WordPress 7.0 or higher.

= 1.2.1 =
Fixes connector showing "Connected" before an API key is entered. No database changes required.

= 1.2.0 =
Fixes a false "no valid connector" warning on the AI admin page. No database changes required.
