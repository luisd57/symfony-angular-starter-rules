<!-- TEMPLATE — replace every {{...}} token and fill each {{FILL: ...}} section, then delete
     this comment block. Copy this file, .claude/, SCAFFOLDING.md, and boilerplate/ into a new project's root
     (Claude Code only auto-loads the repo-root .claude/, not a nested one). To bootstrap the
     project structure, tell the agent to follow SCAFFOLDING.md. Delete the rules files for
     apps the project doesn't use; delete SCAFFOLDING.md once scaffolding is done. -->

1. Ask, don't assume. If something is unclear, ask before writing a single line. Never make silent assumptions about intent, architecture, or requirements.

2. Simplest solution first. Always implement the simplest thing that could work. Do not add abstractions or flexibility that weren't explicitly requested.

3. Don't touch unrelated code. If a file or function is not directly part of the current task, do not modify it, even if you think it could be improved.

4. Flag uncertainty explicitly. If you are not confident about an approach or technical detail, say so before proceeding. Confidence without certainty causes more damage than admitting a gap.

## Process Skills (Superpowers)

Brainstorming, TDD, systematic debugging, verification-before-completion, and code review come from the Superpowers plugin — invoke those skills; do NOT restate their guidance here. Keep this file and `.claude/rules/` focused on project facts and conventions Superpowers doesn't cover.

# {{PROJECT_NAME}}

{{FILL: one paragraph — what this system is, who uses it, the core workflow.}}

## Workflow Rules

When the user says "/done" or indicates a feature/milestone is complete:
1. Update the "Implementation Status" section at the bottom of this file.
2. If the work revealed reusable patterns or gotchas, suggest updating auto-memory (but ask first).
3. Do NOT update `.claude/rules/` files unless explicitly asked.

Memory: keep all auto-memory inline in `MEMORY.md` — one file only, do NOT create per-memory files. This is the project-scoped memory store, not a file in the repo. Never write project memory to the user-level `~/.claude/CLAUDE.md`, which applies to every project on this machine. Follow the terseness rules in `.claude/rules/documentation-style.md` for memory entries too.

## Project Structure

Default layout (delete lines for apps not in this project):

- `API/` — Symfony backend, hexagonal architecture (PHP, PostgreSQL, Redis)
- `app/` — Angular authenticated web UI
- `landing/` — Public-facing Astro site (+ Svelte islands if interactive)

{{FILL: adjust names/stack if they differ; one line per additional directory.}}

Everything runs dockerized — see `docker-compose.yml` and `Makefile`.

## Domain Terminology

{{FILL: the domain terms a newcomer would misread — term then definition. Delete this section if the domain is self-evident.}}

## Dev Environment

{{FILL: how to start the app, run tests, and the service URLs. Commands in a fenced block.}}

## Adding Project-Specific Rules

Stack/architecture conventions go in `.claude/rules/*.md`. Add `paths:` frontmatter to scope a rule to matching files (lazy-loaded); omit it for always-on rules. Follow `.claude/rules/documentation-style.md`.

## Implementation Status

{{FILL: one line per component/milestone. Update on /done.}}
