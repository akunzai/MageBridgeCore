import { test, expect } from '@playwright/test';
import { JoomlaSiteUrls } from '../../helpers';

/**
 * Tests for MageBridge Ajax handler and CMS page views.
 */
test.describe('MageBridge Site - Ajax Handler', () => {
  test('should have ajax view available', async ({ page }) => {
    await page.goto(JoomlaSiteUrls.magebridge.ajax);
    await expect(page.locator('text=Fatal error')).not.toBeVisible();
  });
});

test.describe('MageBridge Site - CMS Page', () => {
  test('should load CMS page view', async ({ page }) => {
    await page.goto(JoomlaSiteUrls.magebridge.cms);

    await expect(page.locator('text=Fatal error')).not.toBeVisible();

    const body = page.locator('body');
    await expect(body).toBeVisible();
  });
});
