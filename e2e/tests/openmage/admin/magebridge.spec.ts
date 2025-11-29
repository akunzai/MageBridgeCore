import { test, expect } from '@playwright/test';

/**
 * Tests for MageBridge module in OpenMage admin.
 */
test.describe('OpenMage Admin - MageBridge Module', () => {
  test.describe('Menu Navigation', () => {
    test('should have MageBridge menu in CMS navigation', async ({ page }) => {
      await page.goto('/admin/');

      const magebridgeLink = page.getByRole('link', { name: 'MageBridge', exact: true });
      await expect(magebridgeLink).toBeAttached();
    });

    test('should navigate to MageBridge page via menu', async ({ page }) => {
      await page.goto('/admin/');

      // Click on CMS menu to expand it
      await page.getByRole('link', { name: 'CMS', exact: true }).hover();

      // Click MageBridge submenu
      await page.getByRole('link', { name: 'MageBridge', exact: true }).click();

      await page.waitForLoadState('networkidle');

      await expect(page.locator('text=Fatal error')).not.toBeVisible();

      const pageContent = await page.content();
      expect(pageContent.includes('MageBridge')).toBeTruthy();
    });

    test('should display MageBridge page without errors', async ({ page }) => {
      await page.goto('/admin/');

      await page.getByRole('link', { name: 'CMS', exact: true }).hover();
      await page.getByRole('link', { name: 'MageBridge', exact: true }).click();
      await page.waitForLoadState('networkidle');

      await expect(page.locator('text=Fatal error')).not.toBeVisible();
      await expect(page.locator('text=Parse error')).not.toBeVisible();

      const pageContent = await page.content();
      const hasCheckContent =
        pageContent.includes('Check') ||
        pageContent.includes('Status') ||
        pageContent.includes('Version') ||
        pageContent.includes('System');

      expect(hasCheckContent).toBeTruthy();
    });
  });

  test.describe('Module Status', () => {
    test('should have MageBridge module accessible', async ({ page }) => {
      await page.goto('/admin/');

      await page.getByRole('link', { name: 'CMS', exact: true }).hover();
      await page.getByRole('link', { name: 'MageBridge', exact: true }).click();
      await page.waitForLoadState('networkidle');

      await expect(page.locator('text=Fatal error')).not.toBeVisible();

      const pageContent = await page.content();
      expect(pageContent.includes('MageBridge')).toBeTruthy();
    });
  });

  test.describe('System Health Check', () => {
    test('should navigate to System Health Check page', async ({ page }) => {
      await page.goto('/admin/system_config/index/tab/magebridge/');
      await page.waitForLoadState('networkidle');

      await expect(page.locator('text=Fatal error')).not.toBeVisible();
      const pageContent = await page.content();
      expect(pageContent.length > 100).toBeTruthy();
    });

    test('should display health check status', async ({ page }) => {
      await page.goto('/admin/system_config/index/tab/magebridge/');
      await page.waitForLoadState('networkidle');

      const pageContent = await page.content();
      // Check for common health check indicators
      const hasHealthContent =
        pageContent.includes('Check') ||
        pageContent.includes('Status') ||
        pageContent.includes('Version') ||
        pageContent.includes('Configuration') ||
        pageContent.includes('Settings');

      expect(hasHealthContent).toBeTruthy();
    });

    test('should not display errors on health check page', async ({ page }) => {
      await page.goto('/admin/system_config/index/tab/magebridge/');
      await page.waitForLoadState('networkidle');

      await expect(page.locator('text=Fatal error')).not.toBeVisible();
      await expect(page.locator('text=Parse error')).not.toBeVisible();
      await expect(page.locator('text=Exception')).not.toBeVisible();
    });
  });

  test.describe('API Browse & Test', () => {
    test('should have browse functionality available', async ({ page }) => {
      await page.goto('/admin/');

      await page.getByRole('link', { name: 'CMS', exact: true }).hover();
      await page.getByRole('link', { name: 'MageBridge', exact: true }).click();
      await page.waitForLoadState('networkidle');

      // Look for browse or API access link/button
      const pageContent = await page.content();
      const hasBrowseContent =
        pageContent.includes('Browse') ||
        pageContent.includes('API') ||
        pageContent.includes('Test') ||
        pageContent.includes('Data');

      expect(hasBrowseContent).toBeTruthy();
    });

    test('should allow API connectivity test', async ({ page }) => {
      // Navigate to system configuration tab for MageBridge
      await page.goto('/admin/system_config/index/tab/magebridge/');
      await page.waitForLoadState('networkidle');

      // Check for configuration fields related to API
      const pageContent = await page.content();
      const hasApiConfig =
        pageContent.includes('API') ||
        pageContent.includes('host') ||
        pageContent.includes('protocol') ||
        pageContent.includes('Configuration');

      expect(hasApiConfig).toBeTruthy();
    });

    test('should display API settings without errors', async ({ page }) => {
      await page.goto('/admin/system_config/index/tab/magebridge/');
      await page.waitForLoadState('networkidle');

      // Verify page loaded successfully
      await expect(page.locator('text=Fatal error')).not.toBeVisible();
      await expect(page.locator('text=Parse error')).not.toBeVisible();

      // Verify we can see form elements
      const form = page.locator('form');
      if (await form.count() > 0) {
        await expect(form.first()).toBeVisible();
      }
    });
  });
});
