import { chromium, request, type APIRequestContext, type Browser } from '@playwright/test';
import * as fs from 'node:fs';
import * as path from 'node:path';
import { ADMIN_EMAIL, ADMIN_PASSWORD, ADMIN_STORAGE_STATE, APP_URL, MAILHOG_URL } from './fixtures/helpers';

const READY_TIMEOUT_MS: number = 120_000;
const POLL_INTERVAL_MS: number = 2_000;

export default async function globalSetup(): Promise<void> {
  // 1. Wait for the Angular dev server to finish compiling. `depends_on: app`
  //    in compose waits for the CONTAINER to start, not for the dev server to
  //    listen — first compile is ~10s and the suite would race it.
  await waitForUrl(APP_URL, 'app');

  // 2. Wait for MailHog (usually instant, but defensive).
  await waitForUrl(MAILHOG_URL, 'mailhog');

  // 3. Start from an empty inbox.
  const mailhog = await request.newContext();
  try {
    const response = await mailhog.delete(`${MAILHOG_URL}/api/v1/messages`);
    if (!response.ok()) {
      throw new Error(`MailHog DELETE failed: ${response.status()}`);
    }
  } finally {
    await mailhog.dispose();
  }

  // 4. Log in ONCE through the real UI and persist the full storageState
  //    (cookies AND localStorage). Every spec reuses it via
  //    playwright.config.ts `use.storageState`. Two reasons this matters:
  //      - the route guard reads localStorage synchronously, before
  //        /api/auth/me returns, so a cookie-only state bounces every
  //        protected route to /login
  //      - logging in once avoids tripping the API's per-IP login rate
  //        limiter, which fires when many specs authenticate from one container
  const browser: Browser = await chromium.launch();
  const ctx = await browser.newContext({ baseURL: APP_URL });
  const page = await ctx.newPage();
  try {
    await page.goto('/login');
    // {{FILL: match this project's login form and post-login route}}
    await page.getByRole('textbox', { name: 'Email' }).fill(ADMIN_EMAIL);
    await page.getByRole('textbox', { name: 'Password' }).fill(ADMIN_PASSWORD);
    await page.getByRole('button', { name: 'Log In' }).click();
    try {
      await page.waitForURL(/\/$/, { timeout: 15_000 });
    } catch (error: unknown) {
      const message: string = error instanceof Error ? error.message : String(error);
      throw new Error(
        `Admin login pre-check failed: ${message}. Set ADMIN_EMAIL / ADMIN_PASSWORD to ` +
          `match the account seeded by app:create-{{admin_role}}.`,
      );
    }

    await fs.promises.mkdir(path.dirname(ADMIN_STORAGE_STATE), { recursive: true });
    await ctx.storageState({ path: ADMIN_STORAGE_STATE });
  } finally {
    await page.close();
    await ctx.close();
    await browser.close();
  }
}

async function waitForUrl(url: string, label: string): Promise<void> {
  const context: APIRequestContext = await request.newContext();
  const deadline: number = Date.now() + READY_TIMEOUT_MS;
  try {
    let lastError: string = '';
    while (Date.now() < deadline) {
      try {
        const response = await context.get(url, { timeout: 5_000 });
        // Any HTTP response (even 404) means the server is listening.
        if (response.status() > 0) return;
      } catch (error: unknown) {
        lastError = error instanceof Error ? error.message : String(error);
      }
      await new Promise<void>((resolve): NodeJS.Timeout => setTimeout(resolve, POLL_INTERVAL_MS));
    }
    throw new Error(
      `Timed out after ${READY_TIMEOUT_MS}ms waiting for ${label} at ${url}. Last error: ${lastError}`,
    );
  } finally {
    await context.dispose();
  }
}
