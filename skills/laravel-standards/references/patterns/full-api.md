---
owner: machine
title: Patterns — full-api
---

# Patterns: full-api

## Domain module folder layout [L7+]
Rule: Within each existing `app/Modules/{Domain}/` module, keep domain-specific Events, Listeners, Jobs, QueryBuilder, and DataTransferObjects together under same domain folder. Put DTO factories under `DataTransferObjects/Factories/`. A module is movable or deletable as one unit. Cross-cutting code (Http, Supports, Rules) stays outside. Canonical DTO folder name is `DataTransferObjects`, not `DTO`.

Why: fills undefined placement for Events, Listeners, Jobs, QueryBuilder, and DTOs without duplicating existing Models, Actions, Enums, or Services rules; keeps domain code self-contained for moves and deletions.

This rule extends existing module-location rules; it does not replace them.

Evidence: ~20 domains each carrying the full subfolder set, verified against source in proj-j (L8). Source root was `app/Services/{Domain}` — not adopted; the prescribed `app/Modules` root follows the human rule instead of the source. One source domain used `DTO/` as the folder name — not adopted; `DataTransferObjects` is canonical.
Example:
```text
app/Modules/Group/
    Models/
    Actions/
    Enums/
    Events/
    Listeners/
    Jobs/
    QueryBuilder/
    DataTransferObjects/
        GroupData.php
        Factories/GroupDataFactory.php
```

## Action classes located under `app/Modules/{Domain}/Actions` [L10+]
Rule: Extract non-trivial business logic (multi-step writes, transactions, cross-model operations) into a dedicated Action class under `app/Modules/{Domain}/Actions`, one action per file. Name it `<Verb><Entity>Action`; event/webhook consumers may instead use `<Event>Handler` to signal they're triggered by an external event, not a direct call. Use an instance method named `execute()` as the Action entry point.
Why: keeps controllers thin/testable and colocates business logic with its domain module rather than scattering it across generic `app/Services`; reinforces existing human rule "one public method per Action class" with a concrete file-location and call-convention standard.
Evidence: ~49 Action classes across 13 domain modules (Article, Customer, Assessments, AddOn, Cluster, Enterprise, FileUpload, History, Newsletter, Order, VideoCertification), verified against source in proj-a. Location and the `execute()` entry point were corroborated by 3 Action classes in proj-g (L12); proj-g names two `EntityAction` rather than `VerbEntityAction`, so that naming deviation is not adopted. Proj-g also wraps a single `create()` call in an Action, which existing coding standards forbid; corroboration covers location and entry point only.
Example:
```php
// app/Modules/Order/Actions/UpsertOrderAction.php
class UpsertOrderAction
{
    public function __construct(private ?Order $order = null) {}

    public function execute(UpsertOrderData $data): Order
    {
        $this->order ??= new Order();
        $this->order->fill($data->toArray());
        $this->order->saveOrFail();

        return $this->order;
    }
}
```

## FormRequest exposes `data()` mapping to a DTO [L10+]
Rule: For writes consumed by an Action class, add a `data(): SomeData` method to the FormRequest that maps validated input into a typed DTO, and pass that DTO (not the Request) into the Action. Every field read in `data()` must also appear in `rules()` — don't call `$this->string()/->integer()/->float()` on a field the FormRequest doesn't validate; that would silently bypass the allow-list guarantee `validated()` normally gives you.
When using `spatie/laravel-data`, build the DTO in one shot from the validated array: `return SomeData::from($this->validated());`. Never write `SomeData::from($this)` or `SomeData::from($request)`: those variants read the entire unvalidated payload. Keep the method named `data()`.
Why: decouples Action classes from the HTTP layer so they're unit-testable without mocking `Request`; single, explicit place where "what does a valid write look like" is defined; constructing from `validated()` makes the allow-list guarantee structural.
Evidence: 9+ occurrences across Assessment, AddOn, Customer, CMS, VideoCertification, EnterprisePackage, Article, Cluster, and Order request classes, verified against source in proj-a. Corroborated by 3 FormRequest/controller pairs using `SomeDto::from($this->validated())` with spatie/laravel-data v4 in proj-g (L12).
Example:
```php
class UpdateOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name'  => ['required', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:1'],
        ];
    }

    // Build from validated() only. Never ::from($this) / ::from($request).
    public function data(): OrderData
    {
        return OrderData::from($this->validated());
    }
}

// controller
public function update(UpdateOrderRequest $request, Order $order): OrderDetailResource
{
    $order = DB::transaction(fn () => (new UpdateOrderAction($order))->execute($request->data()));

    return OrderDetailResource::make($order);
}
```

