#!/usr/bin/env bash
# shellcheck disable=SC2310
set -e

# Set default compose file relative to this script's location
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
export COMPOSE_FILE="${COMPOSE_FILE:-${SCRIPT_DIR}/../compose.yml}"
# Disable TTY if stdin or stdout is not a terminal
TTY_FLAG=""
if [[ ! -t 0 ]] || [[ ! -t 1 ]]; then
  TTY_FLAG="-T"
fi

# Helper function for docker compose exec
dc_exec() {
  # shellcheck disable=SC2086,SC2248
  docker compose exec ${TTY_FLAG} "$@"
}

echo "=================================================="
echo "MageBridge Test Data Seeding Script"
echo "=================================================="

# shellcheck disable=1091
[[ -f "${SCRIPT_DIR}/.env" ]] && source "${SCRIPT_DIR}/.env"

# Database connection parameters
JOOMLA_DB_HOST="${JOOMLA_DB_HOST:-mysql}"
JOOMLA_DB_NAME="${JOOMLA_DB_NAME:-joomla}"
JOOMLA_DB_PREFIX="${JOOMLA_DB_PREFIX:-jos_}"
JOOMLA_DB_PASSWORD="${JOOMLA_DB_PASSWORD:-${MYSQL_ROOT_PASSWORD:-secret}}"
JOOMLA_DB_USER="${JOOMLA_DB_USER:-root}"

# Helper function for MySQL operations
mysql_exec() {
  # Always use -T for MySQL operations (no TTY needed)
  # shellcheck disable=SC2086,SC2248
  docker compose exec -T -e MYSQL_PWD="${JOOMLA_DB_PASSWORD}" mysql mysql -u"${JOOMLA_DB_USER}" "${JOOMLA_DB_NAME}" "$@"
}

# Function to check if table has data
has_data() {
  local table="$1"
  local count
  # shellcheck disable=SC2311
  count=$(mysql_exec -sN -e "SELECT COUNT(*) FROM ${JOOMLA_DB_PREFIX}${table}" 2>/dev/null) || true
  [[ "${count}" -gt 0 ]]
}

echo ""
echo "Step 1: Checking existing data..."
echo "--------------------------------------------------"

# Check which tables need seeding
SEED_LOGS=false
SEED_PRODUCTS=false
SEED_STORES=false
SEED_URLS=false
SEED_USERGROUPS=false

if ! has_data "magebridge_log"; then
  echo "✓ Logs table is empty - will seed"
  SEED_LOGS=true
else
  echo "✗ Logs table has data - skipping"
fi

if ! has_data "magebridge_products"; then
  echo "✓ Products table is empty - will seed"
  SEED_PRODUCTS=true
else
  echo "✗ Products table has data - skipping"
fi

if ! has_data "magebridge_stores"; then
  echo "✓ Stores table is empty - will seed"
  SEED_STORES=true
else
  echo "✗ Stores table has data - skipping"
fi

if ! has_data "magebridge_urls"; then
  echo "✓ URLs table is empty - will seed"
  SEED_URLS=true
else
  echo "✗ URLs table has data - skipping"
fi

if ! has_data "magebridge_usergroups"; then
  echo "✓ Usergroups table is empty - will seed"
  SEED_USERGROUPS=true
else
  echo "✗ Usergroups table has data - skipping"
fi

echo ""
echo "Step 2: Seeding test data..."
echo "--------------------------------------------------"

# Seed Logs (55 records for pagination testing)
if [[ "${SEED_LOGS}" = true ]]; then
  echo "Seeding Logs table (55 records)..."
  
  # Type values: 1=Trace, 2=Notice, 3=Warning, 4=Error, 5=Feedback, 6=Profiler
  # Origin values: joomla, magento
  mysql_exec -e "INSERT INTO ${JOOMLA_DB_PREFIX}magebridge_log (message, type, origin, section, remote_addr, session, http_agent, timestamp) VALUES
