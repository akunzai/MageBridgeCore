import { test, expect } from '@playwright/test';

/**
 * Tests for OpenMage admin general functionality.
 */
test.describe('OpenMage Admin - General Functionality', () => {
  test('should navigate to Catalog > Manage Products', async ({ page }) => {
    await page.goto('/admin/');

    await page.getByRole('link', { name: 'Catalog', exact: true }).hover();
    await page.getByRole('link', { name: 'Manage Products' }).click();
    await page.waitForLoadState('networkidle');

    await expect(page.locator('#productGrid')).toBeVisible();
  });

  test('should navigate to Customers > Manage Customers', async ({ page }) => {
    await page.goto('/admin/');

    await page.locator('#nav-admin-customer').hover();
    await page.getByRole('link', { name: 'Manage Customers' }).click();
    await page.waitForLoadState('networkidle');

    await expect(page.locator('#customerGrid')).toBeVisible();
  });

  test('should navigate to System > Cache Management', async ({ page }) => {
    await page.goto('/admin/');

    await page.getByRole('link', { name: 'System', exact: true }).hover();
    await page.getByRole('link', { name: 'Cache Management' }).click();
    await page.waitForLoadState('networkidle');

    await expect(page.locator('#cache_grid')).toBeVisible();
  });
});
