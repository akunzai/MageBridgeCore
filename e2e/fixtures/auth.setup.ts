import { test as setup, expect } from '@playwright/test';

const ADMIN_FILE = 'playwright/.auth/admin.json';

/**
 * Setup authentication state for admin user.
 * This runs once before all tests and saves the authenticated state.
 */
setup('authenticate as admin', async ({ page }) => {
  // Navigate to Joomla admin login page
  await page.goto('/administrator/');

  // Wait for login form to be visible
  await expect(page.locator('form#form-login')).toBeVisible();

  // Fill in login credentials from environment or use defaults
  const username = process.env.ADMIN_USERNAME || 'admin';
  const password = process.env.ADMIN_PASSWORD || 'ChangeTheP@ssw0rd';

  await page.fill('input[name="username"]', username);
  await page.fill('input[name="passwd"]', password);

  // Click login button
  await page.click('button[type="submit"]');

  // Wait for successful login - should redirect to admin dashboard
  await expect(page).toHaveURL(/\/administrator\/index\.php/);

  // Verify we're logged in by checking for admin menu or user dropdown
  await expect(
    page.locator('.header-item-content').first()
  ).toBeVisible({ timeout: 10000 });

  // Save authentication state
  await page.context().storageState({ path: ADMIN_FILE });
});
