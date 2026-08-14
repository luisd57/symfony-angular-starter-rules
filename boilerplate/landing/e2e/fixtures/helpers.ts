export const API_BASE_URL: string = process.env['API_BASE_URL'] ?? 'http://localhost:8080/api';

export function uniqueEmail(prefix: string): string {
  return `${prefix}+${Date.now()}-${Math.random().toString(36).slice(2, 7)}@e2e.test`;
}

// {{FILL: page objects and locators for the landing flows.
//  Note for Astro islands: a `client:visible` island below the fold does not
//  hydrate (and does not fetch) until scrolled into view - helpers that touch
//  one must scroll it in first.}}
