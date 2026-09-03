<!-- TEMPLATE - replace every {{...}} token and fill each {{FILL: ...}} section, then delete
     this comment block. Copy this file, .claude/, and boilerplate/ into a new project's root
     (Claude Code only auto-loads the repo-root .claude/, not a nested one). To bootstrap the
     project structure, invoke /scaffold-project. Delete the rules files for apps the project
     doesn't use, and delete .claude/skills/scaffold-project/ once scaffolding is done. -->

Delegate to a subagent only for large, genuinely independent investigations. Don't delegate work
you can finish in a handful of tool calls, and don't use subagents to double-check your own work.

## Process Skills

All from the **mattpocock-skills** plugin. Invoke them; do NOT restate their guidance here.

| Situation | Skill |
|---|---|
| Idea too big for one session, route unclear | `wayfinder` |
| Plan or decision that needs stress-testing | `grilling` |
| Conversation has settled, needs writing up | `to-spec`, then `to-tickets` |
| Question dialogue can't answer | `prototype` |
| Implementing anything | `tdd` |
| Something broken, throwing, or slow | `diagnosing-bugs` |
| Before opening a PR | `code-review` |
| Terminology or an ADR | `domain-modeling` |

`to-spec`, `to-tickets` and `wayfinder` are user-invocable only. Ask for them rather than writing
a spec or ticket by hand.

Keep this file and `.claude/rules/` focused on project facts the plugin doesn't cover.

# {{PROJECT_NAME}}

{{FILL: one paragraph - what this system is, who uses it, the core workflow.}}

## Project Structure

Default layout (delete lines for apps not in this project):

- `API/` - Symfony backend, hexagonal architecture (PHP, PostgreSQL, Redis)
- `app/` - Angular authenticated web UI
- `landing/` - Public-facing Astro site (+ Svelte islands if interactive)

{{FILL: adjust names/stack if they differ; one line per additional directory.}}

Everything runs dockerized - see `docker-compose.yml` and `Makefile`.

## Deliberate Deviations (do not "fix")

{{FILL: choices that look wrong but are intentional, each with its reason. An agent
that doesn't know why will keep proposing the textbook alternative. Delete if none.}}

## Domain Terminology

{{FILL: the domain terms a newcomer would misread - term then definition. Delete this section if the domain is self-evident.}}

## API Response Envelope

Emitted by `ApiResponseTrait`, consumed by each frontend service's `unwrap<T>()`.

```json
{"success": true, "data": {...}}
{"success": false, "error": {"code": "...", "message": "..."}}
{"success": true, "data": [...], "pagination": {"page": 1, "limit": 20, "total": 42, "total_pages": 3}}
```

Auth: JWT via httpOnly cookie (browser) or Bearer token (API clients). Dates: ISO-8601 throughout.

## On-Demand Documentation

Not loaded automatically. Reference with `@` when needed:
{{FILL: e.g. `@API/docs/database-schema.md`, `@API/Product-Requirements.md`, the Postman collection}}

## Dev Environment

{{FILL: how to start the app and the service URLs. Commands in a fenced block.}}

The check that proves the tree is green: `make test`.

## Adding Project-Specific Rules

Stack/architecture conventions go in `.claude/rules/*.md`. Add `paths:` frontmatter to scope a
rule to matching files (lazy-loaded); omit it for always-on rules. Repeatable multi-step
workflows go in `.claude/skills/` instead. Follow `.claude/rules/documentation-style.md`.

`.claude/hooks/` is the third place, for a convention worth enforcing rather than only stating.
A hook fails open by design, so it never replaces the rule that explains why. Registration is in
`.claude/settings.json`; run `npm test` in `.claude/hooks/` after changing one.

## Status

Current per-component status: `docs/STATUS.md`. Read it when the state of an unfinished
component matters; the `/done` skill updates it.
