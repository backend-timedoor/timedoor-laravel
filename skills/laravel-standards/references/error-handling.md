---
owner: human
title: Error Handling
---

# Error Handling

How exceptions are thrown, caught, and turned into responses. The response *shape* is defined in [[api-response]] — this file is about where that mapping lives and how exception code is structured.

## Throwing

- Throw a **typed exception** for a domain error — never a bare `throw new \Exception(...)`. Rethrowing an existing exception is fine.
- Use `ValidationException::withMessages([...])` for a business-rule failure that maps naturally onto a request field.
- Use `abort_if()` / `abort_unless()` for guard clauses (404/403) instead of nested conditionals.

## Rendering — centralize it

Map framework and generic exceptions (`ModelNotFoundException`, `AuthenticationException`, `ValidationException`, `AuthorizationException`) to the standard error envelope **once**, in the exception handler — not per controller.

- **[L10 and earlier]** register `renderable()` callbacks in `app/Exceptions/Handler.php::register()`.
- **[L11+]** register them in `bootstrap/app.php` via `->withExceptions(function ($exceptions) { ... })`.

```php
// [L10] app/Exceptions/Handler.php
$this->renderable(function (ModelNotFoundException $e, $request) {
    if ($request->expectsJson()) {
        return response()->json([
            'error' => [
                'code'  => 404,
                'title' => 'Url Not Found',
                'message' => class_basename($e->getModel()).' not found',
                'errors' => [],
            ],
        ], 404);
    }
});
```

Give a domain exception its own `render()` method only when its error shape genuinely differs from the standard envelope. Do not require every exception class to implement `render()` — that is boilerplate once the handler already covers the common case.

## Do not double-report

The global handler already reports uncaught exceptions. In a single `catch` block, pick **one**:

- rethrow for the handler to report and render, or
- swallow it with a single structured log line (see [[logging]]).

Never combine `report($e)` + `throw $e` + a manual `Log::error()` in the same block.

## Expected rejections are not bugs

An authorization or validation failure is an expected outcome, not something that should page anyone or fill error tracking. Add those to `$dontReport` (L10) / `->dontReport()` (L11+):

```php
protected $dontReport = [
    AuthorizationException::class,
    RoleDeniedException::class,
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
