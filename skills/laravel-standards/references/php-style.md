---
owner: human
title: PHP Language Style (PSR-12)
---

# PHP Language Style

Language-level style below the framework layer. Follows **PSR-12 Extended Coding Style** and PHP 8.x practice. For class/controller/model/route naming and file structure see [[coding-standards]]; for framework patterns (FormRequest, Eloquent, Resources) see [[coding-standards]] and the `patterns/` files.

Laravel Pint (`laravel` preset) owns all whitespace, operator spacing, and brace placement — run it after every change and accept what it reports (see [[coding-standards]] and [[workflow]]). Do not hand-format against Pint. This file covers only what Pint does **not** decide: `declare(strict_types=1)`, identifier naming, string/quote/interpolation choices, type-system usage, and class element order. If the team wants a stricter whitespace rule (e.g. aligned `=>`), that goes in `pint.json`, not here.

Apply the PHP 8.x items only where the project's PHP version supports them — detect it from `composer.json` first (see [[version-notes]]).

## File basics

- UTF-8 without BOM, Unix `LF` line endings.
- 4-space indentation, never tabs.
- End every PHP file with a single newline.
- Omit the closing `?>` tag in files that contain only PHP.
- Soft line limit 120 characters (aim for 80).
- Start every **new** PHP file with `declare(strict_types=1);` after the opening tag. Don't add it to an existing legacy file during an unrelated small edit unless that project already uses it consistently (see [[version-notes]]).
- One class per file; filename matches the class name exactly (PSR-4).

```php
<?php

declare(strict_types=1);

namespace App\Modules\Shop\Services;
```

## Naming (language-level)

These are the language-level deltas on top of the naming table in [[coding-standards]].

- Identifiers contain alphanumeric characters only — no underscores — **except** global helper functions, which use `snake_case` (see [[coding-standards]]).
- Variables and methods start lowercase, `camelCase` for multi-word.
- Class names use `PascalCase` with no consecutive capitals: `Pdf`, `HttpClient`, `QrCode` — not `PDF`, `HTTPClient`, `QRCode`.
- Constants are `UPPER_SNAKE_CASE`, declared as class constants with explicit visibility, or via `define()` at global scope. Prefer a string-backed enum over a set of related constants (see below).
- Loop variables are descriptive; `$i` / `$n` are acceptable only in a short numeric loop. If a loop body exceeds ~20 lines, give the index a real name.

**Correct:**
```php
$itemName = 'Kingston KVR13R9S4K4';
$numberOfItems = 10;
$totalPrice = $price * $numberOfItems;
```

## Strings

- Single quotes for literals with no interpolation. Double quotes when the literal contains an apostrophe (`"It's fine."`) or needs interpolation.
- Concatenate with `.`. Break a long concatenation across lines, one fragment per line; let Pint settle the operator spacing and placement.

  ```php
  $sql = 'SELECT `id`, `name` FROM `people` '
      .'WHERE `name` = ? '
      .'ORDER BY `id` ASC';
  ```

- Interpolate with braces: `"Hello {$name}"`. Never `"Hello $name"` or `"Hello ${name}"` (`${}` is deprecated as of PHP 8.2).
- Use nowdoc (`<<<'SQL'`) for long multi-line strings with no interpolation, heredoc (`<<<HTML`) when interpolation is needed.

## Arrays

- Short syntax `[]`, never `array()`.
- Space after each comma. No negative or non-zero starting indexes.
- Multi-line array: one element per line, one indent level, **trailing comma on the last element**.
- One space around `=>`; Pint normalizes it — do not hand-align.

```php
$user = [
    'firstName' => 'John',
    'lastName' => 'Smith',
    'age' => 36,
];
```

## Type system (PHP 8.x)

- Declare a type for every parameter and every return, including `void` and nullable `?Type`.
- Prefer a union type (`int|string`) over `mixed`.
- `[PHP 8.0+]` Use `match` instead of `switch` when the expression produces a value — `match` is strict (`===`) and has no fallthrough. Keep `switch` only for pure side-effect branching.
- `[PHP 8.0+]` Use the nullsafe operator `$user?->address?->city` instead of manual null-check chains.
- `[PHP 8.0+]` Use named arguments at call sites with several optional parameters.
- `[PHP 8.0+]` Use constructor property promotion for simple value/DI classes; see [[dependency-injection]].
- `[PHP 8.1+]` Use `readonly` for write-once properties.
- `[PHP 8.1+]` Use string-backed enums for a fixed set of domain values instead of constants or magic strings:

  ```php
  enum UserState: string
  {
      case Temporary = 'temporary';
      case Normal = 'normal';
      case Cancelled = 'cancelled';
  }
  ```

## Control statements

- One space before `(` and after `)`; spaces around operators inside the condition.
- Opening `{` on the same line as the statement; closing `}` on its own line; body indented 4 spaces.
- **Always use braces** for `if` / `elseif` / `else` / loops — no brace-less single statements.
- For a long condition, break one clause per line with the logical operator at the end of the line, and put `) {` on its own line:

  ```php
  if (
      $order->isPaid() &&
      $order->items->isNotEmpty() &&
      ! $order->isShipped()
  ) {
      $this->ship($order);
  }
  ```

- Prefer early returns and `abort_if()` guard clauses over nested conditionals (see [[coding-standards]]).

## Class layout

- Opening `{` on the line **below** the class name.
- Every property has an explicit type and explicit visibility.
- Element order inside a class (Pint's `laravel` preset does not enforce this — keep it by hand):
  1. `use` traits
  2. Constants
  3. Properties (static, then instance)
  4. `__construct`
  5. Other magic methods
  6. `public` methods
  7. `protected` methods
  8. `private` methods
- Declare `extends` / `implements` on the class line; if it runs long, one interface per line, indented.
- Use `final` for a class not designed for extension, `abstract` for one that must be extended.

```php
<?php

declare(strict_types=1);

namespace App\Modules\Shop\Models;

final class Cart
{
    private array $items = [];

    public function __construct(
        private readonly int $userId,
    ) {
    }

    public function add(int $productId, int $quantity): void
    {
        $this->items[] = ['product_id' => $productId, 'quantity' => $quantity];
    }
}
```

## Quick reference

| Context | Format | Example |
|---|---|---|
| Variable / method | `camelCase` | `$totalPrice`, `getChannel()` |
| Constant / enum case backing | `UPPER_SNAKE` value | `STATE_NORMAL` |
| Class / enum | `PascalCase`, no consecutive caps | `FileInputStream`, `Pdf` |
| Global helper function | `snake_case` | `auth_user()` |
| File (class) | matches class name | `Cart.php` |
| Indentation | 4 spaces (PSR-12) | — |
| Array syntax | short `[]`, trailing comma | `['key' => 'value']` |
| Strict types | mandatory on new files | `declare(strict_types=1);` |

<!-- Add new team PHP-style rules below this line. Machine ingestion never touches this file. -->
