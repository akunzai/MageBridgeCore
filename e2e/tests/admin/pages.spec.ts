import { test, expect } from '@playwright/test';

/**
 * Tests for all MageBridge admin pages to verify they load correctly.
 */
test.describe('MageBridge Admin Pages', () => {
  const pages = [
    { name: 'Home', view: 'home' },
    { name: 'Products', view: 'products' },
    { name: 'Stores', view: 'stores' },
    { name: 'URLs', view: 'urls' },
    { name: 'User Groups', view: 'usergroups' },
    { name: 'Users', view: 'users' },
    { name: 'Logs', view: 'logs' },
    { name: 'Check', view: 'check' },
  ];

  for (const { name, view } of pages) {
    test(`should load ${name} page`, async ({ page }) => {
      await page.goto(
        `/administrator/index.php?option=com_magebridge&view=${view}`
      );

      // Should not show error
      await expect(page.locator('.alert-danger, .alert-error')).not.toBeVisible(
        { timeout: 5000 }
      );

      // Page should have main content area
      await expect(page.getByRole('main')).toBeVisible();
    });
  }
});

test.describe('MageBridge Products CRUD', () => {
  test('should display products list', async ({ page }) => {
    await page.goto(
      '/administrator/index.php?option=com_magebridge&view=products'
    );

    // Should see toolbar with buttons
    await expect(page.getByRole('navigation', { name: 'Toolbar' })).toBeVisible();

    // Should see table headers (use exact: true to avoid matching product links)
    await expect(page.getByRole('link', { name: 'Label', exact: true })).toBeVisible();
    await expect(page.getByRole('link', { name: 'Product SKU' })).toBeVisible();
  });

  test('should have New button', async ({ page }) => {
    await page.goto(
      '/administrator/index.php?option=com_magebridge&view=products'
    );

    // Should have New button in toolbar
    await expect(page.getByRole('button', { name: 'New' })).toBeVisible();
  });

  test('should open new product form', async ({ page }) => {
    await page.goto(
      '/administrator/index.php?option=com_magebridge&view=products'
    );

    // Click New button
    await page.getByRole('button', { name: 'New' }).click();

    // Should navigate to product edit form
    await expect(page).toHaveURL(/view=product|task=.*\.add/);

    // Should see the admin form (use specific ID to avoid matching debug toolbar form)
    await expect(page.locator('#adminForm')).toBeVisible();
  });
});

test.describe('MageBridge Stores CRUD', () => {
  test('should display stores list', async ({ page }) => {
    await page.goto(
      '/administrator/index.php?option=com_magebridge&view=stores'
    );

    // Should see toolbar
    await expect(page.getByRole('navigation', { name: 'Toolbar' })).toBeVisible();

    // Should see table headers
    await expect(page.getByRole('link', { name: 'Label' })).toBeVisible();
    await expect(page.getByRole('link', { name: 'Store Title' })).toBeVisible();
  });

  test('should have New button', async ({ page }) => {
    await page.goto(
      '/administrator/index.php?option=com_magebridge&view=stores'
    );

    await expect(page.getByRole('button', { name: 'New' })).toBeVisible();
  });
});

test.describe('MageBridge URLs CRUD', () => {
  test('should display URLs list', async ({ page }) => {
    await page.goto('/administrator/index.php?option=com_magebridge&view=urls');

    // Should see toolbar
    await expect(page.getByRole('navigation', { name: 'Toolbar' })).toBeVisible();

    // Should see table headers (URLs page has Source URL and Destination URL)
    await expect(page.getByRole('link', { name: 'Source URL' })).toBeVisible();
    await expect(page.getByRole('link', { name: 'Destination URL' })).toBeVisible();
  });

  test('should have New button', async ({ page }) => {
    await page.goto('/administrator/index.php?option=com_magebridge&view=urls');

    await expect(page.getByRole('button', { name: 'New' })).toBeVisible();
  });
});

test.describe('MageBridge User Groups CRUD', () => {
  test('should display user groups list', async ({ page }) => {
    await page.goto(
      '/administrator/index.php?option=com_magebridge&view=usergroups'
    );

    // Should see toolbar
    await expect(page.getByRole('navigation', { name: 'Toolbar' })).toBeVisible();

    // Should see table header (use exact to avoid matching data rows)
    await expect(page.getByRole('link', { name: 'Description', exact: true })).toBeVisible();
  });

  test('should have New button', async ({ page }) => {
    await page.goto(
      '/administrator/index.php?option=com_magebridge&view=usergroups'
    );

    await expect(page.getByRole('button', { name: 'New' })).toBeVisible();
  });
});

test.describe('MageBridge Logs (Read-only)', () => {
  test('should display logs list', async ({ page }) => {
    await page.goto('/administrator/index.php?option=com_magebridge&view=logs');

    // Should see toolbar
    await expect(page.getByRole('navigation', { name: 'Toolbar' })).toBeVisible();

    // Should see table headers (use exact: true to avoid matching other links)
    await expect(page.getByRole('link', { name: 'Message', exact: true })).toBeVisible();
    await expect(page.getByRole('link', { name: 'Type', exact: true })).toBeVisible();
  });

  test('should have filter options', async ({ page }) => {
    await page.goto('/administrator/index.php?option=com_magebridge&view=logs');

    // Should have search functionality
    await expect(page.getByRole('textbox', { name: 'Search' })).toBeVisible();

    // Should have filter dropdowns (Origin, Type)
    await expect(page.getByRole('combobox').filter({ hasText: 'Select Origin' })).toBeVisible();
    await expect(page.getByRole('combobox').filter({ hasText: 'Select Type' })).toBeVisible();
  });

  test('should NOT have New button (read-only)', async ({ page }) => {
    await page.goto('/administrator/index.php?option=com_magebridge&view=logs');

    // Logs should only have Delete button, not New
    await expect(page.getByRole('button', { name: 'Delete' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'New' })).not.toBeVisible();
  });
});

test.describe('MageBridge Check Page', () => {
  test('should display system check information', async ({ page }) => {
    await page.goto(
      '/administrator/index.php?option=com_magebridge&view=check'
    );

    // Should have main content area
    await expect(page.getByRole('main')).toBeVisible();

    // Should see page heading
    await expect(page.getByRole('heading', { level: 1 })).toBeVisible();
  });
});
