import * as path from 'node:path';

// ── Configuration ────────────────────────────────────────────────────────

export const APP_URL: string = process.env['APP_URL'] ?? 'http://127.0.0.1:4200';
export const MAILHOG_URL: string = process.env['MAILHOG_URL'] ?? 'http://localhost:8025';

// Must match the account seeded by `app:create-{{admin_role}}`.
export const ADMIN_EMAIL: string = process.env['ADMIN_EMAIL'] ?? 'admin@example.com';
export const ADMIN_PASSWORD: string = process.env['ADMIN_PASSWORD'] ?? 'ChangeMe1!';

/** Where globalSetup persists the admin's logged-in state. */
export const ADMIN_STORAGE_STATE: string = path.join(__dirname, '..', '.auth', 'admin.json');

// ── Helpers ──────────────────────────────────────────────────────────────

export function uniqueEmail(prefix: string): string {
  return `${prefix}+${Date.now()}-${Math.random().toString(36).slice(2, 7)}@e2e.test`;
}

// {{FILL: add per-domain helpers here - page objects, seeding shortcuts,
//  MailHog message readers.}}
