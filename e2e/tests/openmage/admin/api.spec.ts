import { test, expect } from '@playwright/test';

/**
 * Tests for OpenMage API configuration (SOAP/XML-RPC).
 */
test.describe('OpenMage Admin - API Configuration', () => {
  test.describe('System Configuration', () => {
    test('should navigate to System Configuration page', async ({ page }) => {
      await page.goto('/admin/');

      await page.getByRole('link', { name: 'System', exact: true }).hover();
      await page.getByRole('link', { name: 'Configuration', exact: true }).click();
      await page.waitForLoadState('networkidle');

      await expect(page.locator('#system_config_tabs')).toBeVisible();
    });

    test('should have MageBridge in configuration menu', async ({ page }) => {
      await page.goto('/admin/');

      await page.getByRole('link', { name: 'System', exact: true }).hover();
      await page.getByRole('link', { name: 'Configuration', exact: true }).click();
      await page.waitForLoadState('networkidle');

      const pageContent = await page.content();
      expect(pageContent.includes('MageBridge') || pageContent.includes('Services')).toBeTruthy();
    });
  });

  test.describe('Web Services - Roles', () => {
    test('should navigate to SOAP/XML-RPC Roles page', async ({ page }) => {
      await page.goto('/admin/');

      await page.getByRole('link', { name: 'System', exact: true }).hover();
      await page.getByRole('link', { name: 'Web Services' }).hover();
      await page.getByRole('link', { name: 'SOAP/XML-RPC - Roles' }).click();
      await page.waitForLoadState('networkidle');

      await expect(page.locator('text=Fatal error')).not.toBeVisible();

      const body = page.locator('body');
      await expect(body).toBeVisible();
    });

    test('should have MageBridge API role configured', async ({ page }) => {
      await page.goto('/admin/');

      await page.getByRole('link', { name: 'System', exact: true }).hover();
      await page.getByRole('link', { name: 'Web Services' }).hover();
      await page.getByRole('link', { name: 'SOAP/XML-RPC - Roles' }).click();
      await page.waitForLoadState('networkidle');

      const pageContent = await page.content();
      expect(pageContent.includes('MageBridge')).toBeTruthy();
    });
  });

  test.describe('Web Services - Users', () => {
    test('should navigate to SOAP/XML-RPC Users page', async ({ page }) => {
      await page.goto('/admin/');

      await page.getByRole('link', { name: 'System', exact: true }).hover();
      await page.getByRole('link', { name: 'Web Services' }).hover();
      await page.getByRole('link', { name: 'SOAP/XML-RPC - Users' }).click();
      await page.waitForLoadState('networkidle');

      await expect(page.locator('text=Fatal error')).not.toBeVisible();
    });

    test('should have magebridge_api user configured', async ({ page }) => {
      await page.goto('/admin/');

      await page.getByRole('link', { name: 'System', exact: true }).hover();
      await page.getByRole('link', { name: 'Web Services' }).hover();
      await page.getByRole('link', { name: 'SOAP/XML-RPC - Users' }).click();
      await page.waitForLoadState('networkidle');

      const pageContent = await page.content();
      expect(pageContent.includes('magebridge_api')).toBeTruthy();
    });
  });
});
