import { test, expect } from '@playwright/test';
import { JoomlaSiteUrls } from '../../helpers';

/**
 * Tests for MageBridge frontend root view.
 */
test.describe('MageBridge Site - Root View', () => {
  test.describe('CMS Content URL Handling', () => {
    test('should have properly formatted URLs in CMS banner links', async ({
      page,
    }) => {
      await page.goto(JoomlaSiteUrls.magebridge.root);
      await page.waitForLoadState('networkidle');

      // MageBridge content should be visible when integration is working
      const contentDiv = page.locator('#magebridge-content');
      await expect(contentDiv).toBeVisible();

      // Get all links in the content area
      const links = await contentDiv.locator('a[href]').all();

      for (const link of links) {
        const href = await link.getAttribute('href');

        if (!href) continue;

        // Skip anchor-only links and external Magento links
        if (href.startsWith('#') || href.includes('store.dev.local')) continue;

        // Check for malformed URLs (view=root followed immediately by path without & or space)
        // Bad: view=rootaccessories/eyewear.html
        // Good: view=root&request=accessories/eyewear.html
        const malformedPattern = /view=root[a-zA-Z0-9]/;
        expect(
          href,
          `URL "${href}" should not have path directly appended to view=root`
        ).not.toMatch(malformedPattern);
      }
    });

    test('should navigate to category page when clicking CMS banner link', async ({
      page,
    }) => {
      await page.goto(JoomlaSiteUrls.magebridge.root);
      await page.waitForLoadState('networkidle');

      // MageBridge content should be visible when integration is working
      const contentDiv = page.locator('#magebridge-content');
      await expect(contentDiv).toBeVisible();

      // Find first banner link with a proper request parameter
      // Note: Banner links may be hidden by CSS but still exist in DOM
      const bannerLink = contentDiv
        .locator('a[href*="request="]')
        .or(contentDiv.locator('a[href*="/store/"][href$=".html"]'))
        .first();

      // Should have at least one banner link (may be hidden due to CSS)
      const linkCount = await bannerLink.count();
      expect(linkCount).toBeGreaterThan(0);

      // Get the href to navigate directly (banner links may not be visible/clickable due to CSS)
      const href = await bannerLink.getAttribute('href');
      expect(href).toBeTruthy();

      // Navigate to the link URL directly instead of clicking
      // This avoids issues with overlapping elements or invisible links in banners
      await page.goto(href!);
      await page.waitForLoadState('networkidle');

      // Should not show 404 error
      await expect(page.locator('text=View not found')).not.toBeVisible();
      await expect(page.locator('h1:has-text("404")')).not.toBeVisible();

      // Page should load successfully
      const body = page.locator('body');
      await expect(body).toBeVisible();
    });
  });

  test.describe('Page Loading', () => {
    test('should load MageBridge frontend page', async ({ page }) => {
      await page.goto(JoomlaSiteUrls.magebridge.root);

      // Should not show fatal PHP error
      await expect(page.locator('text=Fatal error')).not.toBeVisible();
      await expect(page.locator('text=Parse error')).not.toBeVisible();

      const body = page.locator('body');
      await expect(body).toBeVisible();
    });

    test('should display content container when connected', async ({ page }) => {
      await page.goto(JoomlaSiteUrls.magebridge.root);
      await page.waitForLoadState('networkidle');

      // MageBridge content should be visible when integration is working
      const contentDiv = page.locator('#magebridge-content');
      await expect(contentDiv).toBeVisible();
    });

    test('should have proper content classes when Magento responds', async ({ page }) => {
      await page.goto(JoomlaSiteUrls.magebridge.root);
      await page.waitForLoadState('networkidle');

      // MageBridge content should be visible and have proper class
      const contentDiv = page.locator('#magebridge-content');
      await expect(contentDiv).toBeVisible();
      await expect(contentDiv).toHaveClass(/magebridge-content/);
    });
  });

  test.describe('Document Structure', () => {
    test('should load page with proper document structure', async ({ page }) => {
      await page.goto(JoomlaSiteUrls.magebridge.root);
      await page.waitForLoadState('networkidle');

      const html = page.locator('html');
      await expect(html).toBeVisible();

      const head = page.locator('head');
      const headExists = await head.count();
      expect(headExists).toBeGreaterThan(0);

      // MageBridge content should be visible when integration is working
      const contentDiv = page.locator('#magebridge-content');
      await expect(contentDiv).toBeVisible();
    });

    test('should not have critical JavaScript errors on page load', async ({ page }) => {
      const errors: string[] = [];
      page.on('pageerror', (error) => {
        errors.push(error.message);
      });

      await page.goto(JoomlaSiteUrls.magebridge.root);
      await page.waitForLoadState('networkidle');

      // Filter out known non-critical errors from Magento frontend JS
      const criticalErrors = errors.filter(
        (err) =>
          !err.includes('ResizeObserver') &&
          !err.includes('Non-Error') &&
          !err.includes('Catalog is not defined') &&
          !err.includes('Product is not defined') &&
          !err.includes('Varien is not defined') &&
          !err.includes('is not defined')
      );

      expect(criticalErrors).toHaveLength(0);
    });
  });
});
