import { test, expect } from '@playwright/test';
import { JoomlaAdminUrls } from '../../helpers';

/**
 * Admin SSO: Joomla session should open Magento backend without a second login.
 */
test.describe('MageBridge Admin - Magento SSO', () => {
  test('should have SSO enabled', async ({ page }) => {
    await page.goto(JoomlaAdminUrls.magebridge.config);
    await page.getByRole('tab', { name: 'Users' }).click();
    await expect(page.locator('input[id$="enable_sso1"]')).toBeChecked();
  });

  test('should open Magento backend from Joomla without a Magento login form', async ({
    page,
  }) => {
    test.setTimeout(45000);

    await page.goto(JoomlaAdminUrls.magebridge.magento);
    await page.waitForLoadState('domcontentloaded');

    await expect(page.getByText('View not found')).not.toBeVisible();
    await expect(page.locator('#loginForm')).toHaveCount(0);

    await expect(page).toHaveURL(/store\.dev\.local|view=magento/, {
      timeout: 15000,
    });
    await expect(page.locator('h3.head-dashboard').first()).toBeVisible({
      timeout: 15000,
    });
  });
});
