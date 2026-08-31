---
owner: machine
title: Patterns — full-api
---

# Patterns: full-api

## Action classes located under `app/Modules/{Domain}/Actions` [L10+]
Rule: Extract non-trivial business logic (multi-step writes, transactions, cross-model operations) into a dedicated Action class under `app/Modules/{Domain}/Actions`, one action per file. Name it `<Verb><Entity>Action`; event/webhook consumers may instead use `<Event>Handler` to signal they're triggered by an external event, not a direct call. Use an instance method named `execute()` as the Action entry point.
Why: keeps controllers thin/testable and colocates business logic with its domain module rather than scattering it across generic `app/Services`; reinforces existing human rule "one public method per Action class" with a concrete file-location and call-convention standard.
Evidence: ~49 Action classes across 13 domain modules (Article, Customer, Assessments, AddOn, Cluster, Enterprise, FileUpload, History, Newsletter, Order, VideoCertification), verified against source in proj-a.
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
Why: decouples Action classes from the HTTP layer so they're unit-testable without mocking `Request`; single, explicit place where "what does a valid write look like" is defined.
Evidence: 9+ occurrences across Assessment, AddOn, Customer, CMS, VideoCertification, EnterprisePackage, Article, Cluster, and Order request classes, verified against source in proj-a.
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

    public function data(): OrderData
    {
        return new OrderData(
            name: $this->string('name'),
            price: $this->float('price'),
        );
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
