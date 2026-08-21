---
owner: human
title: API Response Standard
---

# API Response Standard

Every API response uses ONE envelope. No endpoint invents its own shape.

## Success

Single resource:
```json
{
  "data": {
    "id": 1,
    "name": "Jack Mi",
    "email": "jack@example.com",
    "photo": "https://ui-avatars.com/api/?name=Jack+Mi",
    "role": { "slug": "admin", "name": "Admin" }
  }
}
```
No `success` flag, no `message` field — presence of `data` (success) vs `error` (failure) is the signal.

HTTP codes: 200 (read/update), 201 (create).

Paginated list (Laravel's default paginated-Resource output, passed through unmodified):
```json
{
  "data": [ { "id": 1, "...": "..." } ],
  "links": {
    "first": "https://example.com/api/v1/admin?page=1",
    "last": "https://example.com/api/v1/admin?page=10",
    "prev": null,
    "next": "https://example.com/api/v1/admin?page=2"
  },
  "meta": {
    "current_page": 1,
    "per_page": 10,
    "last_page": 10,
    "total": 100
  }
}
```

Delete / logout / no-content success: `response()->json(status: 204)` — HTTP 204, empty body. Do not return `{"data": null}` for these.

## Error

```json
{
  "error": {
    "code": 422,
    "title": "Validation Error",
    "message": "The given data was invalid.",
    "errors": [
      { "key": "email", "message": "The email field is required." }
    ]
  }
}
```

`errors` is an array of `{key, message}` objects, one entry per failed field/rule — not an object keyed by field name.

### Error titles (`mapErrorTitle()`)

Extend this map as new status codes appear in the API; don't fall through to "Server Error" for a code you actually handle deliberately.

| Code | Title |
|------|-------|
| 400  | Bad Request |
| 401  | Authentication Failed |
| 403  | Access Forbidden |
| 404  | Url Not Found |
| 405  | Method Not Allowed |
| 408  | Request Timeout |
| 409  | Conflict |
| 413  | Payload Too Large |
| 415  | Unsupported Media Type |
| 422  | Validation Error |
| 429  | Too Many Requests |
| 500 (default) | Server Error |

## Security: production error output

Production 5xx responses MUST NOT leak stack traces, file paths, or internal exception detail. Gate any debug payload behind `config('app.debug')`. What ships in production is always the generic envelope:
```json
{ "error": { "code": 500, "title": "Server Error", "message": "Something is wrong. Be patient.", "errors": [] } }
```
See [[security]] for the broader non-negotiable on this.

## Implementation rules

- Centralize this via a single `ExceptionFormatter` trait/concern consumed by the app's exception `Handler` — never per-controller try/catch building error JSON by hand.
- Transform models ONLY through API Resources (`JsonResource`) — never return a raw model or array pulled straight off Eloquent.
- Register exception rendering so validation/auth/404/permission errors automatically produce the error envelope above with no controller-level code.

<!-- Add new team API response rules below this line. Machine ingestion never touches this file. -->
