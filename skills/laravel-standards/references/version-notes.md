---
owner: human
title: Laravel Version Differences
---

# Version Notes

Rules that differ across Laravel versions used in our projects. Tag every version-specific pattern in `patterns/` with `[L8]`, `[L9]`, `[L10]`, `[L11+]`.

- **[L11+]** Slim skeleton: no `app/Http/Kernel.php`; middleware registered in `bootstrap/app.php`. `casts()` method preferred over `$casts` property.
- **[L10+]** Native type declarations in generated code; prefer typed properties.
- **[L8-L9]** `$casts` property; middleware via `Kernel.php`; older route caching constraints.

When generating code, detect the project's version from `composer.json` first and follow the matching column.
