import { defineConfig, devices } from '@playwright/test';
import * as path from 'node:path';

const APP_URL: string = process.env['APP_URL'] ?? 'http://127.0.0.1:4200';
const ADMIN_STORAGE_STATE: string = path.join(__dirname, 'e2e', '.auth', 'admin.json');

export default defineConfig({
  testDir: './e2e',
  fullyParallel: false,
  forbidOnly: !!process.env['CI'],
  retries: process.env['CI'] ? 1 : 0,
  workers: 1,
  reporter: [['list'], ['html', { open: 'never' }]],
  timeout: 60_000,
  expect: { timeout: 10_000 },

  // globalSetup must do two things:
  //   1. Wait for the dev server to finish its FIRST compile. The container
  //      starts before Angular is serving, so without the wait the suite races
  //      the build.
  //   2. Log in ONCE through a real browser and persist storageState. Route
  //      guards read localStorage synchronously, before /auth/me resolves, so a
  //      cookie-only storageState bounces every protected route to /login.
  //      Logging in once also avoids tripping the API's login rate limiter,
  //      which fires when many tests authenticate from one container IP.
  globalSetup: require.resolve('./e2e/global-setup'),

  use: {
    baseURL: APP_URL,
    // Tests needing a different or anonymous session create their own context
    // with storageState: undefined.
    storageState: ADMIN_STORAGE_STATE,
    // Always on, so a green run is still replayable in the trace viewer.
    // Serve the report over HTTP (the playwright-report compose service) -
    // the viewer uses a Service Worker and shows nothing over file://.
    trace: 'on',
    screenshot: 'only-on-failure',
    video: 'on',
  },

  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],
});
