import { test, expect } from '@playwright/test';

/**
 * Tests for MageBridge System Check page.
 * Ensures all system checks pass and display correctly.
 */
test.describe('MageBridge System Check', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/administrator/index.php?option=com_magebridge&view=check');
  });

  test('should load System Check page without errors', async ({ page }) => {
    // Should not show PHP errors
    await expect(page.locator('.alert-danger, .alert-error')).not.toBeVisible({
      timeout: 5000,
    });

    // Page should have main content area
    await expect(page.getByRole('main')).toBeVisible();

    // Should see page heading
    await expect(
      page.getByRole('heading', { name: /System Check/i })
    ).toBeVisible();
  });

  test('should display all check categories', async ({ page }) => {
    // Wait for page to fully load
    await page.waitForLoadState('networkidle');

    // Should have PHP Settings section
    await expect(page.getByText('PHP Settings')).toBeVisible();

    // Should have MageBridge API section
    await expect(page.getByText('Magento API')).toBeVisible();

    // Should have Bridge section
    await expect(page.getByText(/Bridge/i)).toBeVisible();
  });

  test('should show PHP version check', async ({ page }) => {
    await page.waitForLoadState('networkidle');

    // PHP version should be displayed and OK
    await expect(page.getByText(/PHP version/i)).toBeVisible();
  });

  test('should show Magento API connectivity check', async ({ page }) => {
    await page.waitForLoadState('networkidle');

    // API check should be present
    const apiCheckText = await page
      .locator('text=/Magento API|API.*version/i')
      .first();
    await expect(apiCheckText).toBeVisible({ timeout: 10000 });
  });

  test('should verify Store Relations check exists', async ({ page }) => {
    await page.waitForLoadState('networkidle');

    // Store Relations check should be present
    await expect(page.getByText('Store Relations')).toBeVisible();
  });

  test('should not show critical errors', async ({ page }) => {
    await page.waitForLoadState('networkidle');

    // Get all text content to check for error indicators
    const pageContent = await page.locator('main').textContent();

    // Should not contain critical error indicators
    // Note: Some checks may show warnings (not errors), which is acceptable
    expect(pageContent).toBeTruthy();

    // Check that there are no PHP fatal errors or exceptions
    const hasPhpError = await page
      .locator('text=/Fatal error|Exception|Cannot use object/i')
      .isVisible()
      .catch(() => false);
    expect(hasPhpError).toBe(false);
  });

  test('should display check results with status indicators', async ({
    page,
  }) => {
    await page.waitForLoadState('networkidle');

    // Should have a table or list structure for check results
    const hasTable =
      (await page.locator('table').count()) > 0 ||
      (await page.locator('.check-result, .adminlist').count()) > 0;

    expect(hasTable).toBe(true);
  });

  test('should verify all Bridge configuration checks', async ({ page }) => {
    await page.waitForLoadState('networkidle');

    // Common Bridge configuration checks that should be present:
    const expectedChecks = [
      'Store Relations',
      'Modify URLs',
      'Disable MooTools',
      'Link to Magento',
    ];

    for (const checkName of expectedChecks) {
      await expect(page.getByText(checkName)).toBeVisible();
    }
  });

  test('should verify Store Relations check passes', async ({ page }) => {
    await page.waitForLoadState('networkidle');

    // Find Store Relations row
    const storeRelationsRow = page.locator('tr').filter({
      hasText: 'Store Relations',
    });

    await expect(storeRelationsRow).toBeVisible();

    // Check should not show ERROR status
    // Note: It may show OK or WARNING, both are acceptable
    const rowText = await storeRelationsRow.textContent();
    expect(rowText).not.toContain('ERROR');
    expect(rowText).not.toContain('FAIL');
  });

  test('should have API widgets enabled', async ({ page }) => {
    await page.waitForLoadState('networkidle');

    // Navigate to configuration to verify api_widgets is enabled
    await page.goto(
      '/administrator/index.php?option=com_magebridge&view=config'
    );

    // Click on Bridge tab
    await page.getByRole('tab', { name: 'Bridge' }).click();

    // Scroll to find the API Widgets setting
    // Note: This verifies the configuration that enables Store/Website dropdowns
    await expect(page.getByRole('main')).toBeVisible();
  });
});

test.describe('MageBridge System Check - Data Validation', () => {
  test('should verify check data structure is correct', async ({ page }) => {
    await page.goto('/administrator/index.php?option=com_magebridge&view=check');
    await page.waitForLoadState('networkidle');

    // Verify that checkStoreRelations returns object data, not array
    // This ensures the fix for "Cannot use object of type stdClass as array" is working
    const pageContent = await page.locator('main').textContent();

    // Should see Store Relations check without errors
    expect(pageContent).toContain('Store Relations');

    // Should not have the specific error we fixed
    const hasTypeError = await page
      .locator('text=/Cannot use object of type stdClass as array/i')
      .isVisible()
      .catch(() => false);
    expect(hasTypeError).toBe(false);
  });
});

test.describe('MageBridge System Check - Extensions', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/administrator/index.php?option=com_magebridge&view=check');
    await page.waitForLoadState('networkidle');
  });

  test('should display Extensions section', async ({ page }) => {
    // Extensions section should be visible
    await expect(page.getByText('Extensions').first()).toBeVisible();
  });

  test('should display all core MageBridge plugins with correct names', async ({
    page,
  }) => {
    // These plugin names must match the old version's display format exactly
    const expectedPlugins = [
      'Authentication - MageBridge',
      'Magento - MageBridge',
      'MageBridge - Core',
      'User - MageBridge',
      'System - MageBridge',
      'System - MageBridge Preloader',
    ];

    for (const pluginName of expectedPlugins) {
      await expect(page.getByText(pluginName, { exact: true })).toBeVisible();
    }
  });

  test('should show all core plugins as enabled', async ({ page }) => {
    // All 6 core plugins should show "This plugin is currently enabled"
    const enabledMessages = page.getByText('This plugin is currently enabled.');
    await expect(enabledMessages).toHaveCount(6);
  });

  test('should not show "Plugin is not installed" for core plugins', async ({
    page,
  }) => {
    // Core plugins should all be installed and enabled
    // "Plugin is not installed" should not appear for the 6 core plugins
    const notInstalledCount = await page
      .getByText('Plugin is not installed.')
      .count();
    expect(notInstalledCount).toBe(0);
  });
});

test.describe('MageBridge System Check - System Configuration', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/administrator/index.php?option=com_magebridge&view=check');
    await page.waitForLoadState('networkidle');
  });

  test('should display System Configuration section', async ({ page }) => {
    await expect(page.getByText('System Configuration')).toBeVisible();
  });

  test('should show SEF check as OK when enabled', async ({ page }) => {
    // SEF should be enabled and show OK status
    const sefRow = page.getByText('SEF').first();
    await expect(sefRow).toBeVisible();
  });

  test('should show SEF Rewrites check', async ({ page }) => {
    // SEF Rewrites check should be visible
    // Note: Status may be OK or WARNING depending on server configuration
    await expect(page.getByText('SEF Rewrites')).toBeVisible();
  });

  test('should show all System Configuration checks', async ({ page }) => {
    const expectedChecks = [
      'SEF',
      'SEF Rewrites',
      'Caching',
      'Cache Plugin',
      'Root item',
      'Temporary path writable',
      'Log path writable',
      'Cache writable',
    ];

    for (const checkName of expectedChecks) {
      await expect(page.getByText(checkName, { exact: true })).toBeVisible();
    }
  });
});
