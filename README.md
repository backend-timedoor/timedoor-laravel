# Timedoor Laravel Standards — Claude Code Plugin

Laravel backend coding standards as a Claude Code plugin: PHP conventions, security rules, a single API response format, and patterns distilled from real production projects. Install once — Claude follows these standards in every Laravel project on your machine.

## Install / Instalasi

```bash
# inside Claude Code
/plugin marketplace add backend-timedoor/timedoor-laravel
/plugin install timedoor-laravel
```

Update later with `/plugin update timedoor-laravel` (check CHANGELOG.md for what changed).

**Recommended:** add one line to each project's `CLAUDE.md` to guarantee the standards are always applied:

```
Always follow the laravel-standards skill from the timedoor-laravel plugin for all PHP/Laravel code.
```

## What's inside 

- **Skill `laravel-standards`** — auto-triggers on any PHP/Laravel work. Enforces: thin controllers + service layer, FormRequest validation, standard API envelope, security rules, and a mandatory Definition of Done (Pint + PHPStan + tests after every change).
- **`/review-code <path>`** — audit existing code against the standards; get file:line findings ordered by severity, with fixes.
- **`/report-issue`** — found a bad or missing rule? Generate a structured issue draft for this repo.

## How the standards evolve

Patterns in `references/patterns/` are distilled from real projects through an internal pipeline (mechanical scan → extraction → adversarial critique → human review → PR). Files marked `owner: human` are written directly by the team and always win on conflict. See CHANGELOG.md for history.

Contributions welcome via issues and PRs.

## License

MIT
