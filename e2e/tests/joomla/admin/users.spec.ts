import { test, expect } from '@playwright/test';
import { JoomlaAdminUrls } from '../../helpers';

test.describe('MageBridge Admin - Users', () => {
  test.describe('List Page', () => {
    test('should display users list', async ({ page }) => {
      await page.goto(JoomlaAdminUrls.magebridge.users);
      
      // Page title contains "Users" (could be "Users Syncing" or "MageBridge: Users")
      await expect(page.locator('h1')).toContainText('Users');
      await expect(page.locator('#adminForm')).toBeVisible();
    });

    test('should have Export button', async ({ page }) => {
      await page.goto(JoomlaAdminUrls.magebridge.users);
      
      const exportButton = page.getByRole('button', { name: 'Export' });
      await expect(exportButton).toBeVisible();
    });

    test('should have Import button', async ({ page }) => {
      await page.goto(JoomlaAdminUrls.magebridge.users);
      
      const importButton = page.getByRole('button', { name: 'Import' });
      await expect(importButton).toBeVisible();
    });

    test('should not have CRUD buttons (New, Edit, Copy, Delete)', async ({
      page,
    }) => {
      await page.goto(JoomlaAdminUrls.magebridge.users);
      
      // Should not have New button
      const newButton = page.getByRole('button', { name: 'New', exact: true });
      await expect(newButton).not.toBeVisible();
      
      // Should not have Edit button
      const editButton = page.getByRole('button', { name: 'Edit', exact: true });
      await expect(editButton).not.toBeVisible();
      
      // Should not have Copy button
      const copyButton = page.getByRole('button', { name: 'Copy' });
      await expect(copyButton).not.toBeVisible();
      
      // Should not have Delete button
      const deleteButton = page.getByRole('button', { name: 'Delete' });
      await expect(deleteButton).not.toBeVisible();
    });

    test('should only show Export and Import buttons in toolbar', async ({
      page,
    }) => {
      await page.goto(JoomlaAdminUrls.magebridge.users);
      
      // Get all toolbar buttons
      const toolbar = page.locator('[role="toolbar"]');
      const buttons = toolbar.getByRole('button');
      
      // Count visible buttons (should be 2: Export and Import)
      const buttonCount = await buttons.count();
      
      // Should have exactly 2 buttons
      expect(buttonCount).toBe(2);
      
      // Verify they are Export and Import
      const exportButton = page.getByRole('button', { name: 'Export' });
      const importButton = page.getByRole('button', { name: 'Import' });
      
      await expect(exportButton).toBeVisible();
      await expect(importButton).toBeVisible();
    });
  });

  test.describe('Export Functionality', () => {
    test('should download CSV file when clicking Export', async ({
      page,
    }) => {
      await page.goto(JoomlaAdminUrls.magebridge.users);
      
      // Start waiting for download before clicking
      const downloadPromise = page.waitForEvent('download');
      
      // Click Export button
      await page.getByRole('button', { name: 'Export' }).click();
      
      // Wait for download to start
      const download = await downloadPromise;
      
      // Verify filename contains expected pattern
      const filename = download.suggestedFilename();
      expect(filename).toMatch(/magebridge-export-joomla-users.*\.csv/);
    });
  });

  test.describe('Import Functionality', () => {
    test('should have Import button visible', async ({
      page,
    }) => {
      await page.goto(JoomlaAdminUrls.magebridge.users);
      
      // Verify Import button exists and is visible
      const importButton = page.getByRole('button', { name: 'Import' });
      await expect(importButton).toBeVisible();
      
      // Note: Import functionality requires CSV upload which is better tested manually
      // or with more complex file upload scenarios
    });
  });
});
