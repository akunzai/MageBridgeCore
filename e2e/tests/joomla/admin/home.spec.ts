import { test, expect } from '@playwright/test';
import { JoomlaAdminUrls, AdminPages } from '../../helpers';

/**
 * Tests for MageBridge admin Home page and navigation.
 */
test.describe('MageBridge Admin - Home Page', () => {
  test('should load home page without errors', async ({ page }) => {
    await page.goto(JoomlaAdminUrls.magebridge.home);

    // Should not show error
    await expect(page.locator('.alert-danger, .alert-error')).not.toBeVisible({
      timeout: 5000,
    });

    // Page should have main content area
    await expect(page.getByRole('main')).toBeVisible();
  });

  test('should display Magento Backend link', async ({ page }) => {
    await page.goto(JoomlaAdminUrls.magebridge.home);

    // Find Magento Backend icon link (it's an image link)
    const magentoLink = page
      .locator('a')
      .filter({ has: page.locator('img[alt*="Magento"]') });

    // Get the href to verify it points to view=magento
    const href = await magentoLink.getAttribute('href');
    expect(href).toContain('view=magento');
  });

  test('should have view=magento endpoint accessible', async ({ page }) => {
    // Navigate to view=magento - this should either:
    // 1. Redirect to Magento admin (cross-domain)
    // 2. Or stay on Joomla but not show 404 error
    const response = await page.goto(JoomlaAdminUrls.magebridge.magento);

    // The endpoint should be accessible (not 404)
    expect(response?.status()).not.toBe(404);

    // Should NOT see Joomla "View not found" error
    await expect(page.getByText('View not found')).not.toBeVisible();
  });
});

test.describe('MageBridge Admin - Clean Cache', () => {
  test('should display Clean Cache button with task parameter', async ({
    page,
  }) => {
    await page.goto(JoomlaAdminUrls.magebridge.home);

    // Find Clean Cache icon link
    const cacheLink = page
      .locator('a')
      .filter({ has: page.locator('img[alt*="Clean Cache"]') });

    await expect(cacheLink).toBeVisible();

    // Verify the link uses task=cache (not view=cache)
    const href = await cacheLink.getAttribute('href');
    expect(href).toContain('task=cache');
    expect(href).not.toContain('view=cache');
  });

  test('should clean cache and show success message', async ({ page }) => {
    await page.goto(JoomlaAdminUrls.magebridge.home);

    // Find and click Clean Cache link
    const cacheLink = page
      .locator('a')
      .filter({ has: page.locator('img[alt*="Clean Cache"]') });

    await cacheLink.click();
    await page.waitForLoadState('networkidle');

    // Should redirect back to home page
    await expect(page).toHaveURL(/view=home/);

    // Should show success message in alert container
    const successAlert = page.locator('.alert-success, .alert-message');
    await expect(successAlert.filter({ hasText: /cache.*clean/i })).toBeVisible();

    // Should not show error
    await expect(page.locator('.alert-danger, .alert-error')).not.toBeVisible();
  });
});

test.describe('MageBridge Admin - Page Navigation', () => {
  for (const { name, view } of AdminPages) {
    test(`should load ${name} page`, async ({ page }) => {
      await page.goto(
        `/administrator/index.php?option=com_magebridge&view=${view}`
      );

      // Should not show error
      await expect(page.locator('.alert-danger, .alert-error')).not.toBeVisible(
        { timeout: 5000 }
      );

      // Page should have main content area
      await expect(page.getByRole('main')).toBeVisible();
    });
  }
});
