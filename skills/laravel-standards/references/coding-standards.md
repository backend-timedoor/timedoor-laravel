---
owner: human
title: PHP & Laravel Coding Standards
---

# Coding Standards

> TEMPLATE: replace/extend each section with your team's real standards document.
> Keep the format: rule (imperative) → why → correct vs incorrect example.

## Naming

- Classes `PascalCase`, methods/variables `camelCase`, config & DB columns `snake_case`, routes `kebab-case`.
- Controllers singular + `Controller` suffix: `ProductController`. Models singular: `Product`. Tables plural: `products`.

## Structure

- Directory roles: `app/Http/Controllers` (thin), `app/Services` (business logic), `app/Actions` (single-purpose operations), `app/Http/Requests` (validation), `app/Http/Resources` (API transformation).

**Correct:**
```php
class ProductController extends Controller
{
    public function store(StoreProductRequest $request, ProductService $service): JsonResponse
    {
        $product = $service->create($request->validated());
        return ApiResponse::created(new ProductResource($product));
    }
}
```

**Incorrect (business logic in controller, inline validation):**
```php
public function store(Request $request)
{
    $request->validate(['name' => 'required']);
    $product = new Product();
    $product->name = $request->name;
    // ...30 more lines of logic
}
```

## Functions & methods

- Max ~20 lines per method as a smell threshold; extract private methods or Actions beyond that.
- Always declare parameter types and return types.
- Early returns over nested conditionals.

## Eloquent

- Prevent N+1: eager-load with `with()`; enable `Model::preventLazyLoading()` in non-production.
- Use `$fillable` (allow-list), never `$guarded = []`.
- Query scopes for reusable filters: `scopeActive`, `scopePublished`.
