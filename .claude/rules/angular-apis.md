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

## Signals are the default for state. Two separate questions are not.

Component and service state is signals — `signal()`, `computed()`, `WritableSignal`, with
`inject()` throughout. That is not in question and never needs raising.

Two narrower choices sit on top of that, and each is a per-project decision:

- **Data fetching.** Services returning `Observable<T>` with a per-service `unwrap<T>()` is the
  default, because the shipped interceptor and boilerplate assume it. `rxResource()` /
  `httpResource()` wrap the same call in a signal and are a fine choice — just make it
  deliberately, since the two styles read very differently at the call site.
- **Forms.** Reactive (`FormBuilder` / `FormGroup`) is the default. Signal Forms
  (`@angular/forms/signals`) is a fine choice, made once.

Pick one of each per project and apply it throughout; a codebase running both is the failure
mode. Record whichever you pick in CLAUDE.md under Deliberate Deviations.

## Style
- Explicit types on every declaration/member/parameter (ESLint `typedef` + `explicit-function-return-type`, strictTypeChecked); `no-inferrable-types` stays OFF
- Template-only members `protected`; exposed signals `readonly`; selector prefix `app`
- With Angular Material: all colors via `mat.theme()` / `--mat-sys-*` tokens — component SCSS never hardcodes colors

## E2E (Playwright)
- Do NOT gate assertions on `networkidle`: it fires before a lazily-loaded route's chunk issues its XHRs, so a correct page screenshots as empty. Wait on the content itself (`expect(locator).toBeVisible()`).
- Run the browser with the app's timezone (`TZ=...`); a UTC container silently shifts every rendered local timestamp.