## Action-scoped permission middleware [L10, pre-L11 middleware-registration style]
Rule: Declare each API action's permission middleware in the controller constructor, scoped with `only(...)` per action. Prevents accidental permission reuse across endpoints.
Why: makes authorization explicit at each endpoint boundary and limits permission middleware to intended actions.
Evidence: repeated in 3+ API controllers, verified against source in proj-b.
Note: Laravel 11+ replaces constructor `$this->middleware()` with the `HasMiddleware` interface; use this exact form only on Laravel 10 and earlier.
Example:
```php
class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:products.view')->only('index');
        $this->middleware('permission:products.update')->only('update');
    }
}
```

## Allowlisted, eager-loaded API query pipelines [L10+]
Rule: Compose API list queries with `QueryBuilder::for()`, explicit `allowedFilters([...])`, required `with([...])`, and `paginate()`, then return a `JsonResource` collection.
Why: filter allow-lists constrain client-controlled queries, eager loading prevents N+1 queries, and pagination bounds response and database work.
Evidence: repeated across 3+ API list controllers, verified against source in proj-b.
Example:
```php
$products = QueryBuilder::for(Product::class)
    ->allowedFilters(['status'])
    ->with('owner')
    ->paginate();

return ProductResource::collection($products);
```

## Per-domain route file split, required from area index [L10+]
Rule: Split each API area's routes into one file per domain resource (`routes/api/{area}/{domain}.php`), then `require` them from the area's index route file instead of one large routes file.
Why: keeps the route table navigable as domain count grows and reduces merge conflicts; one file maps to one bounded context.
Evidence: recurring across admin and web route trees (auth, customer, article, assessment, order, cms, enterprise, addon + web/shared/individual/enterprise/utility), verified against source in proj-a.
Example:
```php
// routes/api/admin/v1.php
require __DIR__.'/auth.php';
require __DIR__.'/customer.php';
require __DIR__.'/order.php';
```

## Protected route groups for cross-cutting middleware [L7+]
Rule: Wrap protected API domains in a route group carrying authentication middleware (and any other cross-cutting concern middleware, e.g. audit/activity logging) scoped to that group; leave public routes outside any such group. Don't attach the same middleware endpoint-by-endpoint. Pair every `prefix()` with a matching `name()` prefix so route names stay collision-free across audience trees.
Why: Boundary-level middleware centralizes fail-closed access checks and prevents endpoint-level middleware drift where a new route is added without authentication; mirrored name prefixes keep `route()` lookups unambiguous when several audiences expose the same domain.
Evidence: 6 occurrences across versioned API route files and the web route file, verified against source in proj-c; ~100 grouped routes across two audience route files in proj-d, confirming the shape holds below L10.
Example:
```php
Route::middleware(['auth:user', 'activity']) // 'activity' stands in for project-specific cross-cutting middleware; not a required alias.
    ->prefix('products')
    ->name('products.')
    ->group(function (): void {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::post('/', [ProductController::class, 'store'])->name('store');
    });
```
Snippet candidate: yes

