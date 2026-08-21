---
owner: human
title: PHP & Laravel Coding Standards
---

# Coding Standards

## Formatting

- Format all PHP with Laravel Pint, 4-space indentation (Pint's `laravel` preset default). Run `./vendor/bin/pint` after every PHP change and fix everything it reports before calling a task done — see [[workflow]].

## Naming

- Classes `PascalCase`, class methods/variables `camelCase`, config & DB columns `snake_case`, routes `kebab-case`.
- Global helper functions (in `app/Supports/helpers.php`) use `snake_case` — e.g. `auth_user()`, `get_pagination_limit()`. This is the one deliberate exception to camelCase; it marks "framework-level helper", not a class member.
- Controllers singular + `Controller` suffix: `AdminController`. Models singular: `User`. Tables plural: `users`.
- Match filename to class name exactly, one class per file (PSR-4 autoload depends on it).

**Correct:**
```php
class AdminController extends Controller
{
}
```

**Incorrect:**
```php
class Admin_Controller extends Controller // wrong case, wrong file will not autoload
{
}
```

## Structure

- Organize API code by domain area and version, mirrored across Controllers/Requests/Resources:
  ```text
  app/Http/Controllers/API/{Area}/V{n}/{Resource}/{Action}Controller.php
  app/Http/Requests/API/{Area}/V{n}/{Resource}/{Action}Request.php
  app/Http/Resources/API/{Area}/V{n}/{Resource}/{Purpose}Resource.php
  ```
  Example: `app/Http/Controllers/API/Admin/V1/Admin/AdminController.php`, `app/Http/Requests/API/Admin/V1/Admin/StoreRequest.php`.
- Keep domain models under `app/Modules/{Domain}/Models`, not `app/Models` — e.g. `app/Modules/User/Models/User.php`. Model-specific traits go in `app/Modules/{Domain}/Models/Concerns/`.
- Transform every API response through a `JsonResource` — never return a raw model or array pulled straight from Eloquent.
- Keep simple, single-step operations directly in the controller (a straightforward update/delete is fine there). Extract non-trivial business logic — multi-step writes, transactions, anything touching more than one model — into a Service or Action class. Don't build a Service for a one-line `$model->update()`.

**Correct (simple operation, stays in controller):**
```php
public function update(UpdateRequest $request): ProfileResource
{
    $user = auth_user();
    $user->updateOrFail($request->only(['name']));

    return ProfileResource::make($user);
}
```

**Correct (non-trivial, wrapped in a transaction inline is still acceptable for a single controller action, but extract to an Action once it grows or is reused):**
```php
public function update(UpdateRequest $request, User $admin): DetailAdminResource
{
    $admin = DB::transaction(function () use ($admin, $request) {
        $role = Role::whereSlug($request->input('role'))->get();
        $admin->fill($request->only(['name']));
        $admin->saveOrFail();
        $admin->syncRoles($role);

        return $admin;
    });

    return DetailAdminResource::make($admin);
}
```

## Functions & methods

- Max ~20 lines per method as a smell threshold; extract private methods or Actions beyond that.
- Always declare parameter types and return types.
- Early returns over nested conditionals; `abort_if()` for guard clauses instead of nested `if`.

## Eloquent

- Prevent N+1: eager-load with `with()`.
- Use `$fillable` (allow-list) on every model, never `$guarded = []`.
- Query scopes for reusable filters: `scopeActive`, `scopePublished`.
- Wrap multi-table writes in `DB::transaction()`; use `lockForUpdate()` for concurrent balance/counter updates.
- Prefer `saveOrFail()` / `updateOrFail()` / `deleteOrFail()` over the non-`OrFail` variants on API write paths, so a silent failure doesn't return a false "success".

## Validation & requests

- Every API endpoint that accepts input uses a FormRequest — no exceptions for single-field payloads.
- Pull data via `validated()` / `only([...])`, never `all()`, when creating/updating models.
- Validate server-side: allow-list values, enforce type/range/length/format, canonicalize before comparing (e.g. trim/lowercase email).
- `authorize()` in a FormRequest is request-level only (e.g. "is this shape of request even allowed"). Real authorization — ownership, permissions, roles — belongs in Policies, Gates, or middleware, not in FormRequest's `authorize()`.
- Custom validation rules live in `app/Rules`, class name `PascalCase`, written to be reusable across FormRequests. Example: `app/Rules/StrongPassword.php` used from both `Auth\Password\UpdateRequest` and `Admin\StoreRequest`.
- Use `prepareForValidation()` to normalize input before rules run (trim, cast types) rather than repeating normalization in the controller.
- Use `Rule::requiredIf()` / `$validator->sometimes()` for conditional field rules instead of branching in the controller.

**Correct:**
```php
class StoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:250'],
            'email'    => ['required', 'string', 'email:rfc,dns', 'max:250', 'unique:users'],
            'role'     => ['required', 'exists:roles,slug'],
            'password' => ['required', 'string', 'min:8', 'confirmed', new StrongPassword],
        ];
    }
}
```

**Incorrect:**
```php
public function store(Request $request)
{
    $admin = User::create($request->all()); // no FormRequest, mass-assignment risk
}
```

<!-- Add new team coding rules below this line. Machine ingestion never touches this file. -->
