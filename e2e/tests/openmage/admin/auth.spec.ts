import { test, expect } from '@playwright/test';

/**
 * Tests for OpenMage admin authentication.
 */
test.describe('OpenMage Admin - Authentication', () => {
  test('should be logged in as admin', async ({ page }) => {
    await page.goto('/admin/');

    // Should be on admin dashboard (not login page)
    await expect(page.getByRole('heading', { name: 'Dashboard', level: 3 })).toBeVisible();
  });

  test('should display admin dashboard with stats', async ({ page }) => {
    await page.goto('/admin/');

    // Should see dashboard content - Lifetime Sales heading
    await expect(page.getByRole('heading', { name: 'Lifetime Sales', level: 4 })).toBeVisible();
  });
});
