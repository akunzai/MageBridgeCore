import { test as setup, expect } from '@playwright/test';

const OPENMAGE_ADMIN_FILE = 'playwright/.auth/openmage-admin.json';

/**
 * Setup authentication state for OpenMage admin user.
 * This runs once before all OpenMage tests and saves the authenticated state.
 */
setup('authenticate as OpenMage admin', async ({ page }) => {
  // OpenMage admin URL
  const baseURL = process.env.OPENMAGE_URL || 'https://store.dev.local';

  console.log(`Navigating to OpenMage admin: ${baseURL}/admin/`);

  // Navigate to OpenMage admin login page with extended timeout
  // In CI, the page might take longer to load initially
  await page.goto(`${baseURL}/admin/`, { 
    waitUntil: 'domcontentloaded',
    timeout: 60000 
  });

  // Wait for page to stabilize
  await page.waitForLoadState('load', { timeout: 30000 });

  console.log('Page loaded, waiting for login form...');

  // Wait for login form to be visible with extended timeout
  // Use a more robust selector strategy with multiple fallbacks
  try {
    await page.waitForSelector('form#loginForm', { 
      state: 'visible',
      timeout: 60000 
    });
  } catch (error) {
    // Take screenshot for debugging
    await page.screenshot({ path: 'test-results/openmage-login-error.png', fullPage: true });
    console.error('Login form not found. Page content:');
    console.error(await page.content());
    throw error;
  }

  console.log('Login form found, filling credentials...');

  // Fill in login credentials from environment or use defaults
  const username = process.env.OPENMAGE_ADMIN_USERNAME || 'admin';
  const password = process.env.OPENMAGE_ADMIN_PASSWORD || 'ChangeTheP@ssw0rd';

  await page.fill('input#username', username);
  await page.fill('input#login', password);

  console.log('Credentials filled, submitting form...');

  // Click login button and wait for navigation
  await Promise.all([
    page.waitForNavigation({ waitUntil: 'load', timeout: 60000 }),
    page.click('input.form-button[type="submit"]')
  ]);

  console.log('Form submitted, waiting for dashboard...');

  // Wait for successful login - should redirect to admin dashboard
  // Look for Dashboard heading which confirms we're logged in
  await expect(page.getByRole('heading', { name: 'Dashboard', level: 3 })).toBeVisible({ timeout: 60000 });

  // Verify we're logged in by checking for the Dashboard link in navigation
  await expect(page.getByRole('link', { name: 'Dashboard' })).toBeVisible({ timeout: 10000 });

  console.log('Successfully logged in, saving auth state...');

  // Save authentication state
  await page.context().storageState({ path: OPENMAGE_ADMIN_FILE });

  console.log('Auth state saved successfully');
});
