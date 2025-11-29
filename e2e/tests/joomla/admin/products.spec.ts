import { test, expect } from '@playwright/test';
import { JoomlaAdminUrls } from '../../helpers';

/**
 * Tests for MageBridge Products management.
 */
test.describe('MageBridge Admin - Products', () => {
  test.describe('List Page', () => {
    test('should display products list', async ({ page }) => {
      await page.goto(JoomlaAdminUrls.magebridge.products);

      await expect(page.getByRole('navigation', { name: 'Toolbar' })).toBeVisible();
      await expect(page.getByRole('link', { name: 'Label', exact: true })).toBeVisible();
      await expect(page.getByRole('link', { name: 'Product SKU' })).toBeVisible();
    });

    test('should have New button', async ({ page }) => {
      await page.goto(JoomlaAdminUrls.magebridge.products);
      await expect(page.getByRole('button', { name: 'New' })).toBeVisible();
    });

    test('should open new product form', async ({ page }) => {
      await page.goto(JoomlaAdminUrls.magebridge.products);
      await page.getByRole('button', { name: 'New' }).click();

      await expect(page).toHaveURL(/view=product|task=.*\.add/);
      await expect(page.locator('#adminForm')).toBeVisible();
    });

    test('should have list_limit select', async ({ page }) => {
      await page.goto(JoomlaAdminUrls.magebridge.products);

      const limitSelect = page.locator('select[name="list_limit"]');
      await expect(limitSelect).toBeVisible();
      await expect(limitSelect.locator('option')).toHaveCount(14);
    });
  });

  test.describe.serial('CRUD Operations', () => {
    const timestamp = Date.now();
    const testLabel = `E2E Product ${timestamp}`;
    const testSku = `E2E-${timestamp}`;
    const updatedLabel = `E2E Updated ${timestamp}`;

    test('should create a new product', async ({ page }) => {
      await page.goto(JoomlaAdminUrls.magebridge.products);
      await page.getByRole('button', { name: 'New' }).click();

      await expect(page.locator('#adminForm')).toBeVisible();
      await page.locator('input[name="label"]').fill(testLabel);
      await page.locator('input[name="sku"]').fill(testSku);
      await page.getByRole('button', { name: 'Save & Close' }).click();

      await page.waitForURL(/view=products/, { timeout: 10000 });

      // Search for the created product using SKU (no spaces) to handle pagination
      await page.locator('input[name="filter_search"]').fill(testSku);
      await page.getByRole('button', { name: 'Search' }).click();
      await page.waitForLoadState('networkidle');

      await expect(page.getByText(testLabel)).toBeVisible({ timeout: 10000 });
    });

    test('should read/view a product', async ({ page }) => {
      await page.goto(JoomlaAdminUrls.magebridge.products);

      // Search for the product using SKU (no spaces) to handle pagination
      await page.locator('input[name="filter_search"]').fill(testSku);
      await page.getByRole('button', { name: 'Search' }).click();
      await page.waitForLoadState('networkidle');

      await page.getByRole('link', { name: testLabel }).click();

      await expect(page.locator('#adminForm')).toBeVisible();
      await expect(page.locator('input[name="label"]')).toHaveValue(testLabel);
    });

    test('should update a product', async ({ page }) => {
      await page.goto(JoomlaAdminUrls.magebridge.products);

      // Search for the product using SKU (no spaces) to handle pagination
      await page.locator('input[name="filter_search"]').fill(testSku);
      await page.getByRole('button', { name: 'Search' }).click();
      await page.waitForLoadState('networkidle');

      await page.getByRole('link', { name: testLabel }).click();

      await page.locator('input[name="label"]').fill(updatedLabel);
      await page.getByRole('button', { name: 'Save & Close' }).click();

      await expect(page).toHaveURL(/view=products/);

      // Search for the updated product using SKU (no spaces)
      await page.locator('input[name="filter_search"]').fill(testSku);
      await page.getByRole('button', { name: 'Search' }).click();
      await page.waitForLoadState('networkidle');

      await expect(page.getByText(updatedLabel)).toBeVisible();
    });

    test('should delete a product', async ({ page }) => {
      await page.goto(JoomlaAdminUrls.magebridge.products);

      // Search for the product using SKU (no spaces) to handle pagination
      await page.locator('input[name="filter_search"]').fill(testSku);
      await page.getByRole('button', { name: 'Search' }).click();
      await page.waitForLoadState('networkidle');

      const row = page.locator('tr', { has: page.getByText(updatedLabel) });
      await row.locator('input[type="checkbox"][name="cid[]"]').check();

      await page.getByRole('button', { name: 'Delete' }).click();
      await page.waitForLoadState('networkidle');

      await expect(page.getByText(updatedLabel)).not.toBeVisible();
    });
  });

  test.describe('Filters', () => {
    test('should have search input and clear button', async ({ page }) => {
      await page.goto(JoomlaAdminUrls.magebridge.products);

      // Verify filter controls exist
      const searchInput = page.locator('input[name="filter_search"]');
      await expect(searchInput).toBeVisible();

      await expect(page.getByRole('button', { name: 'Search' })).toBeVisible();
      await expect(page.getByRole('button', { name: 'Clear' })).toBeVisible();
    });

    test('should clear search filter', async ({ page }) => {
      await page.goto(JoomlaAdminUrls.magebridge.products);

      // Clear any existing filters first
      await page.getByRole('button', { name: 'Clear' }).click();
      await page.waitForLoadState('networkidle');

      // Check if there's data available
      const noItemsMessage = page.getByText('No items found');
      const hasNoItems = await noItemsMessage.isVisible().catch(() => false);
      test.skip(hasNoItems, 'No products available');

      // Get baseline count
      const baselineRows = page.locator('table.adminlist tbody tr:has(input[type="checkbox"])');
      const baselineCount = await baselineRows.count();

      // Apply a search filter
      const searchInput = page.locator('input[name="filter_search"]');
      await searchInput.fill('test-nonexistent-term');
      await page.getByRole('button', { name: 'Search' }).click();
      await page.waitForLoadState('networkidle');

      // Clear filter
      await page.getByRole('button', { name: 'Clear' }).click();
      await page.waitForLoadState('networkidle');

      // Search input should be cleared
      await expect(searchInput).toHaveValue('');

      // Should return to baseline count
      const clearedRows = page.locator('table.adminlist tbody tr:has(input[type="checkbox"])');
      const clearedCount = await clearedRows.count();

      expect(clearedCount).toBeGreaterThanOrEqual(baselineCount - 1);
      expect(clearedCount).toBeLessThanOrEqual(baselineCount + 1);
    });
  });
});
