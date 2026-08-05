# Reusable-Agentic-Kit

A reusable starting point for agentic coding on a Symfony + Angular stack: conventions, a
scaffold skill, and battle-tested boilerplate.

Copy `CLAUDE.md`, `.claude/` and `boilerplate/` into an empty project, fill the
`{{PLACEHOLDER}}` tokens, and run `/scaffold-project`.

## Stack

- **API** — Symfony 8 / PHP 8.4, hexagonal (Domain / Application / Infrastructure), Doctrine + PostgreSQL, Redis
- **Web UI** — Angular (standalone, signals, zoneless), domain-based folder layout
- **Public site** — Astro, with Svelte islands where interactivity is needed
- Everything dockerized

## What's here

| Path | Purpose |
|---|---|
| `CLAUDE.md` | Project-manual template. Fill the tokens, delete what doesn't apply. |
| `.claude/rules/` | Conventions. Stack rules are `paths:`-scoped so they load only for matching files; `documentation-style.md` and `git-conventions.md` are always on. |
| `.claude/skills/scaffold-project/` | Ordered guide an agent follows to bootstrap a new project. Delete it once scaffolding is done. |
| `.claude/skills/done/` | `/done` — records a finished milestone in `docs/STATUS.md`. |
| `.claude/settings.json` | Permission allowlist for the standard docker/make command surface. |
| `boilerplate/` | Real infrastructure + test-harness files to copy, not reimplement. |

## Design notes

The rules encode deliberate deviations from textbook practice, marked "do not fix" and stated
with their reason so an agent doesn't helpfully undo them:

- ORM attributes live on Domain entities, and there are **no Doctrine relation attributes** — aggregates reference each other by ID value objects. The ORM isn't going to be swapped at the scale these projects run at, so the indirection buys nothing, while bidirectional mappings buy coupling and lazy-loading surprises. This is a mapping rule, not a schema rule: migrations still declare foreign keys with explicit cascade semantics.
- **No validation attributes on DTOs**; controllers validate the decoded request array. Value objects are the real guard, and attribute validation duplicates them and drifts.
- **No kernel exception listener** — each controller action catches the exceptions it can actually produce and maps the status.
- Repositories flush; handlers never manage transactions. No transaction middleware — an accepted trade-off.

Rules files stay terse on purpose (see `.claude/rules/documentation-style.md`). A rule earns
its place only if an agent would otherwise get it wrong.
