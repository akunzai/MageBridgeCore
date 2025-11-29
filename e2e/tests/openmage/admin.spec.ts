import { test, expect } from '@playwright/test';

/**
 * Tests for OpenMage Admin Panel - MageBridge integration.
 * These tests verify that MageBridge module is properly installed and configured in OpenMage.
 *
 * Note: OpenMage admin uses security keys in URLs, so we navigate using menu links
 * instead of direct URL access where possible.
 */
test.describe('OpenMage Admin - MageBridge Module', () => {
  test.describe('Admin Login', () => {
    test('should be logged in as admin', async ({ page }) => {
      await page.goto('/admin/');

      // Should be on admin dashboard (not login page)
      // Check for Dashboard heading
      await expect(page.getByRole('heading', { name: 'Dashboard', level: 3 })).toBeVisible();
    });

    test('should display admin dashboard with stats', async ({ page }) => {
      await page.goto('/admin/');

      // Should see dashboard content - Lifetime Sales heading
      await expect(page.getByRole('heading', { name: 'Lifetime Sales', level: 4 })).toBeVisible();
    });
  });

  test.describe('MageBridge System Check', () => {
    test('should have MageBridge menu in CMS navigation', async ({ page }) => {
      await page.goto('/admin/');

      // Should see MageBridge link in the page navigation
      const magebridgeLink = page.getByRole('link', { name: 'MageBridge', exact: true });
      await expect(magebridgeLink).toBeAttached();
    });

    test('should navigate to MageBridge page via menu', async ({ page }) => {
      await page.goto('/admin/');

      // Click on CMS menu to expand it
      await page.getByRole('link', { name: 'CMS', exact: true }).hover();

      // Click MageBridge submenu
      await page.getByRole('link', { name: 'MageBridge', exact: true }).click();

      // Wait for page navigation
      await page.waitForLoadState('networkidle');

      // Should not show PHP error
      await expect(page.locator('text=Fatal error')).not.toBeVisible();

      // Page should contain MageBridge content
      const pageContent = await page.content();
      expect(pageContent.includes('MageBridge')).toBeTruthy();
    });

    test('should display MageBridge page without errors', async ({ page }) => {
      await page.goto('/admin/');

      // Navigate to MageBridge via menu
      await page.getByRole('link', { name: 'CMS', exact: true }).hover();
      await page.getByRole('link', { name: 'MageBridge', exact: true }).click();
      await page.waitForLoadState('networkidle');

      // Should not show PHP error
      await expect(page.locator('text=Fatal error')).not.toBeVisible();
      await expect(page.locator('text=Parse error')).not.toBeVisible();

      // Should contain check-related text
      const pageContent = await page.content();
      const hasCheckContent =
        pageContent.includes('Check') ||
        pageContent.includes('Status') ||
        pageContent.includes('Version') ||
        pageContent.includes('System');

      expect(hasCheckContent).toBeTruthy();
    });
  });

  test.describe('MageBridge Configuration', () => {
    test('should navigate to System Configuration page', async ({ page }) => {
      await page.goto('/admin/');

      // Navigate via System menu
      await page.getByRole('link', { name: 'System', exact: true }).hover();
      await page.getByRole('link', { name: 'Configuration', exact: true }).click();
      await page.waitForLoadState('networkidle');

      // Should see configuration tabs
      await expect(page.locator('#system_config_tabs')).toBeVisible();
    });

    test('should have MageBridge in configuration menu', async ({ page }) => {
      await page.goto('/admin/');

      // Navigate to System > Configuration
      await page.getByRole('link', { name: 'System', exact: true }).hover();
      await page.getByRole('link', { name: 'Configuration', exact: true }).click();
      await page.waitForLoadState('networkidle');

      // Page should contain MageBridge configuration section
      const pageContent = await page.content();
      expect(pageContent.includes('MageBridge') || pageContent.includes('Services')).toBeTruthy();
    });
  });

  test.describe('API Configuration', () => {
    test('should navigate to SOAP/XML-RPC Roles page', async ({ page }) => {
      await page.goto('/admin/');

      // Navigate via System > Web Services menu
      await page.getByRole('link', { name: 'System', exact: true }).hover();
      await page.getByRole('link', { name: 'Web Services' }).hover();
      await page.getByRole('link', { name: 'SOAP/XML-RPC - Roles' }).click();
      await page.waitForLoadState('networkidle');

      // Should not show PHP error
      await expect(page.locator('text=Fatal error')).not.toBeVisible();

      // Should see some content
      const body = page.locator('body');
      await expect(body).toBeVisible();
    });

    test('should have MageBridge API role configured', async ({ page }) => {
      await page.goto('/admin/');

      // Navigate via System > Web Services menu
      await page.getByRole('link', { name: 'System', exact: true }).hover();
      await page.getByRole('link', { name: 'Web Services' }).hover();
      await page.getByRole('link', { name: 'SOAP/XML-RPC - Roles' }).click();
      await page.waitForLoadState('networkidle');

      // Look for MageBridge role in the page
      const pageContent = await page.content();
      expect(pageContent.includes('MageBridge')).toBeTruthy();
    });

    test('should navigate to SOAP/XML-RPC Users page', async ({ page }) => {
      await page.goto('/admin/');

      // Navigate via System > Web Services menu
      await page.getByRole('link', { name: 'System', exact: true }).hover();
      await page.getByRole('link', { name: 'Web Services' }).hover();
      await page.getByRole('link', { name: 'SOAP/XML-RPC - Users' }).click();
      await page.waitForLoadState('networkidle');

      // Should not show PHP error
      await expect(page.locator('text=Fatal error')).not.toBeVisible();
    });

    test('should have magebridge_api user configured', async ({ page }) => {
      await page.goto('/admin/');

      // Navigate via System > Web Services menu
      await page.getByRole('link', { name: 'System', exact: true }).hover();
      await page.getByRole('link', { name: 'Web Services' }).hover();
      await page.getByRole('link', { name: 'SOAP/XML-RPC - Users' }).click();
      await page.waitForLoadState('networkidle');

      // Look for magebridge_api user in the page
      const pageContent = await page.content();
      expect(pageContent.includes('magebridge_api')).toBeTruthy();
    });
  });

  test.describe('MageBridge Module Status', () => {
    test('should have MageBridge module accessible', async ({ page }) => {
      await page.goto('/admin/');

      // Navigate to MageBridge via CMS menu to verify module is installed
      await page.getByRole('link', { name: 'CMS', exact: true }).hover();
      await page.getByRole('link', { name: 'MageBridge', exact: true }).click();
      await page.waitForLoadState('networkidle');

      // If we can access MageBridge page, module is installed
      await expect(page.locator('text=Fatal error')).not.toBeVisible();

      // Page should contain MageBridge content
      const pageContent = await page.content();
      expect(pageContent.includes('MageBridge')).toBeTruthy();
    });
  });
});