('API authentication successful', 2, 'joomla', '', '192.168.1.100', 'sess_001', 'Mozilla/5.0', '2024-12-01 10:00:00'),
('Product data synchronized', 2, 'magento', '', '192.168.1.101', 'sess_002', 'Mozilla/5.0', '2024-12-01 10:01:00'),
('Bridge connection established', 2, 'joomla', '', '192.168.1.102', 'sess_003', 'Mozilla/5.0', '2024-12-01 10:02:00'),
('User login attempt', 1, 'magento', '', '192.168.1.103', 'sess_004', 'Mozilla/5.0', '2024-12-01 10:03:00'),
('Store configuration updated', 2, 'joomla', '', '192.168.1.104', 'sess_005', 'Mozilla/5.0', '2024-12-01 10:04:00'),
('API rate limit warning', 3, 'magento', '', '192.168.1.105', 'sess_006', 'Mozilla/5.0', '2024-12-01 10:05:00'),
('Product image missing', 3, 'joomla', '', '192.168.1.106', 'sess_007', 'Mozilla/5.0', '2024-12-01 10:06:00'),
('Bridge timeout warning', 3, 'magento', '', '192.168.1.107', 'sess_008', 'Mozilla/5.0', '2024-12-01 10:07:00'),
('Invalid user credentials', 4, 'joomla', '', '192.168.1.108', 'sess_009', 'Mozilla/5.0', '2024-12-01 10:08:00'),
('Store connection failed', 4, 'magento', '', '192.168.1.109', 'sess_010', 'Mozilla/5.0', '2024-12-01 10:09:00'),
('API key validation error', 4, 'joomla', '', '192.168.1.110', 'sess_011', 'Mozilla/5.0', '2024-12-01 10:10:00'),
('Product sync completed', 2, 'magento', '', '192.168.1.111', 'sess_012', 'Mozilla/5.0', '2024-12-01 10:11:00'),
('Bridge heartbeat received', 1, 'joomla', '', '192.168.1.112', 'sess_013', 'Mozilla/5.0', '2024-12-01 10:12:00'),
('User registered successfully', 2, 'magento', '', '192.168.1.113', 'sess_014', 'Mozilla/5.0', '2024-12-01 10:13:00'),
('Store cache cleared', 2, 'joomla', '', '192.168.1.114', 'sess_015', 'Mozilla/5.0', '2024-12-01 10:15:00'),
('Deprecated function call', 3, 'magento', '', '192.168.1.115', 'sess_016', 'Mozilla/5.0', '2024-12-01 10:15:00'),
('Product price update', 2, 'joomla', '', '192.168.1.116', 'sess_017', 'Mozilla/5.0', '2024-12-01 10:16:00'),
('Bridge configuration loaded', 2, 'magento', '', '192.168.1.117', 'sess_018', 'Mozilla/5.0', '2024-12-01 10:17:00'),
('User profile updated', 2, 'joomla', '', '192.168.1.118', 'sess_019', 'Mozilla/5.0', '2024-12-01 10:18:00'),
('Store inventory synced', 2, 'magento', '', '192.168.1.119', 'sess_020', 'Mozilla/5.0', '2024-12-01 10:19:00'),
('API response cached', 1, 'joomla', '', '192.168.1.120', 'sess_021', 'Mozilla/5.0', '2024-12-01 10:20:00'),
('Product category updated', 2, 'magento', '', '192.168.1.121', 'sess_022', 'Mozilla/5.0', '2024-12-01 10:21:00'),
('Bridge SSL verification', 2, 'joomla', '', '192.168.1.122', 'sess_023', 'Mozilla/5.0', '2024-12-01 10:22:00'),
('User session expired', 3, 'magento', '', '192.168.1.123', 'sess_024', 'Mozilla/5.0', '2024-12-01 10:23:00'),
('Store language switched', 2, 'joomla', '', '192.168.1.124', 'sess_025', 'Mozilla/5.0', '2024-12-01 10:24:00'),
('API quota exceeded', 3, 'magento', '', '192.168.1.125', 'sess_026', 'Mozilla/5.0', '2024-12-01 10:25:00'),
('Product stock updated', 2, 'joomla', '', '192.168.1.126', 'sess_027', 'Mozilla/5.0', '2024-12-01 10:26:00'),
('Bridge cache invalidated', 2, 'magento', '', '192.168.1.127', 'sess_028', 'Mozilla/5.0', '2024-12-01 10:27:00'),
('User password changed', 2, 'joomla', '', '192.168.1.128', 'sess_029', 'Mozilla/5.0', '2024-12-01 10:28:00'),
('Store currency updated', 2, 'magento', '', '192.168.1.129', 'sess_030', 'Mozilla/5.0', '2024-12-01 10:29:00'),
('API endpoint deprecated', 3, 'joomla', '', '192.168.1.130', 'sess_031', 'Mozilla/5.0', '2024-12-01 10:30:00'),
('Product review posted', 2, 'magento', '', '192.168.1.131', 'sess_032', 'Mozilla/5.0', '2024-12-01 10:31:00'),
('Bridge protocol mismatch', 4, 'joomla', '', '192.168.1.132', 'sess_033', 'Mozilla/5.0', '2024-12-01 10:32:00'),
('User email verified', 2, 'magento', '', '192.168.1.133', 'sess_034', 'Mozilla/5.0', '2024-12-01 10:33:00'),
('Store discount applied', 2, 'joomla', '', '192.168.1.134', 'sess_035', 'Mozilla/5.0', '2024-12-01 10:34:00'),
('API token refreshed', 2, 'magento', '', '192.168.1.135', 'sess_036', 'Mozilla/5.0', '2024-12-01 10:35:00'),
('Product attribute added', 2, 'joomla', '', '192.168.1.136', 'sess_037', 'Mozilla/5.0', '2024-12-01 10:36:00'),
('Bridge session started', 2, 'magento', '', '192.168.1.137', 'sess_038', 'Mozilla/5.0', '2024-12-01 10:37:00'),
('User avatar uploaded', 2, 'joomla', '', '192.168.1.138', 'sess_039', 'Mozilla/5.0', '2024-12-01 10:38:00'),
('Store shipping calculated', 6, 'magento', '', '192.168.1.139', 'sess_040', 'Mozilla/5.0', '2024-12-01 10:39:00'),
('API connection timeout', 4, 'joomla', '', '192.168.1.140', 'sess_041', 'Mozilla/5.0', '2024-12-01 10:40:00'),
('Product image uploaded', 2, 'magento', '', '192.168.1.141', 'sess_042', 'Mozilla/5.0', '2024-12-01 10:41:00'),
('Bridge protocol updated', 2, 'joomla', '', '192.168.1.142', 'sess_043', 'Mozilla/5.0', '2024-12-01 10:42:00'),
('User preferences saved', 2, 'magento', '', '192.168.1.143', 'sess_044', 'Mozilla/5.0', '2024-12-01 10:43:00'),
('Store widget initialized', 6, 'joomla', '', '192.168.1.144', 'sess_045', 'Mozilla/5.0', '2024-12-01 10:44:00'),
('API method deprecated', 3, 'magento', '', '192.168.1.145', 'sess_046', 'Mozilla/5.0', '2024-12-01 10:45:00'),
('Product bundle created', 2, 'joomla', '', '192.168.1.146', 'sess_047', 'Mozilla/5.0', '2024-12-01 10:46:00'),
('Bridge queue processed', 6, 'magento', '', '192.168.1.147', 'sess_048', 'Mozilla/5.0', '2024-12-01 10:47:00'),
('User address updated', 2, 'joomla', '', '192.168.1.148', 'sess_049', 'Mozilla/5.0', '2024-12-01 10:48:00'),
('Store theme applied', 2, 'magento', '', '192.168.1.149', 'sess_050', 'Mozilla/5.0', '2024-12-01 10:49:00'),
('API version mismatch', 4, 'joomla', '', '192.168.1.150', 'sess_051', 'Mozilla/5.0', '2024-12-01 10:50:00'),
('Product variant added', 2, 'magento', '', '192.168.1.151', 'sess_052', 'Mozilla/5.0', '2024-12-01 10:51:00'),
('Bridge compression enabled', 1, 'joomla', '', '192.168.1.153', 'sess_053', 'Mozilla/5.0', '2024-12-01 10:52:00'),
('User notification sent', 5, 'magento', '', '192.168.1.153', 'sess_054', 'Mozilla/5.0', '2024-12-01 10:53:00'),
('Store checkout completed', 5, 'joomla', '', '192.168.1.154', 'sess_055', 'Mozilla/5.0', '2024-12-01 10:54:00')"
  
  echo "✓ Logs seeded successfully (55 records)"
