import { test, expect } from '@playwright/test';
import { JoomlaSiteUrls } from '../../../helpers';

/**
 * Guest storefront: catalog, product, and cart render through Joomla.
 * Empty checkout is redirected to cart by Magento and is not asserted.
 */
test.describe('MageBridge Site - Guest shop flow', () => {
  test('should open store, product, and cart as a guest', async ({
    page,
  }) => {
    test.setTimeout(60000);

    await page.goto(JoomlaSiteUrls.magebridge.store);
    await expect(page.locator('#magebridge-content')).toBeVisible({
      timeout: 15000,
    });
    await expect(page.locator('#magebridge-cart')).toContainText(/no items/i);

    await page.goto(JoomlaSiteUrls.magebridge.simpleProduct);
    await expect(page.locator('#magebridge-content')).toBeVisible({
      timeout: 15000,
    });
    await expect(
      page.locator('#magebridge-content').getByRole('button', { name: /add to cart/i })
    ).toBeVisible();

    await page.goto(JoomlaSiteUrls.magebridge.cart);
    await expect(page.locator('#magebridge-content')).toBeVisible({
      timeout: 15000,
    });
    await expect(page.locator('#magebridge-content')).toContainText(
      /shopping cart|cart is empty|no items/i
    );
  });
});