## Grouped read services with server-derived scope [L5.7+]
Rule: Group read queries of one bounded reporting domain into a single service class under `app/Modules/{Domain}/Services`, pass shared scope once through the constructor as a filter object, and expose one typed public method per result. Derive scope from the authenticated user or tenant server-side — never raw request input. Keep one service per bounded domain, not a shared `StatisticService`; memoize results reused by multiple methods.
Why: keeps read logic out of controllers and unit-testable without HTTP; one scope object removes repeated filter plumbing and centralizes tenant scoping, so new queries cannot silently omit it. Complements write-side Action classes and the existing rule that services group related methods.
Evidence: five sibling read services in one reporting module, verified against source in proj-e. Single module means low confidence; re-confirm on next api,blade ingestion.
Note: constructor property promotion requires PHP 8; declare the property explicitly on Laravel 6 and other legacy projects.
Example:
```php
// app/Modules/Reporting/Services/ProductReportService.php
class ProductReportService
{
    /** @var ReportScope */
    private $scope;

    /** @var Collection|null */
    private $chartData;

    public function __construct(ReportScope $scope)
    {
        $this->scope = $scope;
        $this->chartData = null;
    }

    public function chartData(): Collection
    {
        return $this->chartData ??= Product::query()
            ->whereIn('owner_id', $this->scope->ownerIds())
            ->whereBetween('created_at', [$this->scope->from(), $this->scope->to()])
            ->get();
    }

    public function total(): int
    {
        return $this->chartData()->count();
    }
}

// Build scope from the authenticated user, not raw input.
$service = new ProductReportService(
    ReportScope::forUser(auth()->user(), $request->validated())
);

return response()->json(['data' => [
    'chart' => $service->chartData(),
    'total' => $service->total(),
]]);
```
Snippet candidate: no

## Bound client-controlled page size server-side [L7+]
Rule: Never pass a raw request value into `paginate()`. Clamp the client's page size to a server-defined maximum and fall back to a default when the value is missing or invalid. If the index endpoint already has a FormRequest, validate `per_page` there (`['integer', 'min:1', 'max:100']`) — that is the primary form. For index endpoints with no other input, use one shared global helper in `app/Supports/helpers.php` so the bound cannot drift per controller. Keep the maximum in config, not as a literal.
Why: an oversized page request is an unauthenticated memory/CPU exhaustion vector and an N+1 amplifier on eager-loaded lists. A single clamp point makes the ceiling auditable in one place rather than re-derived in every controller.
Evidence: 13 controller call sites of one shared helper across 6 domain areas, verified against source in proj-g. Single project; re-confirm on next full-api ingestion.
Snippet candidate: no

## Domain enums live beside their module, string-backed [L10+]
Rule: Put domain enums in `app/Modules/{Domain}/Enums/{Name}Enum.php`, alongside that module's Models/Actions/DTOs — not in a global `app/Enums`. Back them with `string`, not `int`, and cast the column via the model's `casts()`.
Why: mirrors the existing rule that domain models live under `app/Modules/{Domain}/Models`, so a module stays deletable or movable as one unit. String backing keeps stored values self-describing and survives reordering of cases, which integer backing does not.
Evidence: 11 enums across 4 domain modules, verified against source in proj-g. Single project; re-confirm on next full-api ingestion.
Snippet candidate: no

## Gateway status mapped to internal enum at integration boundary [L9+]
Rule: Cast vendor webhook payloads into a DTO whose status field is a gateway-backed enum, then translate it to the internal status enum with an exhaustive `match` — including a `default` arm — inside the domain service. Internal code never reads vendor status values directly.
Why: vendor status vocabularies change without notice; one translation point keeps the blast radius of a new or renamed vendor status to a single file, and internal enums stay free of vendor naming. Without a `default` arm, an unrecognized vendor status throws `UnhandledMatchError` mid-webhook.
Evidence: status translation in payment service and sibling subscription service, 2 call sites, verified against source in proj-i; gateway status enum and internal status enum are separate types in both.
Note: `match` requires PHP 8.0+. The DTO may be a plain readonly class or `spatie/laravel-data` — the rule is the single translation point, not the DTO library.
Example:
```php
$payment->status = match ($data->status) {
    GatewayPaymentStatus::EXPIRED => PaymentStatus::FAILED,
    GatewayPaymentStatus::PENDING => PaymentStatus::PENDING,
    GatewayPaymentStatus::PAID    => PaymentStatus::SUCCESS,
    default => throw new UnexpectedValueException(
        "Unhandled gateway status: {$data->status->value}"
    ),
};
```

