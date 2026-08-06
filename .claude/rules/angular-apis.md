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
- State: `signal()`, `computed()`, `linkedSignal()`
- Control flow: `@if`, `@for`, `@switch` — NOT `*ngIf`, `*ngFor`, `*ngSwitch`
- Zoneless — declare `provideZonelessChangeDetection()` rather than relying on the version default
- Functional providers in `app.config.ts`: `provideRouter()`, `provideHttpClient()`
- Vitest for testing — NOT Karma/Jasmine
- Prefer `[class]` / `[style]` bindings over `NgClass` / `NgStyle`

## Defaults, and when to change them

Services return observables and unwrap the envelope per-service; forms are reactive
(`FormBuilder` / `FormGroup`). These are the defaults because the shipped interceptor,
`unwrap<T>()` and boilerplate all assume them.

A resource API (`rxResource()` / `httpResource()`) or Signal Forms is a reasonable choice, but
it is a decision to raise, not a silent default — adopting one halfway leaves the codebase
running two conventions. Pick one per project, apply it throughout, and record it in CLAUDE.md
under Deliberate Deviations.

## Style
- Explicit types on every declaration/member/parameter (ESLint `typedef` + `explicit-function-return-type`, strictTypeChecked); `no-inferrable-types` stays OFF
- Template-only members `protected`; exposed signals `readonly`; selector prefix `app`
- With Angular Material: all colors via `mat.theme()` / `--mat-sys-*` tokens — component SCSS never hardcodes colors

## E2E (Playwright)
- Do NOT gate assertions on `networkidle`: it fires before a lazily-loaded route's chunk issues its XHRs, so a correct page screenshots as empty. Wait on the content itself (`expect(locator).toBeVisible()`).
- Run the browser with the app's timezone (`TZ=...`); a UTC container silently shifts every rendered local timestamp.