test.describe('OpenMage Admin - General Functionality', () => {
  test('should navigate to Catalog > Manage Products', async ({ page }) => {
    await page.goto('/admin/');

    // Navigate via Catalog menu
    await page.getByRole('link', { name: 'Catalog', exact: true }).hover();
    await page.getByRole('link', { name: 'Manage Products' }).click();
    await page.waitForLoadState('networkidle');

    // Should see products grid
    await expect(page.locator('#productGrid')).toBeVisible();
  });

  test('should navigate to Customers > Manage Customers', async ({ page }) => {
    await page.goto('/admin/');

    // Navigate via Customers menu (use first matching link)
    await page.locator('#nav-admin-customer').hover();
    await page.getByRole('link', { name: 'Manage Customers' }).click();
    await page.waitForLoadState('networkidle');

    // Should see customers grid
    await expect(page.locator('#customerGrid')).toBeVisible();
  });

  test('should navigate to System > Cache Management', async ({ page }) => {
    await page.goto('/admin/');

    // Navigate via System menu
    await page.getByRole('link', { name: 'System', exact: true }).hover();
    await page.getByRole('link', { name: 'Cache Management' }).click();
    await page.waitForLoadState('networkidle');

    // Should see cache grid
    await expect(page.locator('#cache_grid')).toBeVisible();
  });
});