fi

# Seed Products (35 records)
if [[ "${SEED_PRODUCTS}" = true ]]; then
  echo "Seeding Products table (35 records)..."
  
  # connector, connector_value, actions, params should be empty strings
  # shellcheck disable=SC2312
  cat <<PRODUCTSQL | mysql_exec
INSERT INTO ${JOOMLA_DB_PREFIX}magebridge_products (label, sku, connector, connector_value, actions, access, ordering, published, params) VALUES
('Widget Pro', 'WGT-001', '', '', '', 1, 1, 1, ''),
('Gadget Basic', 'GAD-002', '', '', '', 1, 2, 1, ''),
('Tool Advanced', 'TOL-003', '', '', '', 1, 3, 1, ''),
('Device Premium', 'DEV-004', '', '', '', 1, 4, 1, ''),
('Accessory Standard', 'ACC-005', '', '', '', 1, 5, 1, ''),
('Component Essential', 'CMP-006', '', '', '', 1, 6, 1, ''),
('Module Plus', 'MOD-007', '', '', '', 1, 7, 1, ''),
('Kit Complete', 'KIT-008', '', '', '', 1, 8, 1, ''),
('Bundle Mega', 'BND-009', '', '', '', 1, 9, 1, ''),
('Package Ultimate', 'PKG-010', '', '', '', 1, 10, 1, ''),
('System Pro', 'SYS-011', '', '', '', 1, 11, 1, ''),
('Platform Advanced', 'PLF-012', '', '', '', 1, 12, 1, ''),
('Framework Core', 'FRW-013', '', '', '', 1, 13, 1, ''),
('Library Extended', 'LIB-014', '', '', '', 1, 14, 1, ''),
('Extension Premium', 'EXT-015', '', '', '', 1, 15, 1, ''),
('Plugin Advanced', 'PLG-016', '', '', '', 1, 16, 1, ''),
('Addon Professional', 'ADD-017', '', '', '', 1, 17, 1, ''),
('Integration Suite', 'INT-018', '', '', '', 1, 18, 1, ''),
('Connector Plus', 'CON-019', '', '', '', 1, 19, 1, ''),
('Bridge Ultimate', 'BRG-020', '', '', '', 1, 20, 1, ''),
('Solution Enterprise', 'SOL-021', '', '', '', 1, 21, 1, ''),
('Service Business', 'SRV-022', '', '', '', 1, 22, 1, ''),
('Product Standard', 'PRD-023', '', '', '', 1, 23, 1, ''),
('Item Regular', 'ITM-024', '', '', '', 1, 24, 1, ''),
('Article Basic', 'ART-025', '', '', '', 1, 25, 1, ''),
('Piece Simple', 'PCS-026', '', '', '', 1, 26, 1, ''),
('Unit Standard', 'UNT-027', '', '', '', 1, 27, 1, ''),
('Element Core', 'ELM-028', '', '', '', 1, 28, 1, ''),
('Feature Advanced', 'FTR-029', '', '', '', 1, 29, 1, ''),
('Function Premium', 'FNC-030', '', '', '', 1, 30, 1, ''),
('Capability Pro', 'CAP-031', '', '', '', 1, 31, 1, ''),
('Resource Ultimate', 'RSC-032', '', '', '', 1, 32, 1, ''),
('Asset Enterprise', 'AST-033', '', '', '', 1, 33, 1, ''),
('Property Business', 'PRP-034', '', '', '', 1, 34, 1, ''),
('Attribute Standard', 'ATR-035', '', '', '', 1, 35, 1, '');
PRODUCTSQL
  
  echo "✓ Products seeded successfully (35 records)"
