---
owner: human
title: Dependency Injection & Composition
---

# Dependency Injection & Composition

How classes receive their collaborators. Pairs with the Service/Action split in [[coding-standards]].

## Constructor injection

- Inject **stable collaborators** through the constructor: services, API clients, policies, repositories the project already uses.
- Use constructor property promotion `[PHP 8.0+]`, `private readonly` `[PHP 8.1+]`. Declare the property explicitly on older PHP.
- Let the container auto-resolve concrete classes — do not register a binding for every class.

```php
final class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly OtpService $otpService,
    ) {
    }
}
```

## Per-call data goes to the method, not the constructor

Request payloads, job arguments, and any value that changes per invocation are method parameters — never constructor state on a long-lived service. This keeps the container resolution reusable and tests simple.

```php
// Correct
public function preview(PreviewService $previewService, PreviewRequest $request): PreviewResource
{
    return PreviewResource::make($previewService->run($request->validated()));
}
```

An Action that carries per-operation state does so as *promotion + an `execute()` method* — the operation data still arrives in `execute()`, not the container:

```php
final class CreateExamQuestion
{
    public function __construct(private readonly Exam $exam) {}

    public function execute(array $questionGroupIds): void
    {
        // request/job data belongs here
    }
}
```

## Interfaces

Bind an interface only when there is a real seam: multiple implementations, a genuine swap boundary, or an external integration that must be isolated for testing.

```php
$this->app->bind(PaymentGateway::class, StripePaymentGateway::class);
```

An interface with one implementation and no swap/test boundary is noise — inject the concrete class.

## Method injection

Use method (action) injection only for a dependency used by exactly one controller action. Anything used by two or more actions goes in the constructor.

## Never

- Setter injection.
- `app()` / `resolve()` calls inside methods to fetch collaborators — resolve in the constructor. A service must not resolve its own dependencies with `app()`.
- Static property containers or service locators.
- Injecting `Request`, raw user input, or mutable operation data into a long-lived service.
- Refactoring unrelated classes to add or remove `readonly` just to enforce a preference — match the surrounding code on incremental changes.

## Testing

Concrete dependencies stay auto-resolvable. Replace only the external boundary — with a container binding or a mock — in the test that needs it.

<!-- Add new team DI rules below this line. Machine ingestion never touches this file. -->
