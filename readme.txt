=== AI Provider for MiniMax – M2 & M3 LLM Models for the WordPress AI Client ===
Contributors:      mralaminahamed
Tags:              minimax, ai, llm, ai provider, text generation
Requires at least: 7.0
Tested up to:      7.0
Stable tag:        1.5.0
Requires PHP:      7.4
License:           GPL-2.0-or-later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

MiniMax provider for the WordPress AI Client. Access MiniMax M3 and M2 series models for high-performance text generation.

== Description ==

This plugin provides [MiniMax](https://www.minimax.io/) integration for the WordPress AI Client. It enables WordPress sites to use MiniMax's high-performance large language models for text generation, content creation, and other AI capabilities through an OpenAI-compatible API.

This plugin is an independent, third-party integration and is not affiliated with, endorsed by, or sponsored by MiniMax.

= Why MiniMax? =

MiniMax is an AI company known for its high-performance, cost-effective language models. The **MiniMax M2 and M3 series** deliver frontier-level text generation quality — competitive with top models from OpenAI and Anthropic — while offering attractive pricing for high-volume WordPress use cases.

MiniMax models are particularly strong at:

* **Long-context tasks** — handling large documents, long conversations, and extended content generation
* **Instruction following** — precise, structured output generation suitable for automated pipelines
* **Multilingual content** — strong performance in English, Chinese, and other languages
* **Creative writing** — rich, nuanced prose generation for blogs, marketing copy, and storytelling

= Features =

* **MiniMax M2 and M3 series models** — including Highspeed variants optimised for faster responses
* **Automatic model discovery** — live model list fetched from the MiniMax API and cached hourly; falls back to a hardcoded list when offline
* **Full parameter control** — temperature, max tokens, top P, thinking mode, service tier, system instruction, and function declarations
* **Chat history** — multi-turn conversations, not just single prompts
* **Image generation** — `image-01`, with aspect ratio, orientation and multiple candidates
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

When an API key is configured, the live model list is fetched directly from the MiniMax API, so you always see the latest models. If the API is ever unavailable, a built-in fallback list of 8 models keeps everything working — the **MiniMax-M3** flagship plus the **M2 series** (M2.7, M2.5, M2.1, and M2), each with an optional Highspeed variant tuned for lower latency.

See the [full model catalogue](https://github.com/mralaminahamed/ai-provider-for-minimax/blob/trunk/docs/MODELS.md) for every model ID. Not sure which to pick? See the FAQ below.

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
* **Max Tokens** — maximum tokens in the response (1–1,000,000, default 4096)
* **Top P** — nucleus sampling threshold (0.0–1.0, default 1.0)
* **Thinking** — whether MiniMax-M3 reasons before answering; disabling it is faster and cheaper (adaptive or disabled, default adaptive)
* **Service Tier** — standard, or priority routing at 1.5x the cost (default standard)

= For Developers =

This plugin follows the official WordPress AI Provider pattern and works with any plugin built on the WordPress AI Client SDK. It registers the `minimax` provider and supports the generation options MiniMax actually honours (temperature, max tokens, top P, system instruction, and function declarations). Presence and frequency penalties and stop sequences are deliberately not declared, because MiniMax documents them as ignored.

The source code is on GitHub — bug reports and pull requests are welcome:

* Repository: [github.com/mralaminahamed/ai-provider-for-minimax](https://github.com/mralaminahamed/ai-provider-for-minimax)
* Report a bug or request a feature: [open a new issue](https://github.com/mralaminahamed/ai-provider-for-minimax/issues/new)
* Contribute code: [submit a pull request](https://github.com/mralaminahamed/ai-provider-for-minimax/pulls)

Developer documentation:

* [Usage and code examples](https://github.com/mralaminahamed/ai-provider-for-minimax/blob/trunk/docs/USAGE.md)
* [Architecture and API key resolution](https://github.com/mralaminahamed/ai-provider-for-minimax/blob/trunk/docs/ARCHITECTURE.md)
* [Development and contributing](https://github.com/mralaminahamed/ai-provider-for-minimax/blob/trunk/docs/DEVELOPMENT.md)

= More from us =

Other free plugins by the same author, all on WordPress.org.

**Another provider for the same AI Client**

* [AI Provider for OpenCode Zen](https://wordpress.org/plugins/alamin-ai-provider-for-opencode-zen/) - One API key, 61 models including GPT-5, Claude and Gemini 3, for the WordPress AI Client.

**For any site**

* [Swift Menu Duplicator](https://wordpress.org/plugins/swift-menu-duplicator/) - Duplicate menus in one click, snapshot revisions, export and import, WP-CLI and REST.
* [Author Profile Blocks](https://wordpress.org/plugins/author-profile-blocks/) - Author and team profiles as Gutenberg blocks — grid, carousel, list and single.

**For a WooCommerce store**

* [StoreSeeder](https://wordpress.org/plugins/storeseeder/) - Realistic test data for WooCommerce and Fluent Cart — a whole shop from a recipe, with one-click cleanup.
* [StoreSheet](https://wordpress.org/plugins/storesheet/) - Sync WooCommerce products, orders and coupons to a Google spreadsheet, one way and in the background.

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

MiniMax is an AI company building high-performance large language models. Their MiniMax M2 and M3 series offer frontier-level text generation capabilities with strong multilingual support and competitive pricing. Learn more at [minimax.io](https://www.minimax.io/).

= Where do I enter my API key? =

Go to **Settings > Connectors** in your WordPress admin and enter your MiniMax API key there. All AI-enabled plugins on your site will automatically use it. Alternatively, set the `MINIMAX_API_KEY` environment variable on your server.

= Do I need to install a separate AI Client plugin? =

No. WordPress 7.0 and higher include the AI Client SDK natively — no additional plugin is required.

= Which MiniMax model should I choose as the default? =

* **Best quality** — MiniMax-M3 for the highest capability and long-context tasks
* **Speed + quality** — MiniMax-M2.7 Highspeed or MiniMax-M2.5 Highspeed for fast responses without much quality loss
* **Cost-effective** — MiniMax-M2.1 or MiniMax-M2.1 Highspeed for high-volume, lower-cost use cases
* **General use** — MiniMax-M2.5 is a well-rounded choice for most WordPress content tasks

= What happens if the MiniMax API is unreachable? =

The plugin falls back to a hardcoded list of 8 MiniMax models so the AI Client continues to function and AI-enabled plugins stay operational.

= Can I use multiple AI provider plugins at the same time? =

Yes. WordPress lets you install multiple provider plugins. You can switch the active provider from **Settings > Connectors** at any time without reconfiguring any plugin.

= Is my API key stored securely? =

Your API key is stored in the WordPress options table, which is protected by your database credentials. For higher security, set the `MINIMAX_API_KEY` environment variable on your server — this keeps the key entirely out of the database.

= How does billing work? =

Billing is handled entirely by MiniMax. Your usage is billed according to [MiniMax's pricing](https://platform.minimax.io) based on tokens consumed. WordPress and this plugin do not charge anything.

= What generation parameters does this provider support? =

Temperature, max tokens, top P, thinking mode, service tier, system instruction, and function declarations. Presence and frequency penalties and stop sequences are not offered: MiniMax ignores them, so a control for them would do nothing.

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

== Changelog ==

= 1.5.0 - 2026-08-09 =

**Added**
- **Settings are now applied to requests.** Temperature, max tokens and top_p had been stored since 1.0.0 and never read — nothing outside the settings class touched `minimax_settings`, so saving the form changed a database row and nothing else. A caller's own value still wins; the saved values fill in what was left unset.
- **Thinking mode** — MiniMax-M3 can be told to answer without reasoning first, which is faster and cheaper. The M2 series always reasons and ignores the setting.
- **Service tier** — opt in to priority routing, which MiniMax bills at 1.5x standard.
- `minimax_generate_text_params` filter, for anything this plugin does not model.

* **Image input on MiniMax-M3.** Vision has worked since the first release — the AI Client turns an image into an `image_url` content part — but was never declared, so image prompts were routed to other providers. The M2 series is text only and is declared as such.
* **Custom options.** Arbitrary passthrough parameters, which the AI Client's base class already merged into the request body but which no caller could reach, because the option was not declared. All three official WordPress AI providers offer this.
* **Image generation.** `image-01` is now offered as an image model, served from MiniMax's own `/v1/image_generation` endpoint. Aspect ratio, orientation, number of candidates, and a choice between a URL and inline base64 are all supported. Reach `seed`, `prompt_optimizer` and `subject_reference` through the new `minimax_generate_image_params` filter.
* **Chat history** — the models are now declared as supporting multi-turn conversations, not just single prompts. They always could (the OpenAI-compatible endpoint takes a `messages` array and the AI Client already sends one), but the capability was never declared and the AI Client routes on the declaration, so conversation requests were going to other providers. Every official WordPress AI provider declares this.

**Changed**
- **PHP namespace is now `MiniMax\MiniMaxAiProvider\`** (was `AlAminAhamed\MiniMaxAiProvider\`).
- **Class names dropped their `MiniMax` prefix**, which the namespace already carries: `MiniMaxProvider` is `Provider`, `MiniMaxSettings` is `Settings`, `MiniMaxModelMetadataDirectory` is `ModelMetadataDirectory`, `MiniMaxTextGenerationModel` is `TextGenerationModel`, and `MiniMaxProviderAvailability` is `ProviderAvailability`.
- Max tokens now accepts up to 1,000,000, matching MiniMax-M3's context window. The previous ceiling of 200,000 was the M2 limit.

- **Restructured the bootstrap.** The plugin file is now an entry point — constants, autoloader, boot — and all wiring moved into an `AI_Provider_For_MiniMax` singleton in `class-ai-provider-for-minimax.php`, so every hook the plugin registers is visible in one file. Matches the layout used across this author's other plugins.
- Added `MINIMAX_VERSION`, `MINIMAX_URL` and `MINIMAX_PATH` constants; only `MINIMAX_PLUGIN_FILE` existed before.

Both renames are internal. No hook, option, setting or model id changes, and nothing a site has configured is affected — but any code referencing these classes directly needs updating.

**Removed**
- **Presence penalty and frequency penalty settings.** MiniMax documents both as ignored on its OpenAI-compatible endpoint, so the controls could not affect anything. Saved values are left in the database untouched.
- `presencePenalty`, `frequencyPenalty` and `stopSequences` are no longer declared as supported model options. The AI Client uses those declarations to decide which model can satisfy a request, so claiming them routed callers here and had their request quietly ignored. A caller that needs stop sequences is now correctly routed elsewhere.

= 1.4.0 - 2026-07-21 =

**Added**
- Connection status on the settings page — shows whether a MiniMax API key is configured, with a link to the Connectors screen.
- "Connectors" quick link on the Plugins screen, next to Settings.

**Changed**
- Synced the built-in fallback model list to the official MiniMax catalogue — added MiniMax-M3 and removed the retired MiniMax-M1 and MiniMax-Text-01 (8 models).
- Default model is now MiniMax-M3, pre-selected out of the box instead of an empty choice.

**Fixed**
- Autoloader collision with WordPress core's bundled AI Client that could cause a TypeError on AI requests — the plugin no longer ships a conflicting copy of php-ai-client (WordPress core provides it at runtime).

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

= 1.5.0 =
Adds image generation via image-01, and declares chat-history support. Settings now actually apply to requests — temperature, max tokens and top_p were stored and never read. Adds thinking mode and service tier. Removes the presence and frequency penalty controls, which MiniMax ignores. Internal PHP namespace and class names changed; no database changes and no settings to redo.

= 1.4.0 =
Adds a connection-status indicator, defaults to MiniMax-M3, and syncs the fallback model list with the official MiniMax catalogue. Fixes a possible AI Client TypeError. No database changes required.

= 1.3.1 =
Fixes live model list not loading when API key is set via Settings > Connectors. No database changes required.

= 1.3.0 =
Adds Top P, Presence Penalty, and Frequency Penalty settings. No database changes required. Requires WordPress 7.0 or higher.

= 1.2.1 =
Fixes connector showing "Connected" before an API key is entered. No database changes required.

= 1.2.0 =
Fixes a false "no valid connector" warning on the AI admin page. No database changes required.
