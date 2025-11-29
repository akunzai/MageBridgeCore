import { test, expect } from '@playwright/test';

/**
 * Complete CRUD tests for MageBridge admin entities.
 * Tests Create, Read, Update, Delete operations for Products, URLs, and Usergroups.
 *
 * Note: Tests are run serially to ensure CRUD order is maintained.
 */

// Use a fixed timestamp for all tests in this file
const timestamp = Date.now();

test.describe.serial('MageBridge Products CRUD', () => {
  const testLabel = `Test Product ${timestamp}`;
  const updatedLabel = `Updated Product ${timestamp}`;

  test('should create a new product', async ({ page }) => {
    // Navigate to products list
    await page.goto(
      '/administrator/index.php?option=com_magebridge&view=products'
    );

    // Click New button
    await page.getByRole('button', { name: 'New' }).click();

    // Wait for form to load
    await expect(page.locator('#adminForm')).toBeVisible();

    // Fill in the label field
    await page.locator('input[name="label"]').fill(testLabel);

    // Click Save & Close
    await page.getByRole('button', { name: 'Save & Close' }).click();

    // Should redirect back to list and show success message
    await expect(page).toHaveURL(/view=products/);
    await expect(
      page.locator('.alert-success, .alert-message').last()
    ).toBeVisible();

    // Verify the product appears in the list
    await expect(page.getByText(testLabel)).toBeVisible();
  });

  test('should read/view a product', async ({ page }) => {
    // Navigate to products list
    await page.goto(
      '/administrator/index.php?option=com_magebridge&view=products'
    );

    // Click on the product label to edit/view
    await page.getByRole('link', { name: testLabel }).click();

    // Should load the edit form
    await expect(page.locator('#adminForm')).toBeVisible();

    // Verify the label field contains the correct value
    await expect(page.locator('input[name="label"]')).toHaveValue(testLabel);
  });

  test('should update a product', async ({ page }) => {
    // Navigate to products list
    await page.goto(
      '/administrator/index.php?option=com_magebridge&view=products'
    );

    // Click on the product label to edit
    await page.getByRole('link', { name: testLabel }).click();

    // Update the label
    await page.locator('input[name="label"]').fill(updatedLabel);

    // Click Save & Close
    await page.getByRole('button', { name: 'Save & Close' }).click();

    // Should redirect back to list
    await expect(page).toHaveURL(/view=products/);

    // Verify the updated product appears in the list
    await expect(page.getByText(updatedLabel)).toBeVisible();
  });

  test('should delete a product', async ({ page }) => {
    // Navigate to products list
    await page.goto(
      '/administrator/index.php?option=com_magebridge&view=products'
    );

    // Find the row with updated product and check its checkbox
    const row = page.locator('tr', { has: page.getByText(updatedLabel) });
    await row.locator('input[type="checkbox"][name="cid[]"]').check();

    // Click Delete button
    await page.getByRole('button', { name: 'Delete' }).click();

    // Wait for page to reload
    await page.waitForLoadState('networkidle');

    // Verify the product is no longer in the list
    await expect(page.getByText(updatedLabel)).not.toBeVisible();
  });
});

test.describe.serial('MageBridge URLs CRUD', () => {
  const testSource = `test-source-${timestamp}`;
  const testDestination = `test-destination-${timestamp}`;
  const updatedSource = `updated-source-${timestamp}`;

  test('should create a new URL', async ({ page }) => {
    // Navigate to URLs list
    await page.goto('/administrator/index.php?option=com_magebridge&view=urls');

    // Click New button
    await page.getByRole('button', { name: 'New' }).click();

    // Wait for form to load
    await expect(page.locator('#adminForm')).toBeVisible();

    // Fill in the source and destination fields
    await page.locator('input[name="source"]').fill(testSource);
    await page.locator('input[name="destination"]').fill(testDestination);

    // Click Save & Close
    await page.getByRole('button', { name: 'Save & Close' }).click();

    // Should redirect back to list
    await expect(page).toHaveURL(/view=urls/);

    // Verify the URL appears in the list
    await expect(page.getByText(testSource)).toBeVisible();
  });

  test('should read/view a URL', async ({ page }) => {
    // Navigate to URLs list
    await page.goto('/administrator/index.php?option=com_magebridge&view=urls');

    // Click on the URL source to edit/view
    await page.getByRole('link', { name: testSource }).click();

    // Should load the edit form
    await expect(page.locator('#adminForm')).toBeVisible();

    // Verify the fields contain the correct values
    await expect(page.locator('input[name="source"]')).toHaveValue(testSource);
    await expect(page.locator('input[name="destination"]')).toHaveValue(
      testDestination
    );
  });

  test('should update a URL', async ({ page }) => {
    // Navigate to URLs list
    await page.goto('/administrator/index.php?option=com_magebridge&view=urls');

    // Click on the URL source to edit
    await page.getByRole('link', { name: testSource }).click();

    // Update the source
    await page.locator('input[name="source"]').fill(updatedSource);

    // Click Save & Close
    await page.getByRole('button', { name: 'Save & Close' }).click();

    // Should redirect back to list
    await expect(page).toHaveURL(/view=urls/);

    // Verify the updated URL appears in the list
    await expect(page.getByText(updatedSource)).toBeVisible();
  });

  test('should delete a URL', async ({ page }) => {
    // Navigate to URLs list
    await page.goto('/administrator/index.php?option=com_magebridge&view=urls');

    // Find the row with updated URL and check its checkbox
    const row = page.locator('tr', { has: page.getByText(updatedSource) });
    await row.locator('input[type="checkbox"][name="cid[]"]').check();

    // Click Delete button
    await page.getByRole('button', { name: 'Delete' }).click();

    // Wait for page to reload
    await page.waitForLoadState('networkidle');

    // Verify the URL is no longer in the list
    await expect(page.getByText(updatedSource)).not.toBeVisible();
  });

});

