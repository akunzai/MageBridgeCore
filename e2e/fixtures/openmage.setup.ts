import { test as setup, expect } from '@playwright/test';

const OPENMAGE_ADMIN_FILE = 'playwright/.auth/openmage-admin.json';

/**
 * Setup authentication state for OpenMage admin user.
 * This runs once before all OpenMage tests and saves the authenticated state.
 */
setup('authenticate as OpenMage admin', async ({ page }) => {
  // OpenMage admin URL
  const baseURL = process.env.OPENMAGE_URL || 'https://store.dev.local';

  // Navigate to OpenMage admin login page
  await page.goto(`${baseURL}/admin/`);

  // Wait for login form to be visible
  await expect(page.locator('form#loginForm')).toBeVisible({ timeout: 15000 });

  // Fill in login credentials from environment or use defaults
  const username = process.env.OPENMAGE_ADMIN_USERNAME || 'admin';
  const password = process.env.OPENMAGE_ADMIN_PASSWORD || 'ChangeTheP@ssw0rd';

  await page.fill('input#username', username);
  await page.fill('input#login', password);

  // Click login button
  await page.click('input.form-button[type="submit"]');

  // Wait for successful login - should redirect to admin dashboard
  // Look for Dashboard heading which confirms we're logged in
  await expect(page.getByRole('heading', { name: 'Dashboard', level: 3 })).toBeVisible({ timeout: 15000 });

  // Verify we're logged in by checking for the Dashboard link in navigation
  await expect(page.getByRole('link', { name: 'Dashboard' })).toBeVisible({ timeout: 10000 });

  // Save authentication state
  await page.context().storageState({ path: OPENMAGE_ADMIN_FILE });
});
