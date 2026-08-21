---
description: Report a problem or improvement for the laravel-standards plugin as a structured GitHub issue draft
argument-hint: [short description of the problem]
---

The user found a problem with the coding standards (wrong rule, missing rule, rule producing bad code, conflict between rules).

Input: $ARGUMENTS (if empty, ask what went wrong).

Steps:
1. Clarify with the user (briefly): which rule/file is involved, what code it produced or blocked, what they expected instead.
2. Identify the exact reference file and section. Quote the current rule text.
3. Draft a GitHub issue in this format:

   **Title:** [rule-file] short problem statement
   **Body:**
   - Rule involved: <file + section>
   - Current behavior: <what the standard currently says / produces>
   - Problem: <why this is wrong or incomplete>
   - Proposed change: <concrete new/edited rule text>
   - Context: Laravel version, project type (blade/vue/api)

   IMPORTANT: strip any client names, internal hostnames, credentials, or business-identifying details from all quoted code before including it.
4. Show the draft. On approval, tell the user to open it at the plugin repo's Issues page (paste-ready). If the `gh` CLI is available and the user wants, offer: `gh issue create --title "..." --body "..."`.
