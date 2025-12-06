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

  // Handle first-time welcome tour dialog if it appears
  try {
    const welcomeDialog = page.getByRole('dialog', {
      name: /welcome to joomla/i,
    });
    await welcomeDialog.waitFor({ state: 'visible', timeout: 3000 });
    // Click "Hide Forever" to dismiss the welcome tour permanently
    await welcomeDialog.getByRole('button', { name: /hide forever/i }).click();
    // Wait for dialog to be dismissed and overlay to disappear
    await page.waitForSelector('.shepherd-modal-is-visible', {
      state: 'detached',
      timeout: 5000,
    });
  } catch (error) {
    // Dialog didn't appear, which is fine
  }

  // Handle Joomla Statistics opt-in dialog if it appears
  try {
    const statsButton = page.getByRole('button', { name: 'No', exact: true });
    await statsButton.waitFor({ state: 'visible', timeout: 3000 });
    await statsButton.click();
    // Wait a moment for the action to complete
    await page.waitForTimeout(1000);
  } catch (error) {
    // Dialog didn't appear, which is fine
  }

  // Save authentication state
  await page.context().storageState({ path: ADMIN_FILE });
});