fi

# Seed Stores (30 records)
if [[ "${SEED_STORES}" = true ]]; then
  echo "Seeding Stores table (30 records)..."
  
  # connector, connector_value, actions, params should be empty strings
  # shellcheck disable=SC2312
  cat <<STORESQL | mysql_exec
INSERT INTO ${JOOMLA_DB_PREFIX}magebridge_stores (label, title, name, type, connector, connector_value, actions, access, ordering, published, params) VALUES
('Default Store View', 'Main Store', 'default', 'store_view', '', '', '', 1, 1, 1, ''),
('US Store', 'United States Store', 'us_store', 'store_view', '', '', '', 1, 2, 1, ''),
('UK Store', 'United Kingdom Store', 'uk_store', 'store_view', '', '', '', 1, 3, 1, ''),
('EU Store', 'European Store', 'eu_store', 'store_view', '', '', '', 1, 4, 1, ''),
('CA Store', 'Canadian Store', 'ca_store', 'store_view', '', '', '', 1, 5, 1, ''),
('AU Store', 'Australian Store', 'au_store', 'store_view', '', '', '', 1, 6, 1, ''),
('JP Store', 'Japanese Store', 'jp_store', 'store_view', '', '', '', 1, 7, 1, ''),
('CN Store', 'Chinese Store', 'cn_store', 'store_view', '', '', '', 1, 8, 1, ''),
('IN Store', 'Indian Store', 'in_store', 'store_view', '', '', '', 1, 9, 1, ''),
('BR Store', 'Brazilian Store', 'br_store', 'store_view', '', '', '', 1, 10, 1, ''),
('FR Store', 'French Store', 'fr_store', 'store_view', '', '', '', 1, 11, 1, ''),
('DE Store', 'German Store', 'de_store', 'store_view', '', '', '', 1, 12, 1, ''),
('IT Store', 'Italian Store', 'it_store', 'store_view', '', '', '', 1, 13, 1, ''),
('ES Store', 'Spanish Store', 'es_store', 'store_view', '', '', '', 1, 14, 1, ''),
('NL Store', 'Netherlands Store', 'nl_store', 'store_view', '', '', '', 1, 15, 1, ''),
('SE Store', 'Swedish Store', 'se_store', 'store_view', '', '', '', 1, 16, 1, ''),
('NO Store', 'Norwegian Store', 'no_store', 'store_view', '', '', '', 1, 17, 1, ''),
('DK Store', 'Danish Store', 'dk_store', 'store_view', '', '', '', 1, 18, 1, ''),
('FI Store', 'Finnish Store', 'fi_store', 'store_view', '', '', '', 1, 19, 1, ''),
('PL Store', 'Polish Store', 'pl_store', 'store_view', '', '', '', 1, 20, 1, ''),
('RU Store', 'Russian Store', 'ru_store', 'store_view', '', '', '', 1, 21, 1, ''),
('MX Store', 'Mexican Store', 'mx_store', 'store_view', '', '', '', 1, 22, 1, ''),
('AR Store', 'Argentinian Store', 'ar_store', 'store_view', '', '', '', 1, 23, 1, ''),
('CL Store', 'Chilean Store', 'cl_store', 'store_view', '', '', '', 1, 24, 1, ''),
('KR Store', 'Korean Store', 'kr_store', 'store_view', '', '', '', 1, 25, 1, ''),
('TW Store', 'Taiwan Store', 'tw_store', 'store_view', '', '', '', 1, 26, 1, ''),
('SG Store', 'Singapore Store', 'sg_store', 'store_view', '', '', '', 1, 27, 1, ''),
('MY Store', 'Malaysian Store', 'my_store', 'store_view', '', '', '', 1, 28, 1, ''),
('TH Store', 'Thai Store', 'th_store', 'store_view', '', '', '', 1, 29, 1, ''),
('VN Store', 'Vietnamese Store', 'vn_store', 'store_view', '', '', '', 1, 30, 1, '');
STORESQL
  
  echo "✓ Stores seeded successfully (30 records)"
