import { defineConfig, devices } from '@playwright/test';

/**
 * Playwright configuration for MageBridge E2E tests.
 * @see https://playwright.dev/docs/test-configuration
 */
export default defineConfig({
  testDir: './tests',
  /* Run tests in files in parallel */
  fullyParallel: true,
  /* Fail the build on CI if you accidentally left test.only in the source code. */
  forbidOnly: !!process.env.CI,
  /* Retry on CI only */
  retries: process.env.CI ? 2 : 0,
  /* Opt out of parallel tests on CI. */
  workers: process.env.CI ? 1 : undefined,
  /* Reporter to use. See https://playwright.dev/docs/test-reporters */
  reporter: [
    ['html', { open: 'never' }],
    ['list'],
  ],
  /* Shared settings for all the projects below. See https://playwright.dev/docs/api/class-testoptions. */
  use: {
    /* Base URL to use in actions like `await page.goto('/')`. */
    baseURL: process.env.BASE_URL || 'https://www.dev.local',

    /* Collect trace when retrying the failed test. See https://playwright.dev/docs/trace-viewer */
    trace: 'on-first-retry',

    /* Take screenshot on failure */
    screenshot: 'only-on-failure',

    /* Ignore HTTPS errors (self-signed certificates) */
    ignoreHTTPSErrors: true,

    /* Default timeout for actions */
    actionTimeout: 10000,

    /* Default navigation timeout */
    navigationTimeout: 30000,
  },

  /* Global timeout for each test */
  timeout: 60000,

  /* Configure projects for major browsers */
  projects: [
    /* Setup project for Joomla admin authentication */
    {
      name: 'setup',
      testDir: './fixtures',
      testMatch: /auth\.setup\.ts/,
    },

    /* Setup project for OpenMage admin authentication */
    {
      name: 'setup-openmage',
      testDir: './fixtures',
      testMatch: /openmage\.setup\.ts/,
    },

    /* Joomla admin tests */
    {
      name: 'chromium',
      use: {
        ...devices['Desktop Chrome'],
        /* Use authenticated state from setup */
        storageState: 'playwright/.auth/admin.json',
      },
      dependencies: ['setup'],
      testIgnore: /.*\/openmage\/.*/, // Ignore OpenMage tests
    },

    /* OpenMage admin tests */
    {
      name: 'openmage',
      use: {
        ...devices['Desktop Chrome'],
        /* Use OpenMage authenticated state */
        storageState: 'playwright/.auth/openmage-admin.json',
        baseURL: process.env.OPENMAGE_URL || 'https://store.dev.local',
      },
      dependencies: ['setup-openmage'],
      testMatch: /.*\/openmage\/.*/, // Only OpenMage tests
    },

    /* Uncomment to test on Firefox */
    // {
    //   name: 'firefox',
    //   use: {
    //     ...devices['Desktop Firefox'],
    //     storageState: 'playwright/.auth/admin.json',
    //   },
    //   dependencies: ['setup'],
    // },

    /* Uncomment to test on Safari */
    // {
    //   name: 'webkit',
    //   use: {
    //     ...devices['Desktop Safari'],
    //     storageState: 'playwright/.auth/admin.json',
    //   },
    //   dependencies: ['setup'],
    // },
  ],

  /* Output folder for test artifacts */
  outputDir: 'test-results/',

  /* Run your local dev server before starting the tests */
  // webServer: {
  //   command: 'docker compose -f ../.devcontainer/compose.yml up -d',
  //   url: 'https://www.dev.local',
  //   reuseExistingServer: !process.env.CI,
  //   ignoreHTTPSErrors: true,
  // },
});
