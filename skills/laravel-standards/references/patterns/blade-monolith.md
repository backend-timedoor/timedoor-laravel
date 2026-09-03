---
owner: machine
title: Patterns — blade-monolith
---

# Patterns: blade-monolith

## Area layout + section/stack composition for Blade views [L7+]
Rule: Every Blade page view extends a layout for its area (`layouts.{area}`, e.g. `layouts.admin`) and fills named sections; page-scoped CSS/JS goes in `@push` stacks the layout renders with `@stack`, never inline in the page body. Use Blade components (`<x-card>`) for parameterized or repeated fragments and reserve `@include` for static partials. Views render data only — no queries, no business logic, no `@php` blocks holding logic.
Why: One layout per audience keeps document chrome, asset order and navigation in a single file; stacks let a page contribute assets without the layout knowing about it; components pass explicit inputs, so a fragment's contract is visible instead of relying on ambient variables leaking from the parent view.
Evidence: 95 of 98 views extend a layout, 19 use includes/components across proj-d; area layouts split admin from public views.
Example:
```blade
@extends('layouts.admin')

@section('title', $title)

@push('css')
    <link rel="stylesheet" href="{{ asset('css/table.css') }}">
@endpush

@section('content')
    <x-card :title="$title">
        @include('partials.alerts')
    </x-card>
@endsection
```
Snippet candidate: no

## Spreadsheet exports live in dedicated export classes [L7+]
Rule: When a project already uses a spreadsheet export package, every export gets its own class under `app/Exports` — one class per sheet — that receives explicit constructor inputs (filters, date range, id list) and owns the query. The controller only resolves inputs and returns the download. Build the query inside the export class with every relationship the output touches eager-loaded, and prefer a query-backed export the package can chunk; render through a Blade view only when the output needs styling, merged cells, or a custom header block, and that view formats data only — no queries, no aggregation, no `@php` logic.
Why: An export is a report with its own column contract, and that contract belongs in one named class instead of being spread between a controller action and a template. Exports also walk every row of a result set, so a missing eager-load turns one lazy relation into one query per row, and a fully materialized collection turns row count into memory usage — both fail silently in development-sized data and fall over in production.
Evidence: 9 sheet classes plus 2 standalone export classes in proj-h, all constructed with explicit filter inputs; 3 export controller actions delegate to them.
Example:
```php
final class ProductExport implements FromQuery, WithHeadings
{
    public function __construct(private int $categoryId) {}

    public function query(): Builder
    {
        return Product::query()
            ->with(['category', 'unit'])
            ->where('category_id', $this->categoryId);
    }

    public function headings(): array
    {
        return ['SKU', 'Name', 'Category', 'Unit', 'Stock'];
    }
}
```
Snippet candidate: no
