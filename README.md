# Reusable-Agentic-Kit

A reusable starting point for agentic coding on a Symfony + Angular stack, distilled from a
production project: conventions, a scaffold skill, and everything needed to actually bring a
new project up - config, docker, CI and boilerplate, not descriptions of them.

Copy `CLAUDE.md`, `.claude/`, `.mcp.json` and `boilerplate/` into an empty project, fill the
`{{PLACEHOLDER}}` tokens, and run `/scaffold-project`.

## Stack

- **API** - Symfony 8 / PHP 8.4, hexagonal (Domain / Application / Infrastructure), Doctrine + PostgreSQL, Redis
- **Web UI** - Angular (standalone, signals, zoneless), domain-based folder layout
- **Public site** - Astro, with Svelte islands where interactivity is needed
- Everything dockerized

## What's here

| Path | Purpose |
|---|---|
| `CLAUDE.md` | Project-manual template. Fill the tokens, delete what doesn't apply. |
| `.claude/rules/` | Conventions. Stack rules are `paths:`-scoped so they load only for matching files; `documentation-style.md` and `git-conventions.md` are always on. |
| `.claude/skills/scaffold-project/` | Ordered guide an agent follows to bootstrap a new project, plus the copy manifest and token list. Delete it once scaffolding is done. |
| `.claude/skills/done/` | `/done` - records a finished milestone in `docs/STATUS.md`. |
| `.claude/hooks/` | Four hooks enforcing the conventions above, registered in `settings.json`, tested with `npm test`. **Needs the `mattpocock-skills` plugin enabled** - `skill-gate.mjs` names its skills and nothing else clears those gates. |
| `.claude/settings.json`, `.mcp.json` | Permission allowlist, hook registration, and the Playwright MCP server. |
| `boilerplate/` | The project itself, minus the domain: Symfony config, docker-compose, Makefile, Dockerfiles, nginx, CI workflow, frontend config, and the PHP that would otherwise be rewritten every time. |

Every credential in `boilerplate/` is a `CHANGE_ME` placeholder. Generate real values per
project; the JWT keypair is created by `make init` and is gitignored.

## What syncs back, and what doesn't

The kit is a starting point, not a control plane. A project that diverges after meeting a real
problem is working as intended, and that divergence carries information worth keeping. Only a
small set of things should ever be pushed back across all projects.

**Invariant** - the answer is the same everywhere, so the kit is authoritative and a mismatch is
a defect to fix in every project:

- The Process Skills section of `CLAUDE.md`. Which plugin provides them is a machine-level
  decision, not a project trait.
- `documentation-style.md`.
- `git-conventions.md`.
- `paths:` frontmatter hygiene - an always-on rule that could be scoped is a mistake anywhere.

**Seed** - the kit supplies a v1 and the project owns it from then on. Do not diff these for
convergence, and do not retro-fit one project's version onto another:

- Every `paths:`-scoped stack rule (`api-*.md`, `angular*.md`, `astro-landing.md`,
  `dev-gotchas.md`, `testing-policy.md`) and their filenames. A project with three deployables
  wants `dashboard-angular.md`; one with a single `web/` does not.
- `.claude/settings.json` permissions beyond the shared baseline.
- Anything under `docs/`, `.scratch/`, or project-specific skills.
- `.claude/hooks/`. They encode this kit's conventions, so a project that moves a convention
  moves its hook with it. `SOURCE_DIRS` in `skill-gate.mjs` is the line most projects edit
  first, and it fails open: a deployable missing from that list means the TDD gate never
  fires there and nothing reports it.

## Design notes

The rules encode deliberate deviations from textbook practice, marked "do not fix" and stated
with their reason so an agent doesn't helpfully undo them:

- ORM attributes live on Domain entities, and there are **no Doctrine relation attributes** - aggregates reference each other by ID value objects. The ORM isn't going to be swapped at the scale these projects run at, so the indirection buys nothing, while bidirectional mappings buy coupling and lazy-loading surprises. This is a mapping rule, not a schema rule: migrations still declare foreign keys with explicit cascade semantics.
- **No validation attributes on DTOs**; controllers validate the decoded request array. Value objects are the real guard, and attribute validation duplicates them and drifts.
- **No kernel exception listener** - each controller action catches the exceptions it can actually produce and maps the status.
- Repositories flush; handlers never manage transactions. No transaction middleware - an accepted trade-off.

Rules files stay terse on purpose (see `.claude/rules/documentation-style.md`). A rule earns
its place only if an agent would otherwise get it wrong.
