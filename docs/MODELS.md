# Models

MiniMax exposes its text/chat-completion models through an OpenAI-compatible API.

## How the model list is resolved

1. **Live discovery** — when an API key is configured, the plugin calls `GET https://api.minimax.io/v1/models` and uses whatever the API returns. This is always the source of truth.
2. **Caching** — the live list is stored in the `minimax_models_cache` transient for **1 hour**. On an API error the plugin caches an empty result for **5 minutes** and serves the fallback, so a transient outage does not hammer the endpoint.
3. **Fallback** — with no API key, or while an error is cached, the plugin serves a built-in list so the AI Client and any AI-enabled plugins keep working.

The default model (used when a request specifies none) is **`MiniMax-M3`**.

## Fallback catalogue

Mirrors the [official MiniMax catalogue](https://platform.minimax.io/docs/api-reference/api-overview) — **8 text models plus `image-01`**. This list is only a safety net; the live API overrides it whenever a key is present.

| Model ID | Name |
|---|---|
| `MiniMax-M3` | MiniMax M3 (flagship) |
| `MiniMax-M2.7` | MiniMax M2.7 |
| `MiniMax-M2.7-highspeed` | MiniMax M2.7 Highspeed |
| `MiniMax-M2.5` | MiniMax M2.5 |
| `MiniMax-M2.5-highspeed` | MiniMax M2.5 Highspeed |
| `MiniMax-M2.1` | MiniMax M2.1 |
| `MiniMax-M2.1-highspeed` | MiniMax M2.1 Highspeed |
| `MiniMax-M2` | MiniMax M2 |

### Image

| Model ID | Name |
|---|---|
| `image-01` | MiniMax Image 01 |

Served from `POST /v1/image_generation`, not the chat endpoint, and declared
with image capabilities only — it is not a chat model and must not be offered
as one.

The `-highspeed` variants trade some quality for faster inference.

## Keeping the fallback in sync

The fallback lives in `includes/Metadata/ModelMetadataDirectory.php` (`get_fallback_models()`). When the official catalogue changes, update that array **and** its PHPUnit expectations in `tests/phpunit/Metadata/ModelMetadataDirectoryTest.php` (`getExpectedModelCount()` plus the model ID lists), then refresh the counts in `README.md` and `readme.txt`. Source of truth: <https://platform.minimax.io/docs/api-reference/api-overview>.
