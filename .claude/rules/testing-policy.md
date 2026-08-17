# Testing Policy

What this project expects of its tests. How to write a good test is `mattpocock-skills:tdd` -
don't restate it here.

## Test-first

Implementation goes through `mattpocock-skills:tdd`.

## Every change carries coverage at a level that can observe it

| What changed | Level |
|---|---|
| Domain logic, value objects, pure functions | Unit |
| Anything crossing HTTP, the database, or the container | Integration |
| A user-visible flow, where an E2E seam already exists | E2E |

Prefer an existing seam to a new one, and the highest seam that can still see the behaviour.
Adding a seam is a decision worth stating, not a reflex.

## "Suite green" is not coverage

A green suite proves nothing was broken. It does not prove the new behaviour is pinned - it is
satisfied by writing no tests at all. A ticket whose only test criterion is "suite green" is
under-specified; name the behaviour that must fail if the code regresses.

## Run the full suite before pushing backend changes

No `--testsuite` flag. Handler and domain changes ripple into controller exception mapping, which
only the Integration suite exercises.

{{FILL: the exact command, and any project-specific worked example of a test that looked like
coverage and wasn't. A concrete past failure earns its place here; a restatement of the tdd
skill does not.}}
