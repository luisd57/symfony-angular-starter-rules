---
paths:
  - landing/src/**/*.astro
  - landing/src/**/*.svelte
  - landing/src/**/*.ts
---
# Public Site Conventions (Astro + Svelte)

## Tech Stack
- Astro (Islands Architecture — static HTML with selective client hydration)
- Svelte (interactive island components — add only if interactivity is needed)
- Tailwind CSS

## Project Structure
```
src/
├── components/
│   ├── astro/         # Server-rendered (.astro) — layout, static content
│   └── svelte/        # Client-hydrated (.svelte) — interactive flows
├── content/           # Content Collections (markdown)
├── layouts/           # Astro layout templates
├── pages/             # Astro page routes
├── services/          # Typed API client functions
├── types/             # TypeScript interfaces
└── utils/             # Pure helpers
```

## Conventions
- PascalCase filenames, one component per file
- `.astro` for static/server content; `.svelte` for interactive islands
- API calls centralized in `services/`: exported async functions + one private `apiRequest<T>` that unwraps the envelope and throws a typed `ApiError(code, message, details)` (exported from `types/api.ts`); islands catch with `instanceof ApiError`. Base URL from `import.meta.env.PUBLIC_API_BASE_URL`.
- Svelte 5 runes (`$props`/`$state`/`$derived`) with a local `interface Props` per island
- Dates: display in user's local timezone, send in ISO-8601 UTC
- Client-side validation + always handle server error responses gracefully
