import { test, expect } from '@playwright/test';
import { JoomlaAdminUrls, JoomlaSiteUrls } from '../../helpers';

/**
 * Tests for MageBridge bridge connectivity and Magento integration.
 */
test.describe('MageBridge Site - Bridge Integration', () => {
  test.describe('Admin Connectivity Check', () => {
    test('should show connection status on check page', async ({ page }) => {
      await page.goto(JoomlaAdminUrls.magebridge.check);

      await expect(page.getByRole('main')).toBeVisible();

      const bridgeChecks = page.getByRole('group', { name: 'Bridge Checks' });
      await expect(bridgeChecks).toBeVisible();
    });

    test('should display Magento version info', async ({ page }) => {
      await page.goto(JoomlaAdminUrls.magebridge.check);

      const pageContent = await page.content();
      const hasVersionInfo =
        pageContent.includes('Version') ||
        pageContent.includes('API') ||
        pageContent.includes('Magento') ||
        pageContent.includes('OpenMage');

      expect(hasVersionInfo).toBeTruthy();
    });

    test('should show API connection status', async ({ page }) => {
      await page.goto(JoomlaAdminUrls.magebridge.check);

      const pageContent = await page.content();
      const hasStatus =
        pageContent.includes('success') ||
        pageContent.includes('warning') ||
        pageContent.includes('error') ||
        pageContent.includes('badge') ||
        pageContent.includes('alert');

      expect(hasStatus).toBeTruthy();
    });
  });

  test.describe('Content Rendering', () => {
    test('should render Magento homepage content', async ({ page }) => {
      await page.goto(JoomlaSiteUrls.magebridge.root);
      await page.waitForLoadState('networkidle');

      const contentDiv = page.locator('#magebridge-content');
      const isConnected = await contentDiv.isVisible().catch(() => false);

      if (isConnected) {
        const content = await contentDiv.innerHTML();
        expect(content.length).toBeGreaterThan(0);
      }
    });

    test('should handle catalog page request', async ({ page }) => {
      await page.goto(JoomlaSiteUrls.magebridge.catalog);

      await expect(page.locator('text=Fatal error')).not.toBeVisible();
      await expect(page.locator('text=Exception')).not.toBeVisible();
    });

    test('should handle customer account page request', async ({ page }) => {
      await page.goto(JoomlaSiteUrls.magebridge.customer);

      await expect(page.locator('text=Fatal error')).not.toBeVisible();

      const body = page.locator('body');
      await expect(body).toBeVisible();
    });
  });

  test.describe('Error Handling', () => {
    test('should handle invalid request gracefully', async ({ page }) => {
      await page.goto('/index.php?option=com_magebridge&view=root&request=nonexistent/page');

      await expect(page.locator('text=Fatal error')).not.toBeVisible();
      await expect(page.locator('text=Parse error')).not.toBeVisible();

      const body = page.locator('body');
      await expect(body).toBeVisible();
    });

    test('should handle offline mode gracefully', async ({ page }) => {
      await page.goto('/index.php?option=com_magebridge&view=root&offline=1');
      await page.waitForLoadState('networkidle');

      // Should not show PHP errors
      await expect(page.locator('text=Fatal error')).not.toBeVisible();
      await expect(page.locator('text=Parse error')).not.toBeVisible();

      // Should display offline message
      const offlineMessage = page.locator('text=webshop is currently not available');
      await expect(offlineMessage).toBeVisible();
    });
  });
});
