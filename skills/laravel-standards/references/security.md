---
owner: human
title: Laravel Security Rules
---

# Security Rules

## Authentication & endpoint audience

- Use Sanctum SPA Authentication (cookie-based + CSRF) for website endpoints.
- Use Sanctum API Token Authentication (Personal Access Tokens) for mobile endpoints. Do not add a custom refresh-token flow to the base standard.
- Before creating an endpoint, explicitly determine and record its audience: website, mobile, or both. Ask this question when the request does not specify it.
- Group website/mobile-specific endpoints with clear route, namespace, and middleware boundaries, such as `API/Web/V1` or `API/Mobile/V1`.
- Require authentication on every non-public endpoint. Centralize authentication in middleware; fail closed.
- Regenerate the session ID after login, re-authentication, or privilege change. Fully invalidate the session on logout.
- Use `Secure` and `HttpOnly` cookies over HTTPS. Never place session IDs in URLs, errors, or logs.

## Authorization & access control

- Authorize every mutating and protected read action through middleware, Policies, or Gates. Authentication alone is not authorization.
- Base authorization on trusted server-side objects, not client-supplied role, ownership, or permission claims.
- Scope object queries to the authenticated user/tenant where ownership applies; prevent IDOR. Prefer `auth()->user()->orders()->findOrFail($id)` over `Order::findOrFail($id)` when resource ownership matters.
- Deny by default when authorization configuration is missing or unreadable. Never use the `Referer` header as the sole authorization check.
- Re-check authorization on every request, including background jobs, scripts, and direct API calls. Rate-limit sensitive transactions.

## Input, mass assignment & queries

- Validate all external input server-side through FormRequest using allow-lists, type/range/length/format checks, and canonicalization where needed.
- Persist only `$request->validated()` or an explicit `$request->only([...])`; never pass `$request->all()` to `create()`, `fill()`, or `update()`.
- Define `$fillable` on every model. `$guarded = []` is forbidden.
- Use Eloquent/query-builder bindings or parameterized SQL. Never interpolate input into SQL.
- Do not execute user-controlled input through `eval`, dynamic includes, shell commands, or other dynamic execution. Prefer managed framework APIs.

## Passwords, secrets & cryptography

- Hash passwords server-side with Laravel's `Hash` facade and a salted one-way hash. Never store or compare plaintext passwords outside the authentication check.
- Return generic authentication failures; do not reveal which credential field failed.
- Keep secrets only in `.env`, access them through `config()`, and never call `env()` in application code outside config files.
- Never hardcode passwords, API keys, tokens, connection strings, or encryption keys in source or client-side cleartext.
- Use cryptographically secure random values for tokens, randomized filenames, and security identifiers. Manage key rotation, expiry, storage, and destruction.

## Files & uploads

- Treat upload hardening as recommended project practice, not a universal mandatory rule: validate MIME type and magic bytes, enforce size limits, use randomized names, store outside webroot, disable execution, and malware-scan when infrastructure supports it.
- Never trust the client filename or extension. Never expose absolute server paths.
- Never pass user input into dynamic include functions. Authenticate upload actions before processing them.

## Sessions & transport

- Use Laravel's framework session controls. Keep inactivity timeouts short, invalidate sessions on logout, and regenerate IDs after login/re-authentication/privilege change.
- Use HTTPS for credentials and sensitive data. Validate certificate domain, expiry, and chain; never downgrade after TLS failure.
- Configure cookie scope narrowly. Use `Secure` and `HttpOnly`; use appropriate SameSite protection.
- Do not put sensitive data in GET parameters or outbound referrer URLs.

## Errors & audit logging

- Production 5xx responses must be generic. Never expose stack traces, file paths, SQL, session data, or internal exception details. Gate debug payloads behind `config('app.debug')`.
- Audit logging is mandatory. Log successful and failed authentication, authorization failures, tampering, invalid sessions, security-relevant exceptions, and sensitive admin changes with enough context for investigation (actor, action, target, outcome, timestamp, request correlation ID where available).
- Sensitive data is always forbidden in logs: passwords, API tokens, session IDs, cookies, secrets, encryption keys, full payment-card data, and raw credential payloads. Redact before logging.
- Neutralize untrusted values before writing them to logs to prevent log injection. Restrict log access and protect log integrity.

## Data & database

- Apply least privilege to application, database, and service accounts. Separate credentials by trust level where needed.
- Encrypt highly sensitive stored data. Purge sensitive temporary/cache copies promptly and support secure deletion where required.
- Remove default admin accounts, sample schemas, unused database functions, test code, and directory listings before production deployment.
- Keep source code and non-public resources outside downloadable/public paths.

<!-- Add new team security rules below this line. Machine ingestion never touches this file. -->
