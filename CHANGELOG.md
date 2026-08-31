# Changelog

All notable changes to this plugin will be documented in this file.
Format: [Semantic Versioning](https://semver.org/). Never mention client names or source project identities here.

## [0.1.0] - Unreleased
### Added
- Initial plugin scaffold: skill `laravel-standards`, `/review-code` command
- Base references: coding-standards, security, api-response, workflow, manual-rules, version-notes
- Pattern files: blade-monolith and vue-hybrid awaiting ingestion; full-api populated from proj-a
- Full API patterns: domain-scoped route files, modular Action classes with instance `execute()`, and FormRequest-to-DTO mapping
- Ingested proj-b: Laravel 10 API permission middleware scoped per action and allowlisted, eager-loaded, paginated query pipelines
- Ingested proj-c: protected API route groups for authentication and cross-cutting middleware
- Ingested proj-d: area layout + section/stack composition for Blade views; protected route group rule widened to L7+ and strengthened with mirrored `name()` prefixes
- Ingested proj-e: grouped read services with typed, server-derived scope and named result methods

Source catalog code: proj-a. Scope: full-api, Laravel 10, whole project. No source project identities recorded here.

### Rejected
- Business-rule violations standardized on `ValidationException`/`abort_if`: contradicted by legitimate custom exception usage and redundant with existing guard-clause guidance.
- Thin controllers, list/detail resources, singleton exception formatting, environment-gated doc routes, and `pagination_limit()` helper: insufficiently novel or evidenced.
