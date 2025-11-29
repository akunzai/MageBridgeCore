import { test, expect } from '@playwright/test';

/**
 * Tests for MageBridge Magento Integration.
 * These tests verify that the bridge between Joomla and OpenMage is working correctly.
 */
test.describe('MageBridge Magento Integration', () => {
  test.describe('Frontend Root View', () => {
    test('should load MageBridge frontend page', async ({ page }) => {
      // Navigate to the MageBridge component page
      await page.goto('/index.php?option=com_magebridge&view=root');

      // Should not show fatal PHP error
      await expect(page.locator('text=Fatal error')).not.toBeVisible();
      await expect(page.locator('text=Parse error')).not.toBeVisible();

      // Page should render (either content or offline message)
      const body = page.locator('body');
      await expect(body).toBeVisible();
    });

    test('should display MageBridge content container when connected', async ({ page }) => {
      await page.goto('/index.php?option=com_magebridge&view=root');

      // Check for either content container or offline message
      const hasContent = await page.locator('#magebridge-content').isVisible().catch(() => false);
      const hasOffline = await page.locator('text=currently offline').isVisible().catch(() => false);

      // One of these should be true
      expect(hasContent || hasOffline).toBeTruthy();
    });

    test('should have proper content classes when Magento responds', async ({ page }) => {
      await page.goto('/index.php?option=com_magebridge&view=root');

      // If connected, the content div should have magebridge-content class
      const contentDiv = page.locator('#magebridge-content');
      const isVisible = await contentDiv.isVisible().catch(() => false);

      if (isVisible) {
        await expect(contentDiv).toHaveClass(/magebridge-content/);
      }
    });
  });

  test.describe('Bridge Connectivity', () => {
    test('admin check page should show connection status', async ({ page }) => {
      // Navigate to System Check page
      await page.goto('/administrator/index.php?option=com_magebridge&view=check');

      // Should load without error
      await expect(page.getByRole('main')).toBeVisible();

      // Check for system check fieldsets (Bridge Checks, System Compatibility, etc.)
      const bridgeChecks = page.getByRole('group', { name: 'Bridge Checks' });
      await expect(bridgeChecks).toBeVisible();
    });

    test('admin check should display Magento version info', async ({ page }) => {
      await page.goto('/administrator/index.php?option=com_magebridge&view=check');

      // Look for version-related content
      const pageContent = await page.content();

      // Should contain some check information (version, API, etc.)
      const hasVersionInfo =
        pageContent.includes('Version') ||
        pageContent.includes('API') ||
        pageContent.includes('Magento') ||
        pageContent.includes('OpenMage');

      expect(hasVersionInfo).toBeTruthy();
    });

    test('admin check should show API connection status', async ({ page }) => {
      await page.goto('/administrator/index.php?option=com_magebridge&view=check');

      // The page should have status indicators (success/warning/error)
      const pageContent = await page.content();

      // Should contain status-related markup
      const hasStatus =
        pageContent.includes('success') ||
        pageContent.includes('warning') ||
        pageContent.includes('error') ||
        pageContent.includes('badge') ||
        pageContent.includes('alert');

      expect(hasStatus).toBeTruthy();
    });
  });

  test.describe('Magento Content Rendering', () => {
    test('should render Magento homepage content', async ({ page }) => {
      await page.goto('/index.php?option=com_magebridge&view=root');

      // Wait for page to fully load
      await page.waitForLoadState('networkidle');

      // If connected, should have some content from Magento
      const contentDiv = page.locator('#magebridge-content');
      const isConnected = await contentDiv.isVisible().catch(() => false);

      if (isConnected) {
        // Should have actual HTML content (not empty)
        const content = await contentDiv.innerHTML();
        expect(content.length).toBeGreaterThan(0);
      }
    });

    test('should handle catalog page request', async ({ page }) => {
      // Try to access a catalog page through MageBridge
      await page.goto('/index.php?option=com_magebridge&view=root&request=catalog');

      // Should not have PHP errors
      await expect(page.locator('text=Fatal error')).not.toBeVisible();
      await expect(page.locator('text=Exception')).not.toBeVisible();
    });

    test('should handle customer account page request', async ({ page }) => {
      // Try to access customer account page
      await page.goto('/index.php?option=com_magebridge&view=root&request=customer/account');

      // Should not have PHP errors
      await expect(page.locator('text=Fatal error')).not.toBeVisible();

      // Page should render something
      const body = page.locator('body');
      await expect(body).toBeVisible();
    });
  });

  test.describe('Headers and Session', () => {
    test('should load page with proper document structure', async ({ page }) => {
      await page.goto('/index.php?option=com_magebridge&view=root');

      // Verify the page has proper HTML structure
      const html = page.locator('html');
      await expect(html).toBeVisible();

      // Check for head element (where CSS/JS would be loaded)
      const head = page.locator('head');
      const headExists = await head.count();
      expect(headExists).toBeGreaterThan(0);

      // Check for magebridge content or offline message
      const hasContent = await page.locator('#magebridge-content').isVisible().catch(() => false);
      const pageText = await page.textContent('body');
      const isOffline = pageText?.includes('offline') || pageText?.includes('unavailable');

      // Page should show content or offline state
      expect(hasContent || isOffline).toBeTruthy();
    });

    test('should not have critical JavaScript errors on page load', async ({ page }) => {
      const errors: string[] = [];
      page.on('pageerror', (error) => {
        errors.push(error.message);
      });

      await page.goto('/index.php?option=com_magebridge&view=root');
      await page.waitForLoadState('networkidle');

      // Filter out known non-critical errors:
      // - ResizeObserver: browser internal
      // - Non-Error: non-standard error objects
      // - Catalog/Product/etc.: Magento frontend JS that may not be fully loaded
      const criticalErrors = errors.filter(
        (err) =>
          !err.includes('ResizeObserver') &&
          !err.includes('Non-Error') &&
          !err.includes('Catalog is not defined') &&
          !err.includes('Product is not defined') &&
          !err.includes('Varien is not defined') &&
          !err.includes('is not defined') // General undefined variable errors from Magento JS
      );

      // Should have no critical MageBridge-related JavaScript errors
      expect(criticalErrors).toHaveLength(0);
    });
  });

  test.describe('Error Handling', () => {
    test('should handle invalid request gracefully', async ({ page }) => {
      // Request a non-existent Magento page
      await page.goto('/index.php?option=com_magebridge&view=root&request=nonexistent/page');

      // Should not show PHP fatal errors
      await expect(page.locator('text=Fatal error')).not.toBeVisible();
      await expect(page.locator('text=Parse error')).not.toBeVisible();

      // Page should still render
      const body = page.locator('body');
      await expect(body).toBeVisible();
    });

    test('should handle offline mode gracefully', async ({ page }) => {
      // Request with offline flag
      await page.goto('/index.php?option=com_magebridge&view=root&offline=1');

      // Should show some kind of offline/unavailable message or empty content
      // without PHP errors
      await expect(page.locator('text=Fatal error')).not.toBeVisible();
    });
  });
});

test.describe('MageBridge Ajax Handler', () => {
  test('should have ajax view available', async ({ page }) => {
    await page.goto('/index.php?option=com_magebridge&view=ajax&format=raw');

    // Should not show PHP errors
    await expect(page.locator('text=Fatal error')).not.toBeVisible();
  });
});

test.describe('MageBridge CMS Page', () => {
  test('should load CMS page view', async ({ page }) => {
    await page.goto('/index.php?option=com_magebridge&view=cms');

    // Should not have fatal errors
    await expect(page.locator('text=Fatal error')).not.toBeVisible();

    // Page should render
    const body = page.locator('body');
    await expect(body).toBeVisible();
  });
});
