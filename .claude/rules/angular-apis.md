---
# Adjust paths if your Angular app directory is named differently (e.g. dashboard/)
paths:
  - app/src/**/*.ts
  - app/src/**/*.html
  - app/src/**/*.scss
  - app/e2e/**/*.ts
---
# Angular APIs, Style & E2E

## Modern Angular Standards (v21+)
- All components standalone — NO NgModules, no `.module.ts` files
- Signal APIs: `input()`, `output()`, `model()` — NOT `@Input()`, `@Output()`
- `inject()` function — NOT constructor injection
- State: `signal()`, `computed()`, `linkedSignal()`, `resource()` / `httpResource()`
- Control flow: `@if`, `@for`, `@switch` — NOT `*ngIf`, `*ngFor`, `*ngSwitch`
- Zoneless by default — no Zone.js
- Functional providers in `app.config.ts`: `provideRouter()`, `provideHttpClient()`
- Vitest for testing — NOT Karma/Jasmine
- Signal Forms (`@angular/forms/signals`) for new forms
- Prefer `[class]` / `[style]` bindings over `NgClass` / `NgStyle`

Check what the project actually uses before applying the newest API — a codebase mid-migration
(e.g. `rxResource` rather than `httpResource`) should stay internally consistent. Record the
deviation in CLAUDE.md rather than mixing both.

## Style
- Explicit types on every declaration/member/parameter (ESLint `typedef` + `explicit-function-return-type`, strictTypeChecked); `no-inferrable-types` stays OFF
- Template-only members `protected`; exposed signals `readonly`; selector prefix `app`
- With Angular Material: all colors via `mat.theme()` / `--mat-sys-*` tokens — component SCSS never hardcodes colors

## E2E (Playwright)
- Do NOT gate assertions on `networkidle`: it fires before a lazily-loaded route's chunk issues its XHRs, so a correct page screenshots as empty. Wait on the content itself (`expect(locator).toBeVisible()`).
- Run the browser with the app's timezone (`TZ=...`); a UTC container silently shifts every rendered local timestamp.