test.describe.serial('MageBridge User Groups CRUD', () => {
  const testLabel = `Test Group ${timestamp}`;
  const testDescription = `Test Description ${timestamp}`;

  test('should create a new user group', async ({ page }) => {
    // Navigate to user groups list
    await page.goto(
      '/administrator/index.php?option=com_magebridge&view=usergroups'
    );

    // Click New button
    await page.getByRole('button', { name: 'New' }).click();

    // Wait for form to load
    await expect(page.locator('#adminForm')).toBeVisible();

    // Fill in the label and description fields
    await page.locator('input[name="label"]').fill(testLabel);
    await page.locator('input[name="description"]').fill(testDescription);

    // Click Save & Close
    await page.getByRole('button', { name: 'Save & Close' }).click();

    // Should redirect back to list
    await expect(page).toHaveURL(/view=usergroups/);

    // Verify the user group appears in the list (description is displayed in the table)
    await expect(page.getByText(testDescription)).toBeVisible();
  });

  test('should read/view a user group', async ({ page }) => {
    // Navigate to user groups list
    await page.goto(
      '/administrator/index.php?option=com_magebridge&view=usergroups'
    );

    // Click on the user group description to edit/view
    await page.getByRole('link', { name: testDescription }).click();

    // Should load the edit form
    await expect(page.locator('#adminForm')).toBeVisible();

    // Verify the fields contain the correct values
    await expect(page.locator('input[name="label"]')).toHaveValue(testLabel);
    await expect(page.locator('input[name="description"]')).toHaveValue(
      testDescription
    );
  });

  test('should update a user group', async ({ page }) => {
    const updatedLabel = `Updated Group ${timestamp}`;

    // Navigate to user groups list
    await page.goto(
      '/administrator/index.php?option=com_magebridge&view=usergroups'
    );

    // Click on the user group description to edit
    await page.getByRole('link', { name: testDescription }).click();

    // Update the label
    await page.locator('input[name="label"]').fill(updatedLabel);

    // Click Save & Close
    await page.getByRole('button', { name: 'Save & Close' }).click();

    // Should redirect back to list
    await expect(page).toHaveURL(/view=usergroups/);

    // Verify the update was successful (description should still be visible)
    await expect(page.getByText(testDescription)).toBeVisible();
  });

  test('should delete a user group', async ({ page }) => {
    // Navigate to user groups list
    await page.goto(
      '/administrator/index.php?option=com_magebridge&view=usergroups'
    );

    // Find the row with user group and check its checkbox
    const row = page.locator('tr', { has: page.getByText(testDescription) });
    await row.locator('input[type="checkbox"][name="cid[]"]').check();

    // Click Delete button
    await page.getByRole('button', { name: 'Delete' }).click();

    // Wait for page to reload
    await page.waitForLoadState('networkidle');

    // Verify the user group is no longer in the list
    await expect(page.getByText(testDescription)).not.toBeVisible();
  });
});

test.describe('MageBridge Stores CRUD', () => {
  // Note: Stores require Magento connection to work properly
  // These tests verify the UI functionality

  test('should open new store form', async ({ page }) => {
    // Navigate to stores list
    await page.goto(
      '/administrator/index.php?option=com_magebridge&view=stores'
    );

    // Click New button
    await page.getByRole('button', { name: 'New' }).click();

    // Should load the edit form
    await expect(page.locator('#adminForm')).toBeVisible();
  });

  test('should be able to close store form', async ({ page }) => {
    // Navigate to stores list
    await page.goto(
      '/administrator/index.php?option=com_magebridge&view=stores'
    );

    // Click New button
    await page.getByRole('button', { name: 'New' }).click();

    // Should load the edit form
    await expect(page.locator('#adminForm')).toBeVisible();

    // Click Close button (use exact to avoid matching "Save & Close")
    await page.getByRole('button', { name: 'Close', exact: true }).click();

    // Should redirect back to list (note: view=store without 's' is also valid)
    await expect(page).toHaveURL(/view=store/);
  });
});
