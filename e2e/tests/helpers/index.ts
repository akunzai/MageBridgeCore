/**
 * Shared test helpers and utilities for MageBridge E2E tests.
 */

/**
 * Generate a unique test identifier based on timestamp.
 */
export function generateTestId(prefix: string = 'test'): string {
  return `${prefix}-${Date.now()}`;
}

/**
 * Joomla admin URLs.
 */
export const JoomlaAdminUrls = {
  dashboard: '/administrator/index.php',
  login: '/administrator/',
  magebridge: {
    home: '/administrator/index.php?option=com_magebridge&view=home',
    config: '/administrator/index.php?option=com_magebridge&view=config',
    check: '/administrator/index.php?option=com_magebridge&view=check',
    products: '/administrator/index.php?option=com_magebridge&view=products',
    product: '/administrator/index.php?option=com_magebridge&view=product',
    stores: '/administrator/index.php?option=com_magebridge&view=stores',
    store: '/administrator/index.php?option=com_magebridge&view=store',
    urls: '/administrator/index.php?option=com_magebridge&view=urls',
    url: '/administrator/index.php?option=com_magebridge&view=url',
    usergroups: '/administrator/index.php?option=com_magebridge&view=usergroups',
    usergroup: '/administrator/index.php?option=com_magebridge&view=usergroup',
    users: '/administrator/index.php?option=com_magebridge&view=users',
    logs: '/administrator/index.php?option=com_magebridge&view=logs',
    magento: '/administrator/index.php?option=com_magebridge&view=magento',
  },
} as const;

/**
 * Joomla site URLs.
 */
export const JoomlaSiteUrls = {
  home: '/',
  magebridge: {
    root: '/index.php?option=com_magebridge&view=root',
    ajax: '/index.php?option=com_magebridge&view=ajax&format=raw',
    cms: '/index.php?option=com_magebridge&view=cms',
    catalog: '/index.php?option=com_magebridge&view=root&request=catalog',
    customer: '/index.php?option=com_magebridge&view=root&request=customer/account',
  },
} as const;

/**
 * MageBridge configuration tabs.
 */
export const ConfigTabs = [
  'API',
  'Bridge',
  'Users',
  'CSS',
  'JavaScript',
  'Theming',
  'Debugging',
  'Other settings',
] as const;

/**
 * MageBridge admin pages list.
 */
export const AdminPages = [
  { name: 'Home', view: 'home' },
  { name: 'Products', view: 'products' },
  { name: 'Stores', view: 'stores' },
  { name: 'URLs', view: 'urls' },
  { name: 'User Groups', view: 'usergroups' },
  { name: 'Users', view: 'users' },
  { name: 'Logs', view: 'logs' },
  { name: 'Check', view: 'check' },
] as const;

/**
 * Core MageBridge plugin names for system check.
 */
export const CorePlugins = [
  'Authentication - MageBridge',
  'Magento - MageBridge',
  'MageBridge - Core',
  'User - MageBridge',
  'System - MageBridge',
  'System - MageBridge Preloader',
] as const;

/**
 * System Configuration checks.
 */
export const SystemConfigChecks = [
  'SEF',
  'SEF Rewrites',
  'Caching',
  'Cache Plugin',
  'Root item',
  'Temporary path writable',
  'Log path writable',
  'Cache writable',
] as const;

/**
 * Bridge configuration checks.
 */
export const BridgeConfigChecks = [
  'Store Relations',
  'Modify URLs',
  'Disable MooTools',
  'Link to Magento',
] as const;
