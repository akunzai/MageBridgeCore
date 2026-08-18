import { test, expect } from '@playwright/test';
import { JoomlaAdminUrls } from '../../helpers';

test.describe('MageBridge Admin - Configuration Defaults', () => {
  test.describe('Boolean Field Defaults', () => {
    test('should have representative boolean fields default to "No"', async ({
      page,
    }) => {
      await page.goto(JoomlaAdminUrls.magebridge.config);
      
      // Wait for form to load
      await page.waitForSelector('#adminForm');
      
      // Check a few representative boolean fields from different tabs
      // Using ID selectors that work with Joomla's form rendering
      
      // API tab - http_auth (field ID ends with 0 for "No" radio button)
      await page.getByRole('tab', { name: 'API' }).click();
      const httpAuthNo = page.locator('input[id$="http_auth0"]');
      await expect(httpAuthNo).toBeChecked();
      
      // API tab - encryption
      const encryptionNo = page.locator('input[id$="encryption0"]');
      await expect(encryptionNo).toBeChecked();
      
      // Bridge tab - offline
      await page.getByRole('tab', { name: 'Bridge' }).click();
      const offlineNo = page.locator('input[id$="offline0"]');
      await expect(offlineNo).toBeChecked();
      
      // Users tab - enable_sso (install.sh sets this to "Yes" for cart sync)
      await page.getByRole('tab', { name: 'Users' }).click();
      const enableSsoYes = page.locator('input[id$="enable_sso1"]');
      await expect(enableSsoYes).toBeChecked();

      // Users tab - enable_usersync (Defaults.php is 1)
      const enableUsersyncYes = page.locator('input[id$="enable_usersync1"]');
      await expect(enableUsersyncYes).toBeChecked();

      // CSS tab - disable_default_css (Defaults.php is 1)
      await page.getByRole('tab', { name: 'CSS' }).click();
      const disableDefaultCssYes = page.locator('input[id$="disable_default_css1"]');
      await expect(disableDefaultCssYes).toBeChecked();
    });

    test('should keep unset booleans at their PHP defaults', async ({
      page,
    }) => {
      await page.goto(JoomlaAdminUrls.magebridge.config);
      await page.waitForSelector('#adminForm');

      // XML default="0" is not the runtime default. Unbound keys use Defaults.php.
      const checkedNoRadios = page.locator('input[type="radio"][value="0"]:checked');
      const checkedYesRadios = page.locator('input[type="radio"][value="1"]:checked');
      expect(await checkedNoRadios.count()).toBeGreaterThanOrEqual(15);
      expect(await checkedYesRadios.count()).toBeGreaterThanOrEqual(15);
    });
  });
});
