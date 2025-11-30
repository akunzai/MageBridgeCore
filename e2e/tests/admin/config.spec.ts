import { test, expect } from '@playwright/test';

test.describe('MageBridge Config Page', () => {
  test.beforeEach(async ({ page }) => {
    // Navigate to MageBridge config page
    await page.goto(
      '/administrator/index.php?option=com_magebridge&view=config'
    );
  });

  test('should display config page', async ({ page }) => {
    // Should see the MageBridge config page title (contains "Configuration")
    await expect(page.getByRole('heading', { level: 1 })).toContainText(
      /Configuration/i
    );

    // Should see tabs (API, Bridge, Users, etc.)
    await expect(page.getByRole('tab', { name: 'API' })).toBeVisible();
  });

  test('should have API settings tab', async ({ page }) => {
    // Find API tab using role
    const apiTab = page.getByRole('tab', { name: 'API' });
    await expect(apiTab).toBeVisible();
    // API tab should be selected by default
    await expect(apiTab).toHaveAttribute('aria-selected', 'true');
  });

  test('should have Bridge settings tab', async ({ page }) => {
    // Find Bridge tab using role
    const bridgeTab = page.getByRole('tab', { name: 'Bridge' });
    await expect(bridgeTab).toBeVisible();
  });

  test('should display form fields in API tab', async ({ page }) => {
    // API tab should be active by default, verify hostname field
    await expect(page.getByRole('textbox', { name: 'Hostname' })).toBeVisible();

    // Verify other API fields
    await expect(page.getByRole('spinbutton', { name: 'Port' })).toBeVisible();
    await expect(page.getByRole('textbox', { name: 'API User' })).toBeVisible();
  });

  test('should be able to save configuration', async ({ page }) => {
    // Find Save button in toolbar using role
    const saveButton = page.getByRole('button', { name: 'Save', exact: true });
    await expect(saveButton).toBeVisible();

    // Also verify Save & Close button exists
    await expect(
      page.getByRole('button', { name: 'Save & Close' })
    ).toBeVisible();
  });

  test('should navigate between tabs', async ({ page }) => {
    // Verify we have multiple tabs
    const tabs = page.getByRole('tab');
    const tabCount = await tabs.count();
    expect(tabCount).toBeGreaterThan(1);

    // Click Bridge tab
    await page.getByRole('tab', { name: 'Bridge' }).click();
    await expect(
      page.getByRole('tab', { name: 'Bridge' })
    ).toHaveAttribute('aria-selected', 'true');

    // Click Users tab
    await page.getByRole('tab', { name: 'Users' }).click();
    await expect(
      page.getByRole('tab', { name: 'Users' })
    ).toHaveAttribute('aria-selected', 'true');

    // Go back to API tab
    await page.getByRole('tab', { name: 'API' }).click();
    await expect(
      page.getByRole('tab', { name: 'API' })
    ).toHaveAttribute('aria-selected', 'true');
  });

  test('should display all expected tabs', async ({ page }) => {
    // Verify all config tabs are present
    const expectedTabs = [
      'API',
      'Bridge',
      'Users',
      'CSS',
      'JavaScript',
      'Theming',
      'Debugging',
      'Other settings',
    ];

    for (const tabName of expectedTabs) {
      await expect(page.getByRole('tab', { name: tabName })).toBeVisible();
    }
  });
});
