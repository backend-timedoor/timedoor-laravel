---
owner: machine
title: Patterns — filament
---

# Patterns: filament

## Navigation groups come from an enum, never a string literal [Filament v4]

Rule: Define one `NavigationGroupEnum` (string-backed, implementing Filament's `HasLabel`) and assign enum cases to `$navigationGroup` on every Resource. Never type group names as string literals, and do not assign `->value`.

Why: enum cases form a closed, greppable group list; `HasLabel` keeps display text separate from stored case names.

Evidence: 23 of 23 Resources use one shared enum in proj-g. Single project; re-confirm on next Filament ingestion.

Example:
```php
use Filament\Support\Contracts\HasLabel;

enum NavigationGroupEnum: string implements HasLabel
{
    case COMPANY = 'company';
    case CONTENT = 'content';

    public function getLabel(): string
    {
        return match ($this) {
            self::COMPANY => 'Company',
            self::CONTENT => 'Content',
        };
    }
}

class ReportResource extends Resource
{
    protected static string|UnitEnum|null $navigationGroup = NavigationGroupEnum::COMPANY;
}
```
