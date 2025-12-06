import { test, expect } from '@playwright/test';
import { JoomlaAdminUrls, ConfigTabs } from '../../helpers';

/**
 * Tests for MageBridge Configuration page.
 */
test.describe('MageBridge Admin - Configuration Page', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(JoomlaAdminUrls.magebridge.config);
  });

  test.describe('Page Display', () => {
    test('should display config page with title', async ({ page }) => {
      await expect(page.getByRole('heading', { level: 1 })).toContainText(
        /Configuration/i
      );
    });

    test('should display all expected tabs', async ({ page }) => {
      for (const tabName of ConfigTabs) {
        await expect(page.getByRole('tab', { name: tabName })).toBeVisible();
      }
    });
  });

  test.describe('API Tab', () => {
    test('should have API tab selected by default', async ({ page }) => {
      const apiTab = page.getByRole('tab', { name: 'API' });
      await expect(apiTab).toBeVisible();
      await expect(apiTab).toHaveAttribute('aria-selected', 'true');
    });

    test('should display API form fields', async ({ page }) => {
      await expect(page.getByRole('textbox', { name: 'Hostname' })).toBeVisible();
      await expect(page.getByRole('spinbutton', { name: 'Port' })).toBeVisible();
      await expect(page.getByRole('textbox', { name: 'API User' })).toBeVisible();
    });
  });

  test.describe('Tab Navigation', () => {
    test('should navigate to Bridge tab', async ({ page }) => {
      await page.getByRole('tab', { name: 'Bridge' }).click();
      await expect(
        page.getByRole('tab', { name: 'Bridge' })
      ).toHaveAttribute('aria-selected', 'true');
    });

    test('should navigate to Users tab', async ({ page }) => {
      await page.getByRole('tab', { name: 'Users' }).click();
      await expect(
        page.getByRole('tab', { name: 'Users' })
      ).toHaveAttribute('aria-selected', 'true');
    });

    test('should navigate back to API tab', async ({ page }) => {
      // Go to Bridge tab first
      await page.getByRole('tab', { name: 'Bridge' }).click();

      // Go back to API tab
      await page.getByRole('tab', { name: 'API' }).click();
      await expect(
        page.getByRole('tab', { name: 'API' })
      ).toHaveAttribute('aria-selected', 'true');
    });
  });

  test.describe('Save Actions', () => {
    test('should have Save button in toolbar', async ({ page }) => {
      await expect(
        page.getByRole('button', { name: 'Save', exact: true })
      ).toBeVisible();
    });

    test('should have Save & Close button in toolbar', async ({ page }) => {
      await expect(
        page.getByRole('button', { name: 'Save & Close' })
      ).toBeVisible();
    });
  });

  test.describe('Import/Export Functionality', () => {
    test('should display Export button in toolbar', async ({ page }) => {
      const exportButton = page.getByRole('button', { name: 'Export', exact: true });
      await expect(exportButton).toBeVisible();
    });

    test('should display Import button in toolbar', async ({ page }) => {
      const importButton = page.getByRole('button', { name: 'Import', exact: true });
      await expect(importButton).toBeVisible();
    });

    test('should navigate to import layout when clicking Import button', async ({ page }) => {
      // Click Import button
      await page.getByRole('button', { name: 'Import', exact: true }).click();

      // Wait for the import form to be visible (page redirects to import layout)
      await expect(page.getByText('Choose a file to upload:')).toBeVisible({ timeout: 10000 });

      // Verify URL has changed to import layout
      await expect(page).toHaveURL(/option=com_magebridge&view=config&layout=import/);

      // Verify upload button is displayed
      await expect(page.getByRole('button', { name: /upload.*xml/i })).toBeVisible();
      
      // Verify file input exists with correct attributes
      const fileInput = page.locator('input[type="file"][name="xml"]');
      await expect(fileInput).toBeVisible();
      await expect(fileInput).toHaveAttribute('accept', '.xml');
    });
  });
});
