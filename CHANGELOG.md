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
- Ingested proj-g: bounded page sizes, module-local string-backed enums, validated DTO construction, Action evidence, and Filament enum navigation groups
- New human-owned references distilled from internal team style skills: `php-style.md` (PSR-12 language style — file basics, `declare(strict_types=1)`, naming, strings, arrays, PHP 8.x types, control statements, class layout), `dependency-injection.md` (constructor vs method injection, per-call data, interface seams), `error-handling.md` (typed exceptions, centralized `renderable()` mapping, no double-report, `$dontReport`, dead code after `throw`), `jobs-events.md` (`ShouldQueue` default, transactional job body, one orchestration strategy), `logging.md` (structured context)
- Ingested proj-h: dedicated spreadsheet export classes with explicit inputs, eager-loaded query-backed datasets, and conditional view rendering
- Ingested proj-i: gateway status boundary mapping, private external payload snapshots, abstract notification channel bases, and signature-gated webhook FormRequest exception
- Ingested proj-j: domain module subfolder layout, typed builders for complex filters, and named DTO factories for model normalization
- Ingested proj-k: external SDK boundary DTOs, guarded nested DTO accessors, truly identical shared DTOs, and pure result projections

Source catalog code: proj-a. Scope: full-api, Laravel 10, whole project. No source project identities recorded here.

### Rejected
- Business-rule violations standardized on `ValidationException`/`abort_if`: contradicted by legitimate custom exception usage and redundant with existing guard-clause guidance.
- Rejected proj-f repository-layer proposal: conflicts with Service/Action standard; no patterns extracted.
- Thin controllers, list/detail resources, singleton exception formatting, environment-gated doc routes, and `pagination_limit()` helper: insufficiently novel or evidenced.
