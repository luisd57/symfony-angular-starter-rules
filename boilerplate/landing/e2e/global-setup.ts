import { request, type APIRequestContext } from '@playwright/test';
import { API_BASE_URL } from './fixtures/helpers';

const READY_TIMEOUT_MS: number = 120_000;
const POLL_INTERVAL_MS: number = 2_000;

/**
 * Waits for the API to answer before the specs run.
 *
 * {{FILL: add a fail-fast pre-check for whatever data the landing specs need
 *  (seeded content, availability...). Without one, a missing fixture surfaces
 *  deep in a flow as a confusing "nothing to click" failure instead of a clear
 *  "seed this first" message.}}
 */
export default async function globalSetup(): Promise<void> {
  await waitForUrl(`${API_BASE_URL}/health`, 'API');
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
