import { test, expect } from '@playwright/test';

test.describe('Admin Login', () => {
  test('should display login page', async ({ page }) => {
    // Clear storage state to test unauthenticated access
    await page.context().clearCookies();

    await page.goto('/administrator/');

    // Should see login form
    await expect(page.locator('form#form-login')).toBeVisible();
    await expect(page.getByRole('textbox', { name: 'Username' })).toBeVisible();
    await expect(page.locator('input[name="passwd"]')).toBeVisible();
  });

  test('should show error for invalid credentials', async ({ page }) => {
    // Clear storage state
    await page.context().clearCookies();

    await page.goto('/administrator/');

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

  test('should be logged in after setup', async ({ page }) => {
    // This test uses the authenticated state from setup
    await page.goto('/administrator/index.php');

    // Should be on admin dashboard, not login page
    await expect(page.locator('form#form-login')).not.toBeVisible();

    // Should see admin navigation menu
    await expect(page.getByRole('navigation', { name: 'Main Menu' })).toBeVisible();
  });

  test('should access MageBridge menu', async ({ page }) => {
    await page.goto('/administrator/index.php');

    // MageBridge is under Components submenu, need to expand it first
    // Use exact: true to avoid matching "Components Dashboard"
    await page.getByRole('link', { name: 'Components', exact: true }).click();

    // Now MageBridge should be visible
    await expect(page.getByRole('link', { name: 'MageBridge' })).toBeVisible();

    // Click on MageBridge link
    await page.getByRole('link', { name: 'MageBridge' }).click();

    // Should see MageBridge submenu items
    await expect(page.getByRole('link', { name: 'Configuration' })).toBeVisible();
  });
});