## Raw external payload snapshots on a dedicated private disk [L9+]
Rule: Before processing a gateway/external-system callback, persist the raw payload as a JSON file to a dedicated filesystem disk with `'visibility' => 'private'` under `storage_path('app/private/...')`, keyed by `Ym/date` plus a sanitized reference. Call `Storage::disk(...)` directly at the call site — no per-disk wrapper class. Do not write raw payload snapshots into application log channels.
Why: payload snapshots exist for dispute resolution and replay, not log search — they belong in files on a private disk, not interleaved with app logs; private visibility keeps them off any public path. A wrapper class per disk wraps a one-line call; add one only when call sites multiply.
Evidence: 5 private disks in config/filesystems.php and payload snapshot writes in 2 gateway-related log classes, verified against source in proj-i. Per-disk wrapper classes were observed but not adopted — 2 call sites do not justify them.
Note: sanitize any externally derived value used in the path (e.g. webhook reference IDs) before building the filename.
Example:
```php
// config/filesystems.php
'payment-callbacks' => [
    'driver' => 'local',
    'root' => storage_path('app/private/payment-callbacks'),
    'visibility' => 'private',
],

Storage::disk('payment-callbacks')->put(
    now()->format('Ym/d').'/'.$reference.'.json',
    json_encode($payload, JSON_THROW_ON_ERROR),
);
```

## Abstract base notification owns the channel [L7+]
Rule: For each custom notification channel, define one abstract base Notification that pins `via()` to that channel (and holds any shared message construction, e.g. a payload builder trait); concrete notifications extend it and override only content methods.
Why: channel choice is decided once per channel, not per notification — a new notification cannot silently add or drop a channel, and shared channel plumbing has one home.
Evidence: FCM base notification with uniform via() and 3+ content-only subclasses, verified against source in proj-i. Single project; re-confirm on next ingestion that touches notifications.
Example:
```php
abstract class FcmNotification extends Notification
{
    public function via($notifiable): array
    {
        return [FcmChannel::class];
    }

    abstract public function title(): string;
    abstract public function body(): string;
    abstract public function data(): array;
}
```

## Gateway webhook endpoints: signature verification gates the FormRequest exception [L9+]
Rule: A gateway webhook endpoint is the one sanctioned exception to the "every input endpoint uses a FormRequest" rule — and only when the handler verifies the vendor's signature as its first action (HMAC or SDK verifier per vendor spec) and rejects unsigned/mismatched payloads before any parsing. Verification failure returns an error response, never processes the payload. Raw-body DTO parsing after a verified signature is acceptable in place of validation rules.
Why: gateway callbacks arrive from vendor infrastructure, not app users — auth guards and field allow-lists don't fit vendor-shaped payloads, and rewriting the raw body through validation rules can corrupt signature-relevant bytes. But "vendor sends it" is not trust: without signature verification the endpoint accepts forged payment state. Verification-first is what makes the exception safe.
Evidence: human decision at proj-i ingestion review (2026-09-03), amending the FormRequest rule with this carve-out. The ingested source itself had a callback endpoint with neither FormRequest nor signature verification — flagged and rejected as-is; this pattern prescribes the safe form rather than adopting what was observed.
Note: the human-owned rule file still reads "no exceptions"; this pattern is the recorded carve-out. Verify the signature over the exact raw request body, before any middleware or parsing that could alter it.
Example:
```php
public function __construct(
    private PaymentCallbackHandler $handler,
) {
}

public function __invoke(Request $request): JsonResponse
{
    $signature = $request->header('X-Callback-Token');

    if (! hash_equals(config('services.gateway.webhook_token'), (string) $signature)) {
        Log::warning('Webhook signature mismatch', ['ip' => $request->ip()]);
        abort(403);
    }

    $dto = PaymentCallbackDTO::from($request->getContent()); // raw body, post-verification
    $this->handler->handle($dto);

    return response()->json();
}
```