fi

# Seed URLs (40 records)
if [[ "${SEED_URLS}" = true ]]; then
  echo "Seeding URLs table (40 records)..."

  # params should be empty string
  # shellcheck disable=SC2312
  cat <<URLSQL | mysql_exec
INSERT INTO ${JOOMLA_DB_PREFIX}magebridge_urls (source, source_type, destination, description, access, ordering, published, params) VALUES
('/shop', 0, 'catalog', 'Main shop page', 1, 1, 1, ''),
('/products', 0, 'catalog/category/view/id/3', 'Products listing', 1, 2, 1, ''),
('/cart', 0, 'checkout/cart', 'Shopping cart', 1, 3, 1, ''),
('/checkout', 0, 'checkout/onepage', 'Checkout page', 1, 4, 1, ''),
('/account', 0, 'customer/account', 'Customer account', 1, 5, 1, ''),
('/login', 0, 'customer/account/login', 'Login page', 1, 6, 1, ''),
('/register', 0, 'customer/account/create', 'Registration page', 1, 7, 1, ''),
('/wishlist', 0, 'wishlist', 'Wishlist page', 1, 8, 1, ''),
('/orders', 0, 'sales/order/history', 'Order history', 1, 9, 1, ''),
('/search', 0, 'catalogsearch/result', 'Search results', 1, 10, 1, ''),
('/categories', 0, 'catalog/category', 'Categories', 1, 11, 1, ''),
('/brands', 0, 'catalog/brand', 'Brands page', 1, 12, 1, ''),
('/deals', 0, 'catalog/deals', 'Special deals', 1, 13, 1, ''),
('/new', 0, 'catalog/new', 'New products', 1, 14, 1, ''),
('/sale', 0, 'catalog/sale', 'Sale items', 1, 15, 1, ''),
('/contact', 0, 'contacts', 'Contact us', 1, 16, 1, ''),
('/about', 0, 'cms/page/view/id/1', 'About us', 1, 17, 1, ''),
('/shipping', 0, 'cms/page/view/id/2', 'Shipping info', 1, 18, 1, ''),
('/returns', 0, 'cms/page/view/id/3', 'Returns policy', 1, 19, 1, ''),
('/privacy', 0, 'cms/page/view/id/4', 'Privacy policy', 1, 20, 1, ''),
('/terms', 0, 'cms/page/view/id/5', 'Terms of service', 1, 21, 1, ''),
('/faq', 0, 'cms/page/view/id/6', 'FAQ page', 1, 22, 1, ''),
('/blog', 0, 'blog', 'Blog', 1, 23, 1, ''),
('/reviews', 0, 'review', 'Product reviews', 1, 24, 1, ''),
('/compare', 0, 'catalog/product/compare', 'Compare products', 1, 25, 1, ''),
('/gift-cards', 0, 'giftcard', 'Gift cards', 1, 26, 1, ''),
('/promotions', 0, 'catalog/promotions', 'Promotions', 1, 27, 1, ''),
('/best-sellers', 0, 'catalog/bestsellers', 'Best sellers', 1, 28, 1, ''),
('/featured', 0, 'catalog/featured', 'Featured products', 1, 29, 1, ''),
('/clearance', 0, 'catalog/clearance', 'Clearance items', 1, 30, 1, ''),
('/outlet', 0, 'catalog/outlet', 'Outlet store', 1, 31, 1, ''),
('/accessories', 0, 'catalog/accessories', 'Accessories', 1, 32, 1, ''),
('/bundles', 0, 'catalog/bundles', 'Product bundles', 1, 33, 1, ''),
('/downloads', 0, 'downloadable/customer/products', 'My downloads', 1, 34, 1, ''),
('/membership', 0, 'customer/membership', 'Membership info', 1, 35, 1, ''),
('/rewards', 0, 'customer/rewards', 'Reward points', 1, 36, 1, ''),
('/giftregistry', 0, 'giftregistry', 'Gift registry', 1, 37, 1, ''),
('/returns-center', 0, 'rma/returns', 'Returns center', 1, 38, 1, ''),
('/store-locator', 0, 'storelocator', 'Store locator', 1, 39, 1, ''),
('/careers', 0, 'cms/page/view/id/7', 'Careers page', 1, 40, 1, '');
URLSQL

  echo "✓ URLs seeded successfully (40 records)"
