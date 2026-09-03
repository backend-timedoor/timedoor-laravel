---
owner: human
title: Error Handling
---

# Error Handling

How exceptions are thrown, caught, and turned into responses. The error envelope keys, titles, and HTTP codes are defined in [[api-response]] and are not repeated here — this file is about *where* rendering is wired and how exception code is structured.

## Throwing

- Throw a **typed exception** for a domain error — never a bare `throw new \Exception(...)`. Rethrowing an existing exception is fine.
- Use `ValidationException::withMessages([...])` for a business-rule failure that maps naturally onto a request field.
- Use `abort_if()` / `abort_unless()` for guard clauses (404/403) instead of nested conditionals.

## Rendering — one formatter, wired in the handler

Every exception that reaches the client is turned into the [[api-response]] error envelope by **one shared `ExceptionFormatter`** (trait/concern) consumed by the exception handler. Handler mappings call the formatter; they never assemble the `error` array by hand, and controllers never build error JSON at all — see [[api-response]].

Wire the mappings for framework exceptions (`ModelNotFoundException`, `AuthenticationException`, `ValidationException`, `AuthorizationException`, `NotFoundHttpException`) once:

- **[L10 and earlier]** — `renderable()` callbacks in `app/Exceptions/Handler.php::register()`.
- **[L11+]** — `->withExceptions(function ($exceptions) { ... })` in `bootstrap/app.php`.

```php
// [L10] app/Exceptions/Handler.php — delegate, never hand-build the envelope
$this->renderable(function (ModelNotFoundException $e, $request) {
    if ($request->expectsJson()) {
        return $this->errorResponse($e, status: 404); // ExceptionFormatter trait method — shape from [[api-response]]
    }
});
```

Route every exception through the formatter via a handler mapping. A per-exception `render()` method is a last resort — only when the exception's HTTP semantics can't be expressed as a handler mapping — and it must still emit the [[api-response]] envelope through the same formatter. Do not give every exception class its own `render()`.

## Do not double-report

The global handler already reports uncaught exceptions. In a single `catch` block, pick **one**:

- rethrow for the handler to report and render, or
- swallow it with a single structured log line (see [[logging]]).

Never combine `report($e)` + `throw $e` + a manual `Log::error()` in the same block.

## Expected rejections are not bugs

An authorization or validation failure is an expected outcome, not something that should page anyone or fill error tracking. Laravel already skips reporting for its own `ValidationException` and `AuthorizationException` — add your **custom** expected-rejection exceptions to the don't-report list:

```php
// [L10] app/Exceptions/Handler.php — use ->dontReport(...) in bootstrap/app.php on [L11+]
protected $dontReport = [
    RoleDeniedException::class,
    PermissionDeniedException::class,
];
```

## Dead code after `throw`

A `return` or any statement placed after an unconditional `throw` (or after an earlier unconditional `return`) is unreachable — treat it as a bug to remove on sight; it signals a bad copy-paste or an unfinished refactor.

```php
// Wrong — the return is dead
throw new VoucherImplementException($message);
return false;
```

## Production output

Production 5xx responses must never leak stack traces, file paths, SQL, or internal exception detail. Gate any debug payload behind `config('app.debug')`. See [[security]] and [[api-response]].

<!-- Add new team error-handling rules below this line. Machine ingestion never touches this file. -->
