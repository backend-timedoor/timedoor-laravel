---
owner: human
title: Mandatory Workflow Rules
---

# Workflow Rules (Definition of Done)

These are process rules, not syntax rules. They apply to EVERY coding task, regardless of size.

## After writing or editing any PHP code

1. **Run Laravel Pint**: `./vendor/bin/pint` (scope to changed files with `./vendor/bin/pint app/Path/File.php` for speed). Fix everything it reports.
   - *Why:* consistent formatting removes style debates from code review entirely.
2. **Run PHPStan at level 7**: `./vendor/bin/phpstan analyse` with `level: 7` in `phpstan.neon`. Resolve all findings — do not suppress with `@phpstan-ignore` unless the user approves.
   - *Why:* static analysis catches type bugs before runtime; suppressions rot.
3. **Run relevant tests** if they exist: `php artisan test --filter=<Feature>`.
4. **Write unit tests from the requirements before writing implementation code.** Cover every behavior and boundary case supported by the requirements, but avoid speculative cases, duplicate assertions, and test-only abstractions.
   - *Why:* executable requirements expose gaps before implementation and prevent over-engineered code.
5. **Run the relevant unit/feature tests after implementation** — for API endpoints, include the endpoint Feature test when behavior crosses HTTP, middleware, validation, authorization, or persistence boundaries.
6. **Never declare a task finished** without reporting the outcome of Pint, PHPStan, and tests.

**Test-first sequence:**
1. Translate requirements into focused test cases.
2. Write failing tests.
3. Write minimum implementation.
4. Run Pint, PHPStan level 7, and relevant tests.
5. Remove speculative tests and abstractions that requirements do not support.

## Before creating an API endpoint

- Determine and confirm the endpoint's audience before writing code: website (Sanctum SPA cookie auth), mobile (Sanctum API token auth), or both. If the request doesn't specify, ask.
  - *Why:* audience decides the auth guard, middleware group, and route namespace — getting it wrong means rework or a security gap. See [[security]].
- Group audience-specific endpoints under a clear namespace/middleware, e.g. `API/Web/V1` or `API/Mobile/V1`.

## Before creating migrations

- Check existing migrations for naming and column conventions in this project first.
- Every foreign key gets an index; every `down()` must actually reverse `up()`.

## Git hygiene

- Suggest a conventional commit message for the change (e.g. `feat(order): add refund service`).
- Never commit `.env`, credentials, or generated files.

<!-- Add new team workflow rules below this line. Machine ingestion never touches this file. -->
