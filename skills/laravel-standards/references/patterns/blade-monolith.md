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
