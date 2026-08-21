---
owner: human
title: Laravel Version Differences
---

# Version Notes

Rules that differ across Laravel versions used in our projects. Tag every version-specific pattern in `patterns/` with `[L5.7]`, `[L7+]`, `[L8-L9]`, `[L10]`, `[L11+]`, or a narrower version range when required.

## Support policy

- Support Laravel 5.7 legacy projects, Laravel 7+ projects (the common range), and current/future Laravel releases.
- Treat Laravel 13 as the current baseline at this document revision, but never hardcode "latest" behavior: detect the installed version from `composer.json` and the resolved framework package before generating code.
- Prefer framework APIs stable across the detected project's supported range. Isolate unavoidable version-specific code and mark it with the matching version tag.
- Do not upgrade a project's Laravel version, replace deprecated APIs, or apply a newer skeleton pattern unless the user explicitly requests an upgrade.

## Version differences

- **[L11+]** Slim skeleton: middleware is registered in `bootstrap/app.php`; there is no `app/Http/Kernel.php`. `casts()` method is available and preferred where the project's conventions use it.
- **[L10+]** Prefer native type declarations and typed properties in generated code when the project's PHP version supports them.
- **[L8-L9]** Middleware is registered through `app/Http/Kernel.php`; `$casts` property is the compatible Eloquent pattern.
- **[L7+]** Use FormRequest, API Resources, Policies/Gates, Sanctum, Eloquent binding, and typed declarations only where the installed PHP/Laravel versions support the exact API.
- **[L5.7]** Preserve the legacy project's existing middleware registration, route syntax, authentication integration, model casting, and PHP compatibility. Verify package APIs before copying patterns from Laravel 7+.

When generating code, inspect `composer.json` first, then confirm framework APIs against the installed package/version. Follow the matching version tag; do not assume the newest skeleton applies to a legacy project.
