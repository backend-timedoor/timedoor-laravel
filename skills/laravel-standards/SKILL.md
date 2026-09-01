---
name: laravel-standards
description: Timedoor's Laravel backend coding standards. Use this skill for ANY PHP or Laravel work, no matter how small — writing new code, creating or editing controllers, models, migrations, services, form requests, API resources, jobs, commands, middleware, blade views, or tests; refactoring or reviewing existing code; designing API endpoints or response formats; fixing bugs in Laravel projects; answering questions about how the team writes PHP. Even a one-line change to a Laravel file must follow these standards, so always consult this skill before touching PHP/Laravel code.
---

# Laravel Coding Standards

This skill defines how Laravel backend code must be written. All rules here are mandatory unless the user explicitly overrides them.

## Core principles (always apply)

1. **Keep controllers thin.** Validate via FormRequest, perform simple single-step operations directly, delegate non-trivial multi-step business logic to a Service/Action, return a response. Business logic never lives in Blade views.
2. **Use FormRequest for every API endpoint that accepts input** — including single-field payloads. Never inline `$request->validate()`.
3. **API responses always follow the Baskito envelope** defined in `references/api-response.md`: `data` for success, `error.code/title/message/errors[]` for failure. Never invent a new response shape.
4. **Eloquent over raw SQL.** Raw queries only with parameter binding and only when Eloquent/Query Builder genuinely cannot express it. Never interpolate variables into SQL strings.
5. **Explicit over magic.** Type-hint parameters and return types. Avoid dynamic property access patterns that hide intent.
6. **Every rule has a reason.** If a reference file explains why, follow the spirit, not just the letter.

## When to read which reference

Read the relevant file(s) BEFORE writing code — not after:

| Task | Read |
|---|---|
| Any new PHP code, refactor, or review | `references/coding-standards.md` + `references/manual-rules.md` |
| Anything touching auth, input, files, queries, secrets | `references/security.md` |
| Any API endpoint, resource, or response | `references/api-response.md` |
| Working in a Blade-based project | `references/patterns/blade-monolith.md` |
| Working in a Laravel + Vue project | `references/patterns/vue-hybrid.md` |
| Working in a full-API project | `references/patterns/full-api.md` |
| Working in a Filament admin panel | `references/patterns/filament.md` |
| Version-specific syntax questions | `references/version-notes.md` |
| Curated code examples | `assets/snippets/` |

Pattern files contain conventions distilled from real production projects — treat them as equally binding as the base standards. If a pattern file conflicts with a base rule, the base rule (and anything in `manual-rules.md` / `workflow.md`) wins.

## Definition of Done (mandatory, every task)

A coding task is NOT complete until all of these pass. Run them, don't just mention them:

1. Before implementation, write focused unit tests from the requirements. Cover required behavior and supported boundary cases; skip speculative cases and test-only abstractions.
2. For every new API endpoint, add the smallest relevant endpoint Feature test when behavior crosses HTTP, middleware, validation, authorization, or persistence boundaries.
3. `./vendor/bin/pint` — fix all style issues (if Pint is not installed in the project, note it to the user and suggest adding it).
4. `./vendor/bin/phpstan analyse` (or `composer analyse` if defined) — resolve all findings at level 7 unless the project explicitly requires a different level.
5. Re-check the diff against `references/security.md` — no mass-assignment holes, no unvalidated input, no secrets in code.
6. Run focused unit/Feature tests after implementation (`php artisan test --filter=...`).

Before creating an API endpoint, confirm its audience: website (Sanctum SPA cookie auth), mobile (Sanctum Personal Access Token auth), or both. Ask when unspecified; group audience-specific endpoints under clear route, namespace, and middleware boundaries.

Report the result of each step briefly to the user. If a tool is unavailable in the environment, say so explicitly instead of silently skipping.

## Ownership markers

Reference files carry an `owner:` field in their frontmatter:
- `owner: human` — written by the team; never modified by automated ingestion.
- `owner: machine` — generated from project pattern extraction; updated via the team's internal ingestion workflow.

This distinction matters for maintainers, not for usage — when writing code, follow all files equally (human files win on conflict).
