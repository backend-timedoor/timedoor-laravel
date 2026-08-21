---
owner: human
title: Team Manual Rules
---

# Manual Coding Rules

Rules written directly by the team. These OVERRIDE anything in `patterns/` on conflict.
Format for each rule: imperative statement → short reason → (optional) example.

## Placeholder — replace with your real rules

- **Name booleans as questions.** `is_active`, `has_paid`, `can_refund` — never `active_flag`.
  *Why:* reads naturally in conditions: `if ($order->has_paid)`.

- **One public method per Action class; services group related methods.**
  *Why:* Actions stay testable and single-purpose; services avoid class explosion.

<!-- Add rules via your internal /add-rule command or by hand. Machine ingestion never touches this file. -->