## Per-model typed QueryBuilder via `newEloquentBuilder()` [L8+]
Rule: When a model accumulates several reusable filters, attach typed query builder by overriding `newEloquentBuilder($query)` and returning class extending `Illuminate\Database\Eloquent\Builder`, placed at `app/Modules/{Domain}/QueryBuilder/XxxQueryBuilder.php`. Give it fluent `where...(): static` methods comparing enum-backed columns by enum case, then return `$this`. Keep methods within model's tables and relations; never reach into another domain's internals.

Why: typed, chainable, IDE-discoverable filter API beats growing model scope pile; enum comparisons remove magic status strings from call sites.

Evidence: 26 models override `newEloquentBuilder()` with per-domain builders across ~20 domains, verified against source in proj-j (L8). Source cross-domain filter deliberately not adopted.

Note: local scopes (`scopeActive`) remain default for simple one-line filters. Use typed builder only for composed filters (`whereHas` chains, time-window logic) or filter sets large enough that static typing pays off. Not superseded by `spatie/laravel-query-builder`: that package allow-lists request-driven filters; typed methods are hand-written domain filters, and both compose. Declare native `: static` return type; docblock-only fails PHPStan level 7. On PHP 8.1+, pass native enum cases; legacy Laravel 8 may use compatible enum implementations.
Example:
```php
// app/Modules/Invoice/QueryBuilder/InvoiceQueryBuilder.php
class InvoiceQueryBuilder extends Builder
{
    public function whereIsPaid(): static
    {
        return $this->where('status', InvoiceStatus::Paid);
    }

    public function whereIsNotCanceled(): static
    {
        return $this->where('status', '!=', InvoiceStatus::Canceled);
    }
}

// app/Modules/Invoice/Models/Invoice.php
public function newEloquentBuilder($query): InvoiceQueryBuilder
{
    return new InvoiceQueryBuilder($query);
}

Invoice::query()->whereIsPaid()->whereIsNotCanceled()->get();
```

## DTO factories own model-to-DTO normalization [L8+]
Rule: When a DTO needs construction normalization (enum coercion, date casting, relation flattening) or has more than one non-HTTP source, put construction in dedicated `XxxDataFactory` under `app/Modules/{Domain}/DataTransferObjects/Factories/`, with static constructors such as `fromModel(Xxx $model): XxxData`. Factory owns coercion; DTO stays value object. HTTP input is not factory source — request-to-DTO mapping stays in FormRequest `data()` built from `validated()`; never let factory read Request directly.

Why: one home for normalization keeps controllers and importers free of coercion logic without competing with FormRequest `data()` rule for request mapping.

Evidence: 15 factory classes against ~40 DTOs across several domains, verified against source in proj-j (L8, spatie/data-transfer-object v2.8). That package is archived/EOL; on Laravel 9+ / PHP 8.1+ prefer native readonly DTOs or `spatie/laravel-data`. "Factory" means DTO construction, not Laravel database factories; never place these in `database/factories`.
Example:
```php
// app/Modules/Group/DataTransferObjects/Factories/ClassDataFactory.php
class ClassDataFactory
{
    public static function fromModel(Group $group): ClassData
    {
        return new ClassData([
            'name'         => $group->name,
            'status'       => $group->status, // enum, not raw string
            'start_date'   => $group->start_date->toDateString(),
            'meeting_days' => $group->meetingDays->toArray(),
        ]);
    }
}
```
