import { test, expect } from '@playwright/test';

/**
 * Tests for MageBridge Menu Items (Joomla Menus integration).
 */
test.describe('MageBridge Admin - Menu Items', () => {
  const storeMenuItemUrl = '/administrator/index.php?option=com_menus&view=item&client_id=0&layout=edit&id=112';

  test.describe('Store Menu Item - Magento Scope Fields', () => {
    test.beforeEach(async ({ page }) => {
      await page.goto(storeMenuItemUrl);
      
      // Navigate to Magento Scope tab
      await page.getByRole('tab', { name: 'Magento Scope' }).click();
      await expect(
        page.getByRole('tab', { name: 'Magento Scope' })
      ).toHaveAttribute('aria-selected', 'true');
    });

    test('should display Magento Scope tab', async ({ page }) => {
      await expect(page.getByRole('tab', { name: 'Magento Scope' })).toBeVisible();
    });

    test('should display Store/Store View field as dropdown', async ({ page }) => {
      // Find the Store/Store View field - could be select (with API data) or text input (without API data)
      const storeSelect = page.locator('select[name="jform[params][store]"]');
      const storeInput = page.locator('input[name="jform[params][store]"]');
      
      // Check if select dropdown exists (API data available)
      const selectCount = await storeSelect.count();
      
      if (selectCount > 0) {
        // Verify it's a select dropdown
        await expect(storeSelect).toBeVisible();
        await expect(storeSelect).toHaveClass(/form-select/);
        
        // Verify options are loaded (at least "-- Select --" option)
        const options = await storeSelect.locator('option').allTextContents();
        expect(options.length).toBeGreaterThan(0);
      } else {
        // Fallback to text input when API data is not available
        await expect(storeInput).toBeVisible();
        test.skip(true, 'Store field is text input (OpenMage API data not available)');
      }
    });

    test('should have Store/Store View dropdown with correct options', async ({ page }) => {
      const storeSelect = page.locator('select[name="jform[params][store]"]');
      const selectCount = await storeSelect.count();
      
      // Skip if select element doesn't exist (API data not available)
      if (selectCount === 0) {
        test.skip(true, 'Store field is text input (OpenMage API data not available)');
        return;
      }
      
      // Verify select element has options
      const options = await storeSelect.locator('option').allTextContents();
      
      // Should have at least the default "-- Select --" option
      expect(options.length).toBeGreaterThan(0);
      expect(options[0]).toContain('Select');
      
      // Check if store options from OpenMage API are available
      const hasStoreOptions = options.some(opt => 
        opt.includes('Madison Island') || opt.includes('English') || opt.includes('French')
      );
      
      if (!hasStoreOptions && options.length <= 1) {
        test.skip(true, 'OpenMage API store data not available');
      }
      
      expect(hasStoreOptions).toBe(true);
    });

    test('should display Website field as dropdown', async ({ page }) => {
      const websiteSelect = page.locator('select[name="jform[params][website]"]');
      const websiteInput = page.locator('input[name="jform[params][website]"]');
      
      const selectCount = await websiteSelect.count();
      
      if (selectCount > 0) {
        await expect(websiteSelect).toBeVisible();
        await expect(websiteSelect).toHaveClass(/form-select/);
        
        // Verify options are loaded (at least "-- Select --" option)
        const options = await websiteSelect.locator('option').allTextContents();
        expect(options.length).toBeGreaterThan(0);
      } else {
        // Fallback to text input when API data is not available
        await expect(websiteInput).toBeVisible();
        test.skip(true, 'Website field is text input (OpenMage API data not available)');
      }
    });

    test('should have Website dropdown with Main Website option', async ({ page }) => {
      const websiteField = page.locator('select[name="jform[params][website]"]');
      
      const options = await websiteField.locator('option').allTextContents();
      
      // Check if website options from OpenMage API are available
      const hasMainWebsite = options.some(opt => opt.includes('Main Website'));
      
      // Skip if API data is not loaded (CI environment may not have API connection)
      if (!hasMainWebsite && options.length <= 1) {
        test.skip(true, 'OpenMage API website data not available');
      }
      
      expect(hasMainWebsite).toBe(true);
    });

    test('should allow selecting a store view', async ({ page }) => {
      const storeField = page.locator('select[name="jform[params][store]"]');
      
      // Get all options
      const options = await storeField.locator('option').all();
      
      // Find a non-empty option (not the "-- Select --" option)
      let selectedOption = null;
      for (const option of options) {
        const value = await option.getAttribute('value');
        if (value && value !== '') {
          selectedOption = value;
          break;
        }
      }
      
      if (selectedOption) {
        // Select the option
        await storeField.selectOption(selectedOption);
        
        // Verify selection
        const selectedValue = await storeField.inputValue();
        expect(selectedValue).toBe(selectedOption);
      }
    });
  });
});
