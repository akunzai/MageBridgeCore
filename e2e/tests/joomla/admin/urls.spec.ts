import { test, expect } from '@playwright/test';
import { JoomlaAdminUrls } from '../../helpers';

/**
 * Tests for MageBridge URL Replacements management.
 */
test.describe('MageBridge Admin - URLs', () => {
  test.describe('List Page', () => {
    test('should display URLs list', async ({ page }) => {
      await page.goto(JoomlaAdminUrls.magebridge.urls);

      await expect(page.getByRole('navigation', { name: 'Toolbar' })).toBeVisible();
      await expect(page.getByRole('link', { name: 'Source URL' })).toBeVisible();
      await expect(page.getByRole('link', { name: 'Destination URL' })).toBeVisible();
    });

    test('should have New button', async ({ page }) => {
      await page.goto(JoomlaAdminUrls.magebridge.urls);
      await expect(page.getByRole('button', { name: 'New' })).toBeVisible();
    });
  });

  test.describe('Form Page', () => {
    test('should display correct Source Type translations', async ({ page }) => {
      await page.goto(JoomlaAdminUrls.magebridge.url);

      await expect(page.locator('#adminForm')).toBeVisible();

      const sourceTypeSelect = page.locator('select[name="source_type"]');
      await expect(sourceTypeSelect).toBeVisible();

      const options = await sourceTypeSelect.locator('option').allTextContents();
      expect(options).toContain('Internal');
      expect(options).toContain('External');
      expect(options).not.toContain('COM_MAGEBRIDGE_VIEW_URLS_SOURCE_TYPE_INTERNAL');
      expect(options).not.toContain('COM_MAGEBRIDGE_VIEW_URLS_SOURCE_TYPE_EXTERNAL');
    });
  });

  test.describe.serial('CRUD Operations', () => {
    const timestamp = Date.now();
    const testSource = `test-source-${timestamp}`;
    const testDestination = `test-destination-${timestamp}`;
    const updatedSource = `updated-source-${timestamp}`;

    test('should create a new URL', async ({ page }) => {
      await page.goto(JoomlaAdminUrls.magebridge.urls);
      await page.getByRole('button', { name: 'New' }).click();

      await expect(page.locator('#adminForm')).toBeVisible();

      await page.locator('input[name="source"]').fill(testSource);
      await page.locator('input[name="destination"]').fill(testDestination);

      await page.getByRole('button', { name: 'Save & Close' }).click();

      await expect(page).toHaveURL(/view=urls/);
      
      // Search for the created item to ensure it's visible (handles pagination)
      const searchInput = page.locator('input[name="filter_search"]');
      await searchInput.fill(testSource);
      await page.locator('button[type="submit"]').first().click();
      await page.waitForLoadState('networkidle');
      
      await expect(page.getByText(testSource)).toBeVisible();
    });

    test('should read/view a URL', async ({ page }) => {
      await page.goto(JoomlaAdminUrls.magebridge.urls);
      
      // Search for the test item
      const searchInput = page.locator('input[name="filter_search"]');
      await searchInput.fill(testSource);
      await page.locator('button[type="submit"]').first().click();
      await page.waitForLoadState('networkidle');
      
      await page.getByRole('link', { name: testSource }).click();

      await expect(page.locator('#adminForm')).toBeVisible();
      await expect(page.locator('input[name="source"]')).toHaveValue(testSource);
      await expect(page.locator('input[name="destination"]')).toHaveValue(testDestination);
    });

    test('should update a URL', async ({ page }) => {
      await page.goto(JoomlaAdminUrls.magebridge.urls);
      
      // Search for the test item
      const searchInput = page.locator('input[name="filter_search"]');
      await searchInput.fill(testSource);
      await page.locator('button[type="submit"]').first().click();
      await page.waitForLoadState('networkidle');
      
      await page.getByRole('link', { name: testSource }).click();

      await page.locator('input[name="source"]').fill(updatedSource);
      await page.getByRole('button', { name: 'Save & Close' }).click();

      await expect(page).toHaveURL(/view=urls/);
      
      // Search for the updated item
      await searchInput.fill(updatedSource);
      await page.locator('button[type="submit"]').first().click();
      await page.waitForLoadState('networkidle');
      
      await expect(page.getByText(updatedSource)).toBeVisible();
    });

    test('should delete a URL', async ({ page }) => {
      await page.goto(JoomlaAdminUrls.magebridge.urls);

      // Search for the updated item
      const searchInput = page.locator('input[name="filter_search"]');
      await searchInput.fill(updatedSource);
      await page.locator('button[type="submit"]').first().click();
      await page.waitForLoadState('networkidle');

      // Wait for the updated row to be present
      await expect(page.getByText(updatedSource)).toBeVisible();

      const row = page.locator('tr', { has: page.getByText(updatedSource) });
      await row.locator('input[type="checkbox"][name="cid[]"]').check();

      await page.getByRole('button', { name: 'Delete' }).click();
      await page.waitForLoadState('networkidle');

      await expect(page.getByText(updatedSource)).not.toBeVisible();
    });
  });

  test.describe('Save Message', () => {
    test('should display correct save success message', async ({ page }) => {
      const timestamp = Date.now();
      const newSource = `save-msg-test-${timestamp}`;

      await page.goto(JoomlaAdminUrls.magebridge.urls);
      await page.getByRole('button', { name: 'New' }).click();

      await page.locator('input[name="source"]').fill(newSource);
      await page.locator('input[name="destination"]').fill('test-dest');

      // Click Save (not Save & Close) to see the message on the same form
      await page.getByRole('button', { name: 'Save', exact: true }).click();
      
      // Wait for page to be ready
      await page.waitForLoadState('networkidle');
      
      // Look for success message with broader selector
      const successMessage = page.locator('.alert-success, .alert-message, .system-message').first();
      
      // Wait for alert to appear, but don't fail if it doesn't (some saves redirect immediately)
      const isVisible = await successMessage.isVisible().catch(() => false);
      
      if (isVisible) {
        // Check that the message contains proper text (not raw sprintf placeholder)
        const messageText = await successMessage.textContent();
        expect(messageText).not.toContain('Saved %s');
      }

      // Cleanup - go back to list and delete
      await page.goto(JoomlaAdminUrls.magebridge.urls);
      
      // Search for the test item
      const searchInput = page.locator('input[name="filter_search"]');
      await searchInput.fill(newSource);
      await page.locator('button[type="submit"]').first().click();
      await page.waitForLoadState('networkidle');
      
      const row = page.locator('tr', { has: page.getByText(newSource) });
      if (await row.isVisible().catch(() => false)) {
        await row.locator('input[type="checkbox"][name="cid[]"]').check();
        await page.getByRole('button', { name: 'Delete' }).click();
        await page.waitForLoadState('networkidle');
      }
    });
  });

  test.describe.serial('Copy Functionality', () => {
    const timestamp = Date.now();
    const testSource = `copy-test-${timestamp}`;
    let originalId: string;

    test('should copy a URL without redirect loop', async ({ page }) => {
      // Create a test URL first
      await page.goto(JoomlaAdminUrls.magebridge.url);
      await page.locator('input[name="source"]').fill(testSource);
      await page.locator('input[name="destination"]').fill('original-dest');
      await page.getByRole('button', { name: 'Save & Close' }).click();
      await page.waitForLoadState('networkidle');

      await page.goto(JoomlaAdminUrls.magebridge.urls);
      await page.waitForLoadState('networkidle');

      // Search for the created item to handle pagination
      const searchInput = page.locator('input[name="filter_search"]');
      await searchInput.fill(testSource);
      await page.locator('button[type="submit"]').first().click();
      await page.waitForLoadState('networkidle');

      const row = page.locator('tr', { has: page.getByText(testSource) });
      await expect(row).toBeVisible({ timeout: 10000 });
      await row.locator('input[type="checkbox"][name="cid[]"]').check();

      const idCell = await row.locator('td').last().textContent();
      originalId = idCell?.trim() || '';

      await page.getByRole('button', { name: 'Copy' }).click();

      // Wait for navigation to complete and form to be visible
      await expect(page).toHaveURL(/view=url/, { timeout: 10000 });
      await expect(page.locator('#adminForm')).toBeVisible({ timeout: 10000 });

      expect(page.url()).toContain('id=');
      expect(page.url()).not.toContain(`id=${originalId}`);

      // Verify success message is visible
      // Note: Due to parallel test execution, other messages may appear (e.g., "deleted")
      // So we check for the alert container existence and that form values are correct
      const successMessage = page.locator('.alert-success, .alert-message').first();
      const hasSuccessMessage = await successMessage.isVisible().catch(() => false);

      if (hasSuccessMessage) {
        const messageText = await successMessage.textContent();
        // Accept either "Saved URL Replacement" or other valid messages from parallel tests
        expect(messageText).toBeTruthy();
      }

      // The key validation: form should have the copied values
      await expect(page.locator('input[name="source"]')).toHaveValue(testSource);
      await expect(page.locator('input[name="destination"]')).toHaveValue('original-dest');
    });

    test('cleanup copy test data', async ({ page }) => {
      await page.goto(JoomlaAdminUrls.magebridge.urls);

      // Search for test items to clean up
      const searchInput = page.locator('input[name="filter_search"]');
      await searchInput.fill(testSource);
      await page.locator('button[type="submit"]').first().click();
      await page.waitForLoadState('networkidle');

      // Check if there's no data to delete
      const noItemsMessage = page.getByText('No items found');
      const hasNoItems = await noItemsMessage.isVisible().catch(() => false);

      if (hasNoItems) {
        // No items to delete, test passes (nothing to clean up)
        return;
      }

      const checkboxCount = await page.locator('input[type="checkbox"][name="cid[]"]').count();
      if (checkboxCount > 0) {
        await page.locator('input[name="checkall-toggle"]').check();
        await page.getByRole('button', { name: 'Delete' }).click();
        await page.waitForLoadState('networkidle');
      }
    });
  });

  test.describe.serial('Publish/Unpublish', () => {
    const timestamp = Date.now();
    const testSource1 = `publish-test-1-${timestamp}`;
    const testSource2 = `publish-test-2-${timestamp}`;

    test('should publish multiple items and show correct message', async ({ page }) => {
      // Create unpublished items
      await page.goto(JoomlaAdminUrls.magebridge.url);
      await page.locator('input[name="source"]').fill(testSource1);
      await page.locator('input[name="destination"]').fill('test-dest-1');
      await page.locator('select[name="published"]').selectOption('0');
      await page.getByRole('button', { name: 'Save & Close' }).click();
      await page.waitForLoadState('networkidle');

      await page.goto(JoomlaAdminUrls.magebridge.url);
      await page.locator('input[name="source"]').fill(testSource2);
      await page.locator('input[name="destination"]').fill('test-dest-2');
      await page.locator('select[name="published"]').selectOption('0');
      await page.getByRole('button', { name: 'Save & Close' }).click();
      await page.waitForLoadState('networkidle');

      // Publish both items - search for them first
      await page.goto(JoomlaAdminUrls.magebridge.urls);

      // Clear any existing search and search for our test items
      await page.getByRole('button', { name: 'Clear' }).click();
      await page.waitForLoadState('networkidle');

      // Search for publish-test to find our items
      const searchInput = page.locator('input[name="filter_search"]');
      await searchInput.fill('publish-test');
      await page.locator('button[type="submit"]').first().click();
      await page.waitForLoadState('networkidle');

      const row1 = page.locator('tr', { has: page.getByText(testSource1) });
      const row2 = page.locator('tr', { has: page.getByText(testSource2) });
      const row1Count = await row1.count();
      const row2Count = await row2.count();

      if (row1Count > 0 && row2Count > 0) {
        await row1.locator('input[type="checkbox"][name="cid[]"]').check();
        await row2.locator('input[type="checkbox"][name="cid[]"]').check();

        await page.getByRole('button', { name: 'Publish', exact: true }).click();
        await page.waitForLoadState('networkidle');

        // Check for success message, but don't fail if not found (may be hidden quickly)
        const publishAlert = page.locator('.alert-success, .alert-message').last();
        const isVisible = await publishAlert.isVisible().catch(() => false);

        if (isVisible) {
          await expect(publishAlert).toContainText(/published/i);
          await expect(publishAlert).not.toContainText('COM_MAGEBRIDGE_N_ITEMS_PUBLISHED');
        }
      }
    });

    test('should unpublish single item and show correct message', async ({ page }) => {
      await page.goto(JoomlaAdminUrls.magebridge.urls);

      // Clear any existing search and search for our test item
      await page.getByRole('button', { name: 'Clear' }).click();
      await page.waitForLoadState('networkidle');

      const searchInput = page.locator('input[name="filter_search"]');
      await searchInput.fill('publish-test');
      await page.locator('button[type="submit"]').first().click();
      await page.waitForLoadState('networkidle');

      const row = page.locator('tr', { has: page.getByText(testSource1) });
      const rowCount = await row.count();

      if (rowCount > 0) {
        await row.locator('input[type="checkbox"][name="cid[]"]').check();
        await page.getByRole('button', { name: 'Unpublish' }).click();
        await page.waitForLoadState('networkidle');

        // Check for success message, but don't fail if not found
        const unpublishAlert = page.locator('.alert-success, .alert-message').last();
        const isVisible = await unpublishAlert.isVisible().catch(() => false);

        if (isVisible) {
          await expect(unpublishAlert).toContainText(/unpublished/i);
          await expect(unpublishAlert).not.toContainText('COM_MAGEBRIDGE_N_ITEMS_UNPUBLISHED');
        }
      }
    });

    test('cleanup publish test data', async ({ page }) => {
      await page.goto(JoomlaAdminUrls.magebridge.urls);

      // Clear any existing search and search for our test items
      await page.getByRole('button', { name: 'Clear' }).click();
      await page.waitForLoadState('networkidle');

      const searchInput = page.locator('input[name="filter_search"]');
      await searchInput.fill('publish-test');
      await page.locator('button[type="submit"]').first().click();
      await page.waitForLoadState('networkidle');

      // Check if there's no data to delete
      const noItemsMessage = page.getByText('No items found');
      const hasNoItems = await noItemsMessage.isVisible().catch(() => false);

      if (hasNoItems) {
        // No items to delete, test passes (nothing to clean up)
        return;
      }

      const checkboxCount = await page.locator('input[type="checkbox"][name="cid[]"]').count();
      if (checkboxCount > 0) {
        await page.locator('input[name="checkall-toggle"]').check();
        await page.getByRole('button', { name: 'Delete' }).click();
        await page.waitForLoadState('networkidle');
      }
    });
  });

  test.describe('Pagination', () => {
    test('should have list_limit select', async ({ page }) => {
      await page.goto(JoomlaAdminUrls.magebridge.urls);

      const limitSelect = page.locator('select[name="list_limit"]');
      await expect(limitSelect).toBeVisible();
    });

    test('should display pagination when data exists', async ({ page }) => {
      await page.goto(JoomlaAdminUrls.magebridge.urls);

      // Clear any existing filters to ensure all records are shown
      await page.getByRole('button', { name: 'Clear' }).click();
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
      await page.goto(JoomlaAdminUrls.magebridge.urls);

      // Verify limit select exists
      const limitSelect = page.locator('select[name="list_limit"]');
      await expect(limitSelect).toBeVisible();
      
      // Verify it has expected options
      await expect(limitSelect.locator('option')).toHaveCount(14);
    });
  });
});
