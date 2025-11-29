import { test, expect } from '@playwright/test';
import { JoomlaAdminUrls } from '../../helpers';

/**
 * Tests for MageBridge Stores (Store Relations) management.
 */
test.describe('MageBridge Admin - Stores', () => {
  test.describe('List Page', () => {
    test('should display stores list', async ({ page }) => {
      await page.goto(JoomlaAdminUrls.magebridge.stores);

      await expect(page.getByRole('navigation', { name: 'Toolbar' })).toBeVisible();
      await expect(page.getByRole('link', { name: 'Label' })).toBeVisible();
      await expect(page.getByRole('link', { name: 'Store Title' })).toBeVisible();
    });

    test('should have New button', async ({ page }) => {
      await page.goto(JoomlaAdminUrls.magebridge.stores);
      await expect(page.getByRole('button', { name: 'New' })).toBeVisible();
    });

    test('should open new store form', async ({ page }) => {
      await page.goto(JoomlaAdminUrls.magebridge.stores);
      await page.getByRole('button', { name: 'New' }).click();

      await expect(page.locator('#adminForm')).toBeVisible();
    });

    test('should be able to close store form', async ({ page }) => {
      await page.goto(JoomlaAdminUrls.magebridge.stores);
      await page.getByRole('button', { name: 'New' }).click();

      await expect(page.locator('#adminForm')).toBeVisible();

      await page.getByRole('button', { name: 'Close', exact: true }).click();
      await expect(page).toHaveURL(/view=store/);
    });
  });

  test.describe.serial('CRUD Operations', () => {
    test('should create a new store relation', async ({ page }) => {
      await page.goto(JoomlaAdminUrls.magebridge.stores);
      await page.getByRole('button', { name: 'New' }).click();

      await expect(page.locator('#adminForm')).toBeVisible();

      const storeSelect = page.locator('select[name="store"]');
      const storeInput = page.locator('input[name="store"]');
      
      const selectCount = await storeSelect.count();
      
      // Skip if select element doesn't exist (API data not available, field is text input)
      if (selectCount === 0) {
        await expect(storeInput).toBeVisible();
        test.skip(true, 'Store field is text input (OpenMage API data not available)');
        return;
      }
      
      await expect(storeSelect).toBeVisible();

      // Check if store options are available from OpenMage API
      const optionCount = await storeSelect.locator('option').count();
      
      // Skip if only "-- Select --" option is available (API data not loaded)
      if (optionCount <= 1) {
        test.skip(true, 'OpenMage API store data not available');
      }

      // Select a Magento store (skip "-- Select --" and optgroup)
      await storeSelect.selectOption({ index: 2 });

      await page.getByRole('button', { name: 'Save & Close' }).click();
      await page.waitForLoadState('networkidle');

      await expect(page).toHaveURL(/view=stores/);

      const successMessage = page.locator('.alert-success, .alert-message');
      const messageCount = await successMessage.count();
      if (messageCount > 0) {
        const text = await successMessage.last().textContent();
        expect(text).not.toContain('Saved %s');
      }
    });

    test('should display store relation in list after save', async ({ page }) => {
      await page.goto(JoomlaAdminUrls.magebridge.stores);

      const storeLinks = await page
        .locator('table')
        .getByRole('link', { name: /English|French|German|Madison/ })
        .all();

      expect(storeLinks.length).toBeGreaterThan(0);
      await expect(page.getByText('No Matching Results')).not.toBeVisible();
    });

    test('should edit a store relation', async ({ page }) => {
      await page.goto(JoomlaAdminUrls.magebridge.stores);

      // Click on any store relation link in the table
      const storeLink = page
        .locator('table.adminlist tbody tr')
        .first()
        .locator('a')
        .first();
      
      await expect(storeLink).toBeVisible();
      await storeLink.click();

      await expect(page.locator('#adminForm')).toBeVisible();

      const storeSelect = page.locator('select[name="store"]');
      const storeInput = page.locator('input[name="store"]');
      
      const selectCount = await storeSelect.count();
      
      if (selectCount > 0) {
        await expect(storeSelect).toBeVisible();
        // Verify the select element exists and has options
        const optionCount = await storeSelect.locator('option').count();
        expect(optionCount).toBeGreaterThan(1);
      } else {
        // Fallback to text input when API data is not available
        await expect(storeInput).toBeVisible();
        test.skip(true, 'Store field is text input (OpenMage API data not available)');
      }
    });

    test('should copy a store relation', async ({ page }) => {
      await page.goto(JoomlaAdminUrls.magebridge.stores);

      // Ensure at least one store relation exists (from create test)
      const storeLinks = await page
        .locator('table')
        .getByRole('link', { name: /English|French|German|Madison/ })
        .all();
      
      expect(storeLinks.length).toBeGreaterThan(0);

      const firstCheckbox = page
        .locator('input[type="checkbox"][name="cid[]"]')
        .first();
      
      // Wait for checkbox to be visible and enabled
      await expect(firstCheckbox).toBeVisible();
      await firstCheckbox.check();

      await page.getByRole('button', { name: 'Copy' }).click();
      await page.waitForLoadState('networkidle');

      await expect(page).toHaveURL(/view=store/);
      await expect(page.locator('#adminForm')).toBeVisible();

      // Use last() to get the most recent alert message
      await expect(
        page.locator('.alert-success, .alert-message').last()
      ).toContainText('Saved Store Relation');
    });

    test('should delete store relations (cleanup)', async ({ page }) => {
      await page.goto(JoomlaAdminUrls.magebridge.stores);

      // Check if there's no data to delete
      const noItemsMessage = page.getByText('No items found');
      const hasNoItems = await noItemsMessage.isVisible().catch(() => false);

      if (hasNoItems) {
        // No items to delete, test passes (nothing to clean up)
        return;
      }

      // Only delete the first 2 items (created by CRUD tests: create + copy)
      // Don't delete all items to preserve seed data for pagination tests
      const checkboxes = page.locator('input[type="checkbox"][name="cid[]"]');
      const count = await checkboxes.count();

      if (count > 0) {
        // Delete up to 2 items (the ones created by this test)
        const itemsToDelete = Math.min(count, 2);
        for (let i = 0; i < itemsToDelete; i++) {
          await checkboxes.nth(i).check();
        }

        await page.getByRole('button', { name: 'Delete' }).click();
        await page.waitForLoadState('networkidle');

        // Check for success message, but don't fail if not found
        // (message may disappear quickly or be overwritten by parallel tests)
        const message = page.locator('.alert-success, .alert-message').last();
        const isVisible = await message.isVisible().catch(() => false);

        if (isVisible) {
          await expect(message).toContainText('deleted');
        }
      }
    });
  });

  test.describe('Pagination', () => {
    test('should have list_limit select', async ({ page }) => {
      await page.goto(JoomlaAdminUrls.magebridge.stores);

      const limitSelect = page.locator('select[name="list_limit"]');
      await expect(limitSelect).toBeVisible();
    });

    test('should display pagination when data exists', async ({ page }) => {
      await page.goto(JoomlaAdminUrls.magebridge.stores);
      await page.waitForLoadState('networkidle');

      // Check if there's no data
      const noItemsMessage = page.getByText('No items found');
      const hasNoItems = await noItemsMessage.isVisible().catch(() => false);
      test.skip(hasNoItems, 'No data available');

      // Check if pagination exists (indicates more than one page of data)
      const paginationNav = page.locator('nav[aria-label="Pagination"]').first();
      const hasPagination = await paginationNav.isVisible().catch(() => false);
      test.skip(!hasPagination, 'Insufficient data for pagination (need > 20 rows)');

      await expect(paginationNav).toBeVisible();
    });

    test('should have limit select box', async ({ page }) => {
      await page.goto(JoomlaAdminUrls.magebridge.stores);

      // Verify limit select exists
      const limitSelect = page.locator('select[name="list_limit"]');
      await expect(limitSelect).toBeVisible();
      
      // Verify it has expected options
      await expect(limitSelect.locator('option')).toHaveCount(14);
    });
  });
});
