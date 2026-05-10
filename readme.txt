=== AI Provider for MiniMax ===
Donate link: https://alaminahamed.com/donate
Contributors: mralaminahamed
Tags: ai, minimax, artificial intelligence, text generation
Requires at least: 6.7
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://spdx.org/licenses/GPL-2.0-or-later.html

Independent MiniMax provider integration for the WordPress AI Client. Enables MiniMax-M2 model access for coding and text generation.

== Description ==

This plugin integrates [MiniMax](https://www.minimax.io/) as an AI provider for the WordPress AI Client. It enables access to MiniMax's high-performance AI models through the OpenAI-compatible API.

This plugin is an independent, third-party integration and is not affiliated with, endorsed by, or sponsored by MiniMax. "MiniMax" is the name of the third-party service this plugin connects to.

**Features:**

* Seamless integration with the WordPress AI Client plugin
* Dynamic model discovery from the MiniMax API
* Support for text generation with the MiniMax-M2 series
* Secure API key management via WordPress settings or environment variable

**Requirements:**

* WordPress AI Client plugin (or WordPress 7.0+ with built-in AI Client)
* A [MiniMax](https://www.minimax.io/) account and API key

**API Key Configuration:**

Set your MiniMax API key in one of two ways:

1. WordPress AI Client settings — go to the WordPress AI Client configuration page and enter your MiniMax API key there
2. `MINIMAX_API_KEY` environment variable on your server (takes priority over the database setting)

== Installation ==

= As a WordPress Plugin =

1. Download the plugin zip file
2. Go to **Plugins > Add New > Upload Plugin** in your WordPress admin
3. Upload the zip and click **Install Now**
4. Ensure the **WordPress AI Client** plugin is installed and activated
5. Activate **AI Provider for MiniMax**
6. Enter your MiniMax API key via the WordPress AI Client settings
7. Optionally go to **Settings > MiniMax** to configure the default model and generation parameters

= Manual Installation =

1. Upload the `alamin-ai-provider-for-minimax` folder to `/wp-content/plugins/`
2. Follow steps 4–6 above

= As a Composer Package =

`composer require mralaminahamed/alamin-ai-provider-for-minimax`

== Frequently Asked Questions ==

= What is MiniMax? =

MiniMax is an AI company that provides high-performance language models, including the MiniMax-M2 series known for their coding and text generation capabilities. Learn more at [minimax.io](https://www.minimax.io/).

= Do I need the WordPress AI Client? =

Yes. This plugin is a provider add-on for the WordPress AI Client. Install and activate that plugin first.

= Where do I get an API key? =

Sign up at [minimax.io](https://www.minimax.io/) and generate an API key from your account dashboard.

= Is my API key stored securely? =

Your API key is stored by the WordPress AI Client plugin in the WordPress options table. For higher security, set the `MINIMAX_API_KEY` environment variable on your server instead — this bypasses the database entirely.

== External Services ==

This plugin connects to the **MiniMax API** (`https://api.minimax.io/v1`) to provide AI text generation.

**What the service does:** MiniMax is a third-party AI platform that provides large language model APIs. This plugin uses it to retrieve available models and send text generation requests.

**What data is sent and when:**

* Your MiniMax API key is sent via the `Authorization` header on every request.
* When a WordPress feature triggers AI text generation, the text prompt or conversation is sent to `https://api.minimax.io/v1`.
* When the model list is refreshed (once per hour, cached via WordPress transients), a request is sent to `https://api.minimax.io/v1/models` with your API key.

No data is sent until you enter an API key and a WordPress feature triggers a request.

This service is provided by MiniMax: [Terms of Service](https://platform.minimax.io/protocol/terms-of-service), [Privacy Policy](https://platform.minimax.io/protocol/privacy-policy).

== Changelog ==

= 1.0.0 =
* Initial release
* MiniMax provider registration with WordPress AI Client
* Dynamic model discovery
* Settings page for API key and default model configuration
* Support for `MINIMAX_API_KEY` environment variable

== Upgrade Notice ==

= 1.0.0 =
Initial release. No upgrade steps required.
