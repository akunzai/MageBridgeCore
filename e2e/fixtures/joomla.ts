import { test as base, expect, type BrowserContext } from '@playwright/test';

type WorkerStorageState = Awaited<
  ReturnType<BrowserContext['storageState']>
>;

/**
 * Worker-scoped Joomla admin session fixture.
 *
 * Joomla stores flash messages (`application.queue`), SSO redirect targets
 * (`magento_redirect`), and list filters in `jos_session` without row locking.
 * Sharing one session cookie across parallel Playwright workers causes workers
 * to consume each other's flash messages or overwrite session keys.
 *
 * Authenticating once per worker gives each parallel worker an isolated
 * `jos_session` row with minimal login overhead.
 */
export const test = base.extend<{}, { workerStorageState: WorkerStorageState }>({
  storageState: ({ workerStorageState }, use) => use(workerStorageState),

  workerStorageState: [
    async ({ browser }, use) => {
      const baseURL = process.env.BASE_URL || 'https://www.dev.local:8443';
      const context = await browser.newContext({
        baseURL,
        ignoreHTTPSErrors: true,
        storageState: undefined,
      });
      const page = await context.newPage();

      let state: WorkerStorageState;
      try {
        await page.goto('/administrator/');
        await expect(page.locator('form#form-login')).toBeVisible();

        const username = process.env.ADMIN_USERNAME || 'admin';
        const password = process.env.ADMIN_PASSWORD || 'ChangeTheP@ssw0rd';

        await page.fill('input[name="username"]', username);
        await page.fill('input[name="passwd"]', password);
        await page.click('button[type="submit"]');

        await expect(page).toHaveURL(/\/administrator\/index\.php/);
        await expect(
          page.locator('.header-item-content').first()
        ).toBeVisible({ timeout: 10000 });

        // Handle first-time welcome tour dialog if it appears
        try {
          const welcomeDialog = page.getByRole('dialog', {
            name: /welcome to joomla/i,
          });
          if (await welcomeDialog.isVisible()) {
            await welcomeDialog
              .getByRole('button', { name: /hide forever/i })
              .click();
            await page.waitForSelector('.shepherd-modal-is-visible', {
              state: 'detached',
              timeout: 5000,
            });
          }
        } catch {
          // Dialog did not appear
        }

        // Handle Joomla Statistics opt-in dialog if it appears
        try {
          const statsButton = page.getByRole('button', {
            name: 'No',
            exact: true,
          });
          if (await statsButton.isVisible()) {
            await statsButton.click();
            await statsButton.waitFor({ state: 'hidden', timeout: 2000 });
          }
        } catch {
          // Dialog did not appear
        }

        // Let post-login dashboard AJAX requests settle before tests reuse this session.
        await page.waitForLoadState('networkidle');

        state = await context.storageState();
      } finally {
        await context.close();
      }

      await use(state);
    },
    { scope: 'worker' },
  ],
});

export { expect };
