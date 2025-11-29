import { test, expect } from '@playwright/test';
import { JoomlaAdminUrls } from '../../helpers';

/**
 * Tests for Joomla admin authentication.
 */
test.describe('Joomla Admin - Authentication', () => {
  test.describe('Login Page', () => {
    test('should display login form when not authenticated', async ({ page }) => {
      // Clear storage state to test unauthenticated access
      await page.context().clearCookies();

      await page.goto(JoomlaAdminUrls.login);

      // Should see login form
      await expect(page.locator('form#form-login')).toBeVisible();
      await expect(page.getByRole('textbox', { name: 'Username' })).toBeVisible();
      await expect(page.locator('input[name="passwd"]')).toBeVisible();
    });

    test('should show error for invalid credentials', async ({ page }) => {
      // Clear storage state
      await page.context().clearCookies();

      await page.goto(JoomlaAdminUrls.login);

      // Fill invalid credentials
      await page.getByRole('textbox', { name: 'Username' }).fill('invalid_user');
      await page.getByRole('textbox', { name: 'Password' }).fill('wrong_password');
      await page.getByRole('button', { name: 'Log in' }).click();

      // Should show error message (Joomla 5 uses role="alert")
      await expect(page.getByRole('alert')).toBeVisible({
        timeout: 10000,
      });

      // Verify the error message content
      await expect(page.getByRole('alert')).toContainText(/Username and password do not match/);
    });
  });

  test.describe('Authenticated Session', () => {
    test('should be logged in after setup', async ({ page }) => {
      // This test uses the authenticated state from setup
      await page.goto(JoomlaAdminUrls.dashboard);

      // Should be on admin dashboard, not login page
      await expect(page.locator('form#form-login')).not.toBeVisible();

      // Should see admin navigation menu
      await expect(page.getByRole('navigation', { name: 'Main Menu' })).toBeVisible();
    });

    test('should access MageBridge menu from Components', async ({ page }) => {
      await page.goto(JoomlaAdminUrls.dashboard);

      // MageBridge is under Components submenu, need to expand it first
      await page.getByRole('link', { name: 'Components', exact: true }).click();

      // Now MageBridge should be visible
      await expect(page.getByRole('link', { name: 'MageBridge' })).toBeVisible();

      // Click on MageBridge link
      await page.getByRole('link', { name: 'MageBridge' }).click();

      // Should see MageBridge submenu items
      await expect(page.getByRole('link', { name: 'Configuration' })).toBeVisible();
    });
  });
});
