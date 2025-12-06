import { test, expect } from '@playwright/test';
import { JoomlaAdminUrls } from '../../helpers';

/**
 * Tests for MageBridge User Groups management.
 */
test.describe('MageBridge Admin - User Groups', () => {
  test.describe('List Page', () => {
    test('should display user groups list', async ({ page }) => {
      await page.goto(JoomlaAdminUrls.magebridge.usergroups);

      await expect(page.getByRole('navigation', { name: 'Toolbar' })).toBeVisible();
      await expect(page.getByRole('link', { name: 'Description', exact: true })).toBeVisible();
    });

    test('should have New button', async ({ page }) => {
      await page.goto(JoomlaAdminUrls.magebridge.usergroups);
      await expect(page.getByRole('button', { name: 'New' })).toBeVisible();
    });

    test('should have list_limit select', async ({ page }) => {
      await page.goto(JoomlaAdminUrls.magebridge.usergroups);

      const limitSelect = page.locator('select[name="list_limit"]');
      await expect(limitSelect).toBeVisible();
      await expect(limitSelect.locator('option')).toHaveCount(14);
    });
  });

  test.describe.serial('CRUD Operations', () => {
    const timestamp = Date.now();
    const testLabel = `E2E Group ${timestamp}`;
    const testDescription = `E2E Description ${timestamp}`;
    const updatedLabel = `E2E Updated ${timestamp}`;

    test('should create a new user group', async ({ page }) => {
      await page.goto(JoomlaAdminUrls.magebridge.usergroups);
      await page.getByRole('button', { name: 'New' }).click();

      await expect(page.locator('#adminForm')).toBeVisible();
      await page.locator('input[name="label"]').fill(testLabel);
      await page.locator('input[name="description"]').fill(testDescription);
      // Required: select Primary Joomla! group (value must be non-zero)
      await page.locator('select[name="joomla_group"]').selectOption({ label: 'Registered' });
      // Required: select Magento group (value must be non-zero)
      await page.locator('select[name="magento_group"]').selectOption({ label: 'General' });
      await page.getByRole('button', { name: 'Save & Close' }).click();

      await page.waitForURL(/view=usergroups/, { timeout: 10000 });

      // Search using timestamp (no spaces) to handle pagination
      await page.locator('input[name="filter_search"]').fill(String(timestamp));
      await page.getByRole('button', { name: 'Search' }).click();
      await page.waitForLoadState('networkidle');

      await expect(page.getByText(testDescription)).toBeVisible({ timeout: 10000 });
    });

    test('should read/view a user group', async ({ page }) => {
      await page.goto(JoomlaAdminUrls.magebridge.usergroups);

      // Search using timestamp (no spaces) to handle pagination
      await page.locator('input[name="filter_search"]').fill(String(timestamp));
      await page.getByRole('button', { name: 'Search' }).click();
      await page.waitForLoadState('networkidle');

      await page.getByRole('link', { name: testDescription }).click();

      await expect(page.locator('#adminForm')).toBeVisible();
      await expect(page.locator('input[name="label"]')).toHaveValue(testLabel);
    });

    test('should update a user group', async ({ page }) => {
      await page.goto(JoomlaAdminUrls.magebridge.usergroups);

      // Search using timestamp (no spaces) to handle pagination
      await page.locator('input[name="filter_search"]').fill(String(timestamp));
      await page.getByRole('button', { name: 'Search' }).click();
      await page.waitForLoadState('networkidle');

      await page.getByRole('link', { name: testDescription }).click();

      await page.locator('input[name="label"]').fill(updatedLabel);
      await page.getByRole('button', { name: 'Save & Close' }).click();

      await expect(page).toHaveURL(/view=usergroups/);

      // Search using timestamp (no spaces) to verify it's still there
      await page.locator('input[name="filter_search"]').fill(String(timestamp));
      await page.getByRole('button', { name: 'Search' }).click();
      await page.waitForLoadState('networkidle');

      await expect(page.getByText(testDescription)).toBeVisible();
    });

    test('should delete a user group', async ({ page }) => {
      await page.goto(JoomlaAdminUrls.magebridge.usergroups);

      // Search using timestamp (no spaces) to handle pagination
      await page.locator('input[name="filter_search"]').fill(String(timestamp));
      await page.getByRole('button', { name: 'Search' }).click();
      await page.waitForLoadState('networkidle');

      const row = page.locator('tr', { has: page.getByText(testDescription) });
      await row.locator('input[type="checkbox"][name="cid[]"]').check();

      await page.getByRole('button', { name: 'Delete' }).click();
      await page.waitForLoadState('networkidle');

      await expect(page.getByText(testDescription)).not.toBeVisible();
    });
  });
});
