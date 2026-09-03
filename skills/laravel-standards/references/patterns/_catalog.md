---
owner: machine
title: Source Project Catalog
---

# Pattern Source Catalog

Registry of internal projects that have been ingested as pattern sources.
Public note: project names here are ANONYMIZED codes (proj-a, proj-b...). Real identities live only in the private factory repo.

| Code | Type | Laravel | Scope of extraction (from notes) | Status | Last ingested |
|---|---|---|---|---|---|
| proj-a | full-api | 10 | Whole project, no exclusions | extracted | 2026-08-21 |
| proj-b | api,blade,vue | 10 | Whole project, no exclusions | extracted | 2026-08-24 |
| proj-c | api,blade,vue | 11.9 | Whole project, no exclusions | extracted | 2026-08-24 |
| proj-d | api,blade,vue | 8 | Whole project, no exclusions | extracted | 2026-08-31 |
| proj-e | api,blade | 6 | Service Pattern only, focused on easy-to-use/easy-to-understand services | extracted | 2026-08-31 |
| proj-f | api | 9 | repository pattern without services | rejected-conflict | 2026-08-31 |
| proj-g | api,filament | 12 | Whole project; Laravel 12 structure, security, PHP best practice, Filament suggestions | extracted | 2026-08-31 |
| proj-h | blade,vue,inertia | 9 | Bulk export to PDF, XLSX/CSV system | extracted | 2026-09-01 |
| proj-i | blade,vue,inertia,stisla | 9 | Multi-payment gateway, CDN storage integration, job & notification system | extracted | 2026-09-03 |

Legend — Status: `extracted` / `rejected-conflict` / `re-extraction pending` / `deprecated source`.
