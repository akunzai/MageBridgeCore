import { defineConfig, devices } from '@playwright/test';

/**
 * Playwright configuration for MageBridge E2E tests.
 *
 * Test structure:
 * - tests/joomla/admin/  - Joomla admin panel tests
 * - tests/joomla/site/   - Joomla frontend tests
 * - tests/openmage/admin/ - OpenMage admin panel tests
 *
 * @see https://playwright.dev/docs/test-configuration
 */
const isCI = !!process.env.CI;

export default defineConfig({
  testDir: './tests',
  fullyParallel: true,
  forbidOnly: isCI,
  retries: isCI ? 3 : 0,
  workers: 5,
  reporter: isCI ? [['html'], ['github']] : [['html'], ['list']],
  timeout: 30000,
  expect: {
    timeout: 5000,
  },
  use: {
    baseURL: process.env.BASE_URL || 'https://www.dev.local',
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
    ignoreHTTPSErrors: true,
    // Performance optimizations
    actionTimeout: 10000,
    navigationTimeout: 15000,
    video: 'off',
  },
  projects: [
    {
      name: 'setup-joomla',
      testDir: './fixtures',
      testMatch: /auth\.setup\.ts/,
    },
    {
      name: 'setup-openmage',
      testDir: './fixtures',
      testMatch: /openmage\.setup\.ts/,
    },

    /* Joomla admin tests */
    {
      name: 'joomla-admin',
      use: {
        ...devices['Desktop Chrome'],
        storageState: 'playwright/.auth/admin.json',
      },
      dependencies: ['setup-joomla'],
      testMatch: /joomla\/admin\/.*\.spec\.ts/,
    },

    /* Joomla site tests */
    {
      name: 'joomla-site',
      use: {
        ...devices['Desktop Chrome'],
        storageState: 'playwright/.auth/admin.json',
      },
      dependencies: ['setup-joomla'],
      testMatch: /joomla\/site\/.*\.spec\.ts/,
    },

    /* OpenMage admin tests */
    {
      name: 'openmage-admin',
      use: {
        ...devices['Desktop Chrome'],
        storageState: 'playwright/.auth/openmage-admin.json',
        baseURL: process.env.OPENMAGE_URL || 'https://store.dev.local',
      },
      dependencies: ['setup-openmage'],
      testMatch: /openmage\/admin\/.*\.spec\.ts/,
    },
  ],

  /* Output folder for test artifacts */
  outputDir: 'test-results/',
});