fi

# Seed Usergroups (22 records)
if [[ "${SEED_USERGROUPS}" = true ]]; then
  echo "Seeding Usergroups table (22 records)..."

  # params should be empty string
  # shellcheck disable=SC2312
  cat <<USERGROUPSQL | mysql_exec
INSERT INTO ${JOOMLA_DB_PREFIX}magebridge_usergroups (label, description, joomla_group, magento_group, ordering, published, params) VALUES
('Guest to General', 'Map guests to general customer', 1, 1, 1, 1, ''),
('Registered to Wholesale', 'Registered users to wholesale', 2, 2, 2, 1, ''),
('Author to Retailer', 'Authors to retailer group', 3, 3, 3, 1, ''),
('Editor to Premium', 'Editors to premium customers', 4, 4, 4, 1, ''),
('Publisher to VIP', 'Publishers to VIP group', 5, 5, 5, 1, ''),
('Manager to Admin', 'Managers to admin group', 6, 6, 6, 1, ''),
('Bronze Members', 'Bronze tier mapping', 7, 7, 7, 1, ''),
('Silver Members', 'Silver tier mapping', 8, 8, 8, 1, ''),
('Gold Members', 'Gold tier mapping', 9, 9, 9, 1, ''),
('Platinum Members', 'Platinum tier mapping', 10, 10, 10, 1, ''),
('Student Discount', 'Student group mapping', 11, 11, 11, 1, ''),
('Senior Discount', 'Senior citizens group', 12, 12, 12, 1, ''),
('Corporate Buyers', 'Corporate customer group', 13, 13, 13, 1, ''),
('Resellers', 'Reseller group mapping', 14, 14, 14, 1, ''),
('Distributors', 'Distributor group', 15, 15, 15, 1, ''),
('Affiliates', 'Affiliate partners', 16, 16, 16, 1, ''),
('Newsletter Subscribers', 'Newsletter group', 17, 17, 17, 1, ''),
('Beta Testers', 'Beta tester group', 18, 18, 18, 1, ''),
('Early Adopters', 'Early adopter group', 19, 19, 19, 1, ''),
('Loyalty Program', 'Loyalty members', 20, 20, 20, 1, ''),
('Special Access', 'Special access group', 21, 21, 21, 1, ''),
('Partner Network', 'Partner network group', 22, 22, 22, 1, '');
USERGROUPSQL
  
  echo "✓ Usergroups seeded successfully (22 records)"
fi

echo ""
echo "=================================================="
echo "Test Data Seeding Completed!"
echo "=================================================="
echo ""
echo "Summary:"
# shellcheck disable=SC2311,SC2312
{
  echo "  - Logs: $(mysql_exec -sN -e "SELECT COUNT(*) FROM ${JOOMLA_DB_PREFIX}magebridge_log" 2>/dev/null || echo 0) records"
  echo "  - Products: $(mysql_exec -sN -e "SELECT COUNT(*) FROM ${JOOMLA_DB_PREFIX}magebridge_products" 2>/dev/null || echo 0) records"
  echo "  - Stores: $(mysql_exec -sN -e "SELECT COUNT(*) FROM ${JOOMLA_DB_PREFIX}magebridge_stores" 2>/dev/null || echo 0) records"
  echo "  - URLs: $(mysql_exec -sN -e "SELECT COUNT(*) FROM ${JOOMLA_DB_PREFIX}magebridge_urls" 2>/dev/null || echo 0) records"
  echo "  - Usergroups: $(mysql_exec -sN -e "SELECT COUNT(*) FROM ${JOOMLA_DB_PREFIX}magebridge_usergroups" 2>/dev/null || echo 0) records"
}
echo ""
