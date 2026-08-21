---
owner: human
title: Code Security Rules
---

# Security Rules

> TEMPLATE: merge your real security document into these sections.

## Input & mass assignment

- All input through FormRequest validation. Whitelist fields with `validated()` — never `$request->all()` into `create()`/`update()`.
- `$fillable` allow-list on every model. `$guarded = []` is forbidden.

## Queries

- No string interpolation in SQL. Raw queries use bindings: `DB::select('... where id = ?', [$id])`.

## Auth & authorization

- Authorize every mutating action via Policies or Gates — checking "is logged in" is not authorization.
- Never trust client-supplied IDs for ownership: `auth()->user()->orders()->findOrFail($id)`, not `Order::findOrFail($id)`.

## Secrets & config

- Secrets only in `.env`, accessed via `config()`. Never `env()` outside config files (breaks config caching, hints at misplaced secrets).
- Never log tokens, passwords, or full card numbers. Redact before logging.

## Files & uploads

- Validate mime + size server-side. Store outside webroot or with randomized names. Never trust the client filename.

## Output

- Blade `{{ }}` (escaped) by default; `{!! !!}` only for content already sanitized server-side, with a comment explaining why.
