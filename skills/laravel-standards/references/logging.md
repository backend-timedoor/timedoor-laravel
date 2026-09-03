---
owner: human
title: Logging
---

# Logging

How to shape a log call. For **what must and must not be logged** (audit events, redaction of secrets, log-injection neutralization) see [[security]] — that side is non-negotiable and takes precedence.

## Structured context, always

- Pass variable data in the context array, never interpolated into the message string. The message is a stable, greppable event name; the context carries the values.
- Use the `Log` facade (or `logger()`) directly. Do not build a custom logging wrapper speculatively.

**Correct:**
```php
Log::info('coupon claimed', [
    'membership_id' => $membership->id,
    'coupon_id' => $coupon->id,
    'store_id' => $store->id,
]);
```

**Incorrect:**
```php
Log::info("coupon {$coupon->id} claimed by {$membership->id}"); // values baked into the message
Log::info('created coupon'); // no context at all
```

## Level

- `error` — something failed that a human may need to act on.
- `warning` — a handled but notable condition (retry, fallback, degraded path).
- `info` — significant business events worth an audit trail.
- `debug` — development detail; must be silent at production log level.

Do not log an expected rejection (auth/validation failure) as `error` — see [[error-handling]].

<!-- Add new team logging rules below this line. Machine ingestion never touches this file. -->
