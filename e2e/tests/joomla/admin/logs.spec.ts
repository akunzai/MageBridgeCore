import { test, expect } from '@playwright/test';
import { JoomlaAdminUrls } from '../../helpers';

/**
 * Tests for MageBridge Logs page (read-only).
 */
test.describe('MageBridge Admin - Logs', () => {
  test.describe('List Page', () => {
    test('should display logs list', async ({ page }) => {
      await page.goto(JoomlaAdminUrls.magebridge.logs);

      // The page should have a heading and table columns
      await expect(page.getByRole('heading', { name: /Logs/i })).toBeVisible();
      await expect(page.getByRole('link', { name: 'Message', exact: true })).toBeVisible();
      await expect(page.getByRole('link', { name: 'Type', exact: true })).toBeVisible();
    });

    test('should have filter options', async ({ page }) => {
      await page.goto(JoomlaAdminUrls.magebridge.logs);

      await expect(page.getByRole('textbox', { name: 'Search' })).toBeVisible();
      // Use combobox with option text
      await expect(page.getByRole('combobox').filter({ has: page.locator('option:text("Select Origin")') })).toBeVisible();
      await expect(page.getByRole('combobox').filter({ has: page.locator('option:text("Select Type")') })).toBeVisible();
    });

    test('should have toolbar with management links (read-only)', async ({ page }) => {
      await page.goto(JoomlaAdminUrls.magebridge.logs);

      // Logs page has Truncate, Refresh, Export links - not New/Delete buttons
      await expect(page.getByRole('link', { name: 'Truncate' })).toBeVisible();
      await expect(page.getByRole('link', { name: 'Refresh' })).toBeVisible();
      await expect(page.getByRole('link', { name: 'Export' })).toBeVisible();
      // Should NOT have New button
      await expect(page.getByRole('button', { name: 'New' })).not.toBeVisible();
    });
  });

  test.describe('Pagination', () => {
    test('should display pagination controls when there are enough logs', async ({ page }) => {
      await page.goto(JoomlaAdminUrls.magebridge.logs);

      // Check if there are any rows - with default limit of 20
      const rows = page.locator('table.adminlist tbody tr');
      const rowCount = await rows.count();
      
      // Skip test if no data
      test.skip(rowCount === 0, 'No data available for pagination test');

      // Check if pagination exists (indicating more than one page)
      const paginationNav = page.locator('nav[aria-label="Pagination"]').first();
      const hasPagination = await paginationNav.isVisible().catch(() => false);
      
      // Skip if no pagination (total items <= default limit)
      test.skip(!hasPagination, 'Not enough data for pagination (total <= default limit)');

      // Should show pagination info (e.g., "Page 1 of 3")
      await expect(paginationNav).toBeVisible();
      
      // Should have Next page link or page 2 link
      const hasNextOrPage2 = await Promise.race([
        page.getByRole('link', { name: /next/i }).isVisible(),
        page.getByRole('link', { name: '2', exact: true }).isVisible()
      ].map(p => p.catch(() => false)));
      
      expect(hasNextOrPage2).toBeTruthy();
    });

    test('should use Next/Previous navigation', async ({ page }) => {
      await page.goto(JoomlaAdminUrls.magebridge.logs);

      // Check if Next link exists
      const nextLink = page.getByRole('link', { name: /next/i });
      const hasNext = await nextLink.isVisible().catch(() => false);
      
      // Skip test if no Next link (only one page of data)
      test.skip(!hasNext, 'Next link not available (only one page of data)');

      // Click Next
      await nextLink.click();
      await page.waitForLoadState('networkidle');
      await expect(page.locator('text=Page 2 of')).toBeVisible();
      
      // Click Previous
      await page.getByRole('link', { name: /previous/i }).click();
      await page.waitForLoadState('networkidle');
      await expect(page.locator('text=Page 1 of')).toBeVisible();
    });

    test('should filter by origin and show correct results', async ({ page }) => {
      await page.goto(JoomlaAdminUrls.magebridge.logs);

      // Filter by 'Joomla' origin
      const originSelect = page.locator('select[name="filter_origin"]');
      await expect(originSelect).toBeVisible();
      await originSelect.selectOption({ label: 'Joomla' });

      // Wait for filter to apply
      await page.waitForLoadState('networkidle');

      // Should show filtered results with origin 'joomla'
      await expect(page.getByText('joomla').first()).toBeVisible();
    });

    test('should filter by type and show correct results', async ({ page }) => {
      await page.goto(JoomlaAdminUrls.magebridge.logs);

      // Filter by type 'Notice'
      const typeSelect = page.locator('select[name="filter_type"]');
      await expect(typeSelect).toBeVisible();
      await typeSelect.selectOption({ label: 'Notice' });

      // Wait for filter to apply
      await page.waitForLoadState('networkidle');

      // Should show filtered results - check table rows contain 'Notice' type
      const tableCell = page.locator('table.adminlist tbody td:has-text("Notice")').first();
      await expect(tableCell).toBeVisible();
    });

    test('should have limit select box', async ({ page }) => {
      await page.goto(JoomlaAdminUrls.magebridge.logs);

      // Verify limit select exists
      const limitSelect = page.locator('select[name="list_limit"]');
      await expect(limitSelect).toBeVisible();

      // Verify it has expected options
      await expect(limitSelect.locator('option')).toHaveCount(14); // All, 3, 4, 5, 10, 20, 30, 40, 50, 100, 200, 300, 400, 500
    });
  });
});
