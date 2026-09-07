# Scope — speech, video, music and embeddings

**Written 2026-09-07.** A scope, not a plan of record: it says what each capability
would cost and what stands in the way, so the decision to build one can be made on
evidence rather than enthusiasm.

Today this plugin implements two of MiniMax's capability families — text generation
and image generation. MiniMax ships four more. This is what adding them involves.

## What the AI Client can accept

Capability enums in `wordpress/php-ai-client` 1.4.0, and whether a model contract
exists for each:

| Capability enum | Model contract | Method a model must implement |
|---|---|---|
| `TEXT_GENERATION` | ✅ | already implemented |
| `IMAGE_GENERATION` | ✅ | already implemented |
| `TEXT_TO_SPEECH_CONVERSION` | ✅ | `convertTextToSpeechResult( array $prompt ): GenerativeAiResult` |
| `SPEECH_GENERATION` | ✅ | `generateSpeechResult( array $prompt ): GenerativeAiResult` |
| `VIDEO_GENERATION` | ✅ | `generateVideoResult( array $prompt ): GenerativeAiResult`, or `generateVideoOperation( array $prompt ): GenerativeAiOperation` |
| `EMBEDDING_GENERATION` | ✅ | `EmbeddingGenerationModelInterface` |
| `MUSIC_GENERATION` | ❌ | **the enum exists; there is no contract directory** |

That last row is the finding that decides the order. `CapabilityEnum::MUSIC_GENERATION`
is declared, but `Providers/Models/` has no `MusicGeneration/` family beside the other
six. There is nothing to implement against, so **music is blocked upstream** no matter
how much we want it — the work is an SDK contribution, not a plugin feature.

## What MiniMax offers

From the [API overview](https://platform.minimax.io/docs/api-reference/api-overview):

| Family | Models | Shape |
|---|---|---|
| Speech / T2A | `speech-2.8-hd`, `speech-2.8-turbo`, and 2.6 / 02 variants | **Sync** HTTP up to 10,000 characters · **async** task API up to 1,000,000 · WebSocket streaming |
| Video | `MiniMax-H3` (768P/2K, 4–15s), `MiniMax-H3-Max` (480P/768P, 5–15s) | **Async only** — create task, poll by `task_id`, read `content.url` |
| Music | `music-3.0` | — |
| Embeddings | not confirmed in the overview | **needs checking before scoping** |

## Ranked

### 1. Text-to-speech — small, and the natural next one

The sync T2A endpoint returns audio in one call, which maps directly onto
`convertTextToSpeechResult()`. No new infrastructure: it is the same shape as the
image model, which already extends an SDK base for shared plumbing and overrides
`createRequest()` where MiniMax diverges from OpenAI.

Work: one model class, metadata entries for the speech models, options for format
(mp3/pcm/flac/wav), volume, pitch and speed, and the 10,000-character ceiling
enforced before the request rather than discovered as an API error.

Deliberately excluded: the async 1M-character path and the WebSocket streaming path.
Both are worth having and neither is worth blocking a first release on.

### 2. Embeddings — small, but verify first

The SDK has the contract. Whether MiniMax exposes an embeddings endpoint is **not
established** — the API overview does not list one, and the plugin should not declare a
capability that turns out not to exist. Confirm the endpoint before estimating.

### 3. Video — the expensive one, and not because of the API

Video is **async only**: create a task, get a `task_id`, poll until it completes, then
read `content.url`. Three consequences, none of them about writing a model class:

- It must implement `VideoGenerationOperationModelInterface`, not the sync interface.
  Declaring `VIDEO_GENERATION` against `generateVideoResult()` would mean blocking a
  PHP request on a job that takes minutes.
- **WordPress has no job primitive here.** Polling needs Action Scheduler or WP-Cron,
  and that is plugin infrastructure this plugin does not currently have — it is
  presently a stateless provider with no scheduled work and no storage.
- **The download URL expires after 9 hours.** A generated video that nobody fetches in
  time is lost, so the result has to be pulled into the media library, or handed to the
  caller with that expiry made explicit. That is a product decision before it is a
  technical one.

So the cost is not the endpoint. It is that video turns a stateless provider into one
that owns background work and stored artefacts.

### 4. Music — blocked

No SDK contract. Revisit if `Providers/Models/MusicGeneration/` appears, or contribute
it upstream.

## Recommendation

Take **text-to-speech** first: it is self-contained, it uses machinery the plugin
already has, and it is the family a WordPress site is most likely to want — captions,
audio versions of posts, accessibility.

Check the **embeddings** endpoint next, since confirming it is cheap and it would be
the second-smallest build if it exists.

Treat **video** as its own project rather than a fourth model class, and decide the
artefact question — media library or expiring URL — before writing any of it.

Leave **music** until the SDK has somewhere to put it.
