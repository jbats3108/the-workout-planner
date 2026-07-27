import { defineConfig, devices } from '@playwright/test';

const baseURL = process.env.PLAYWRIGHT_BASE_URL ?? 'http://127.0.0.1:8000';
const webServerHealthUrl = `${baseURL}/up`;

export default defineConfig({
    testDir: 'e2e',
    globalSetup: './e2e/global-setup.ts',
    fullyParallel: false,
    forbidOnly: !!process.env.CI,
    retries: process.env.CI ? 2 : 0,
    workers: process.env.CI ? 1 : undefined,
    timeout: process.env.CI ? 60_000 : 30_000,
    reporter: process.env.CI ? 'github' : 'list',
    use: {
        baseURL,
        trace: 'on-first-retry',
        actionTimeout: process.env.CI ? 15_000 : 10_000,
    },
    projects: [{ name: 'chromium', use: { ...devices['Desktop Chrome'] } }],
    webServer: process.env.PLAYWRIGHT_SKIP_WEBSERVER
        ? undefined
        : {
              command: 'php artisan serve --host=127.0.0.1 --port=8000 --no-reload',
              url: webServerHealthUrl,
              reuseExistingServer: !process.env.CI,
              timeout: process.env.CI ? 300_000 : 120_000,
              env: {
                  PHP_CLI_SERVER_WORKERS: '1',
              },
          },
});
