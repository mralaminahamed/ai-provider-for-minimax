# Git Commit Instructions for AI Provider for MiniMax Plugin

## Overview

This plugin uses [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/) to maintain a clear and consistent commit history. All commits must follow the specified format to enable automated changelog generation and semantic versioning.

## Commit Message Format

```
type(scope): short description

[optional body]

[optional footer]
```

### Required Elements

- **type**: The category of change (see Types section below)
- **scope**: The area of the codebase affected (optional, but recommended)
- **description**: Brief, imperative description of the change

### Types

| Type       | Description                                   | Release Impact     |
| ---------- | --------------------------------------------- | ------------------ |
| `feat`     | New feature for users                         | Minor version bump |
| `fix`      | Bug fix for users                             | Patch version bump |
| `docs`     | Documentation changes                         | No version bump    |
| `style`    | Code style changes (formatting, etc.)         | No version bump    |
| `refactor` | Code restructuring without functional changes | No version bump    |
| `perf`     | Performance improvements                      | Patch version bump |
| `test`     | Adding/updating tests                         | No version bump    |
| `chore`    | Maintenance tasks (build, dependencies)       | No version bump    |
| `ci`       | CI/CD pipeline changes                        | No version bump    |
| `build`    | Build system changes                          | No version bump    |

### Scope Guidelines

Use specific, meaningful scopes that clearly identify the affected area:

**Common Scopes:**

- `provider` - Provider registration logic
- `model` - Text generation model (TextGenerationModel)
- `metadata` - Model metadata directory / API fetch / fallback list
- `settings` - WP admin settings page
- `api` - API key resolution or request handling
- `docs` - Documentation files
- `ci` - CI/CD workflows
- `deps` - Dependency updates

**Examples:**

```
feat(metadata): fetch live model list from MiniMax API
fix(settings): resolve transient cache key collision
docs(readme): update installation instructions
chore(deps): bump phpstan to 2.x
```

## Detailed Examples

### Feature Commits

```
feat(provider): register provider with AI Client registry

- Wire up Provider on init hook (priority 5)
- Resolve API key from env then WP option

Closes #12
```

### Bug Fix Commits

```
fix(metadata): clear stale transient on API error

Transient was previously cached indefinitely when the
remote API returned a non-200 status. Now expires in 5 min.
```

### Documentation Commits

```
docs(readme): add non-affiliation disclaimer for WP.org review
```

### Refactoring Commits

```
refactor(metadata): extract get() helper to reduce duplication
```

## Best Practices

### Commit Size

- **Small & Focused**: Each commit should address one logical change
- **Atomic**: Changes should be complete and independently reviewable
- **Tested**: Verify functionality before committing
- **No Mixed Concerns**: Don't combine features, fixes, and refactoring

### Description Guidelines

- Use imperative mood: "add", "fix", "update", not "added", "fixed", "updated"
- Keep under 50 characters for the subject line
- Start with lowercase (conventional commits are case-insensitive)
- Be specific and descriptive

### Body & Footer

- **Body**: Explain what and why, not how (implementation details go in code comments)
- **Footer**: Reference issues with `Fixes #123`, `Closes #456`, or breaking changes with `BREAKING CHANGE:`

## Breaking Changes

```
feat(provider): remove deprecated filter hooks

BREAKING CHANGE: The following filter hooks are removed:
- `minimax_old_filter`
- `minimax_legacy_hook`

Use the new hooks documented in the migration guide.
```

## Workflow Integration

### Branch Naming

- `feature/description` for new features
- `fix/issue-description` for bug fixes
- `docs/update-readme` for documentation
- `chore/update-dependencies` for maintenance

### Pull Request Process

1. Create feature branch from `trunk`
2. Make focused commits following these guidelines
3. Push branch and create pull request
4. Ensure PR description explains the changes
5. Wait for review and CI checks
6. Squash merge with conventional commit message

## Resources

- [Conventional Commits Specification](https://www.conventionalcommits.org/)
- [Semantic Versioning](https://semver.org/)

---

For questions about commit conventions, see AGENTS.md or contact a maintainer.
