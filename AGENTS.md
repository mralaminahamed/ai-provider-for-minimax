# AGENTS.md

## Overview

This is a **WordPress AI Provider plugin** for MiniMax. It registers MiniMax as an AI provider using the [WordPress PHP AI Client](https://github.com/WordPress/wp-ai-client).

## Architecture Pattern

**Follow the official WordPress AI Provider structure** from these repositories:

- **Reference Implementation**: https://github.com/WordPress/ai-provider-for-anthropic
- **Base SDK**: https://github.com/WordPress/wp-ai-client
- **Other Providers**: `ai-provider-for-openai`, `ai-provider-for-google`

### Correct Structure

```
ai-provider-for-minimax/
├── plugin.php                              # Entry point (registers provider on 'init')
├── src/
│   ├── Provider/
│   │   └── MiniMaxProvider.php           # Extends AbstractApiProvider
│   ├── Models/
│   │   └── MiniMaxTextGenerationModel.php
│   └── Metadata/
│       └── MiniMaxModelMetadataDirectory.php
├── composer.json
├── readme.txt
└── .wordpress-org/                         # Plugin assets (icon, banner)
```

### Key Implementation Rules

1. **Provider class** must extend `WordPress\AiClient\Providers\ApiBasedImplementation\AbstractApiProvider`
2. **Plugin file** registers provider via `AiClient::defaultRegistry()->registerProvider()` on `init` hook
3. **No hardcoded model lists** - use dynamic discovery via `/v1/models` API
4. **API endpoint**: `https://api.minimax.io/v1` (OpenAI-compatible)
5. **Namespace**: `AlAminAhamed\MiniMaxAiProvider`
6. **Package name**: `mralaminahamed/ai-provider-for-minimax`

### Required AbstractApiProvider Methods

```php
protected static function baseUrl(): string
protected static function createModel(ModelMetadata $modelMetadata, ProviderMetadata $providerMetadata): ModelInterface
protected static function createProviderMetadata(): ProviderMetadata
protected static function createProviderAvailability(): ProviderAvailabilityInterface
protected static function createModelMetadataDirectory(): ModelMetadataDirectoryInterface
```

### Required ModelMetadataDirectoryInterface Methods

```php
public function listModelMetadata(): array
public function hasModelMetadata(string $model_id): bool
public function getModelMetadata(string $model_id): ModelMetadata
```

### ModelMetadata Constructor

```php
new ModelMetadata(
    $model_id,                           // string
    $model_name,                         // string
    [CapabilityEnum::textGeneration()],  // array<CapabilityEnum>
    [new SupportedOption(OptionEnum::maxTokens())] // array<SupportedOption>
)
```

## Common Mistakes to Avoid

- **Do NOT** use `TextGenerationCapability` class - it doesn't exist
- **Do NOT** hardcode model arrays - models are discovered dynamically
- **Do NOT** skip the metadata directory for model capabilities
- **Do NOT** use custom static method patterns

## Dependencies

- PHP 7.4+ or 8.0+
- WordPress 7.0+ (or `wordpress/php-ai-client` package for older versions)
- `WordPress\AiClient\AiClient` class
- `wordpress/wp-ai-client` package v0.4+

## Configuration

- **API Key env**: `MINIMAX_API_KEY`
- **Provider ID**: `minimax`
- **Base URL**: `https://api.minimax.io/v1`

## Important Files to Reference

| File | Purpose |
|------|---------|
| `src/Provider/MiniMaxProvider.php` | Provider class pattern |
| `src/Models/MiniMaxTextGenerationModel.php` | Model implementation pattern |
| `src/Metadata/MiniMaxModelMetadataDirectory.php` | Metadata directory pattern |
| `vendor/wordpress/php-ai-client/src/Providers/Models/DTO/ModelMetadata.php` | ModelMetadata DTO |
| `vendor/wordpress/php-ai-client/src/Providers/Models/Enums/CapabilityEnum.php` | Capability enum |
| `vendor/wordpress/php-ai-client/src/Providers/Models/Enums/OptionEnum.php` | Option enum |
