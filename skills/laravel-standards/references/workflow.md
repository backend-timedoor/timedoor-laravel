---
owner: human
title: Mandatory Workflow Rules
---

# Workflow Rules (Definition of Done)

These are process rules, not syntax rules. They apply to EVERY coding task, regardless of size.

## After writing or editing any PHP code

1. **Run Laravel Pint**: `./vendor/bin/pint` (scope to changed files with `./vendor/bin/pint app/Path/File.php` for speed). Fix everything it reports.
   - *Why:* consistent formatting removes style debates from code review entirely.
2. **Run PHPStan**: `./vendor/bin/phpstan analyse` (respect the project's `phpstan.neon` level). Resolve all findings — do not suppress with `@phpstan-ignore` unless the user approves.
   - *Why:* static analysis catches type bugs before runtime; suppressions rot.
3. **Run relevant tests** if they exist: `php artisan test --filter=<Feature>`.
4. **Never declare a task finished** without reporting the outcome of steps 1–3.

## Before creating migrations

- Check existing migrations for naming and column conventions in this project first.
- Every foreign key gets an index; every `down()` must actually reverse `up()`.

## Git hygiene

- Suggest a conventional commit message for the change (e.g. `feat(order): add refund service`).
- Never commit `.env`, credentials, or generated files.

<!-- Add new team workflow rules below this line. Machine ingestion never touches this file. -->
