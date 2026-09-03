---
description: Audit PHP/Laravel files against the laravel-standards skill and report violations with fixes
argument-hint: <file-or-directory> [more paths...]
---

Audit the given path(s) against the `laravel-standards` skill.

Target: $ARGUMENTS (if empty, ask the user which files or directory to review; offer `git diff --name-only` results as a quick option).

Steps:
1. Read the skill's SKILL.md, then the reference files relevant to the code under review (coding-standards, php-style, security, api-response, manual-rules, plus dependency-injection / error-handling / jobs-events / logging when the code touches those areas, the matching patterns file, and version-notes if version-specific constructs appear).
2. Read the target files. For directories, review PHP files; skip vendor/, storage/, bootstrap/cache/.
3. For each violation report: file:line — rule violated (name the reference file) — why it matters — concrete fix (show corrected code for non-trivial cases).
4. Order findings by severity: SECURITY first, then correctness, then style. Style issues that Pint would auto-fix: just say "run pint" instead of listing each.
5. End with a short verdict: pass / pass with warnings / needs changes, plus the 1-3 highest-impact fixes.
6. Offer to apply the fixes. If the user agrees, apply them and then run the Definition of Done steps from the skill (pint, phpstan, tests).

Be strict but practical: flag real violations of the written standards, not personal style preferences that no rule covers.
