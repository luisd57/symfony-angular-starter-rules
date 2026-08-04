# symfony-angular-starter-rules

A reusable starting point for agentic coding on a Symfony + Angular stack: conventions, a
scaffold guide, and battle-tested boilerplate.

Copy `CLAUDE.md`, `.claude/`, `SCAFFOLDING.md` and `boilerplate/` into an empty project,
fill the `{{PLACEHOLDER}}` tokens, and tell an agent to follow `SCAFFOLDING.md`.

## Stack

- **API** — Symfony 8 / PHP 8.4, hexagonal (Domain / Application / Infrastructure), Doctrine + PostgreSQL, Redis
- **Web UI** — Angular (standalone, signals, zoneless), domain-based folder layout
- **Public site** — Astro, with Svelte islands where interactivity is needed
- Everything dockerized

## What's here

| Path | Purpose |
|---|---|
| `CLAUDE.md` | Project-manual template. Fill the tokens, delete what doesn't apply. |
| `.claude/rules/` | Conventions, `paths:`-scoped so they load only for matching files. |
| `SCAFFOLDING.md` | Ordered guide an agent follows to bootstrap a new project. |
| `boilerplate/` | Real infrastructure + test-harness files to copy, not reimplement. |

## Design notes

The rules encode deliberate deviations from textbook practice, marked "do not fix" so an
agent doesn't helpfully undo them:

- ORM attributes live on Domain entities, and there are **no Doctrine relation attributes** — aggregates reference each other by ID value objects. This is a mapping rule, not a schema rule: migrations still declare foreign keys with explicit cascade semantics.
- **No validation attributes on DTOs**; controllers validate the decoded request array. Value objects are the real guard.
- **No kernel exception listener** — each controller action catches the exceptions it can produce and maps the status.
- Repositories flush; handlers never manage transactions.

Rules files stay terse on purpose (see `.claude/rules/documentation-style.md`). A rule earns
its place only if an agent would otherwise get it wrong.