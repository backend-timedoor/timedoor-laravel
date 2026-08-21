---
owner: human
title: API Response Standard
---

# API Response Standard

> TEMPLATE: replace envelope/fields with your real standard.

Every API response uses ONE envelope. No endpoint invents its own shape.

## Success

```json
{
  "success": true,
  "message": "Product created",
  "data": { "id": 1, "name": "Sample" }
}
```
HTTP codes: 200 (read/update), 201 (create), 204 (delete, empty body allowed).

## Error

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": { "name": ["The name field is required."] }
}
```
HTTP codes: 400 bad request, 401 unauthenticated, 403 forbidden, 404 not found, 422 validation, 500 server (never leak stack traces in production).

## Pagination

```json
{
  "success": true,
  "data": [ ... ],
  "meta": { "current_page": 1, "per_page": 15, "total": 45, "last_page": 3 }
}
```

## Implementation rules

- Centralize via a helper/trait (e.g. `ApiResponse::success()`, `ApiResponse::error()`) — see `assets/snippets/api-response-helper.php`.
- Transform models ONLY through API Resources — never return raw models (leaks hidden columns when they change).
- Register exception rendering so validation/auth/404 errors automatically use the error envelope.
