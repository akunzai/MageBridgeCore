import { test, expect } from '@playwright/test';
import { JoomlaAdminUrls, CorePlugins, SystemConfigChecks, BridgeConfigChecks } from '../../helpers';

/**
 * Tests for MageBridge System Check page.
 */
test.describe('MageBridge Admin - System Check', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(JoomlaAdminUrls.magebridge.check);
    // Wait for the check page to fully load (it makes API calls)
    await page.waitForLoadState('networkidle');
  });

  test.describe('Page Loading', () => {
    test('should load System Check page without errors', async ({ page }) => {
      await expect(page.locator('.alert-danger, .alert-error')).not.toBeVisible({
        timeout: 5000,
      });

      await expect(page.getByRole('main')).toBeVisible();
      await expect(
        page.getByRole('heading', { name: /System Check/i })
      ).toBeVisible();
    });

    test('should display all check categories', async ({ page }) => {
      // These are the actual fieldset/group names on the page
      await expect(page.getByRole('group', { name: 'Suggestions' })).toBeVisible({ timeout: 15000 });
      await expect(page.getByRole('group', { name: 'System Compatibility' })).toBeVisible();
      await expect(page.getByRole('group', { name: 'Bridge Checks' })).toBeVisible();
    });
  });

  test.describe('System Compatibility Checks', () => {
    test('should show PHP version check', async ({ page }) => {
      await expect(page.getByRole('group', { name: 'System Compatibility' })).toBeVisible({ timeout: 15000 });
      await expect(page.getByRole('cell', { name: 'PHP version', exact: true })).toBeVisible();
    });
  });

  test.describe('Bridge Checks', () => {
    test('should show Bridge Checks section', async ({ page }) => {
      await expect(page.getByRole('group', { name: 'Bridge Checks' })).toBeVisible({ timeout: 15000 });
    });
  });

  test.describe('Bridge Configuration Checks', () => {
    test('should verify all Bridge configuration checks', async ({ page }) => {
      // Wait for Bridge Checks section to load
      await expect(page.getByRole('group', { name: 'Bridge Checks' })).toBeVisible({ timeout: 15000 });

      for (const checkName of BridgeConfigChecks) {
        await expect(page.getByText(checkName).first()).toBeVisible();
      }
    });

    test('should verify Store Relations check passes', async ({ page }) => {
      // Wait for Bridge Checks section to load
      await expect(page.getByRole('group', { name: 'Bridge Checks' })).toBeVisible({ timeout: 15000 });

      const storeRelationsRow = page.locator('tr').filter({
        hasText: 'Store Relations',
      });

      await expect(storeRelationsRow.first()).toBeVisible();

      const rowText = await storeRelationsRow.first().textContent();
      expect(rowText).not.toContain('ERROR');
      expect(rowText).not.toContain('FAIL');
    });
  });

  test.describe('System Configuration Checks', () => {
    test('should display System Configuration section', async ({ page }) => {
      await expect(page.getByText('System Configuration')).toBeVisible();
    });

    test('should show all System Configuration checks', async ({ page }) => {
      for (const checkName of SystemConfigChecks) {
        await expect(page.getByText(checkName, { exact: true })).toBeVisible();
      }
    });
  });

  test.describe('Extensions Checks', () => {
    test('should display Extensions section', async ({ page }) => {
      await expect(page.getByText('Extensions').first()).toBeVisible();
    });

    test('should display all core MageBridge plugins', async ({ page }) => {
      for (const pluginName of CorePlugins) {
        await expect(page.getByText(pluginName, { exact: true })).toBeVisible();
      }
    });

    test('should show all core plugins as enabled', async ({ page }) => {
      const enabledMessages = page.getByText('This plugin is currently enabled.');
      await expect(enabledMessages).toHaveCount(6);
    });

    test('should not show "Plugin is not installed" for core plugins', async ({
      page,
    }) => {
      const notInstalledCount = await page
        .getByText('Plugin is not installed.')
        .count();
      expect(notInstalledCount).toBe(0);
    });
  });

  test.describe('Data Validation', () => {
    test('should verify check data structure is correct', async ({ page }) => {
      const pageContent = await page.locator('main').textContent();
      expect(pageContent).toContain('Store Relations');

      // Should not have the stdClass array error
      const hasTypeError = await page
        .locator('text=/Cannot use object of type stdClass as array/i')
        .isVisible()
        .catch(() => false);
      expect(hasTypeError).toBe(false);
    });

    test('should not show critical errors', async ({ page }) => {
      const pageContent = await page.locator('main').textContent();
      expect(pageContent).toBeTruthy();

      const hasPhpError = await page
        .locator('text=/Fatal error|Exception|Cannot use object/i')
        .isVisible()
        .catch(() => false);
      expect(hasPhpError).toBe(false);
    });

    test('should display check results with status indicators', async ({
      page,
    }) => {
      const hasTable =
        (await page.locator('table').count()) > 0 ||
        (await page.locator('.check-result, .adminlist').count()) > 0;

      expect(hasTable).toBe(true);
    });
  });
});
