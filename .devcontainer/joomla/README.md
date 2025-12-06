# Joomla! Setup

The `install.sh` script automatically configures the following:

- Joomla! installation with admin user
- MageBridge extension installation
- Enable MageBridge plugins (Authentication, Content, Core, Magento, System, User)
- MageBridge configuration:
  - Hostname: `store.dev.local`
  - API User: `magebridge_api`
  - API Key: `ChangeTheAp1K3y`
  - Protocol: `HTTPS`
  - Enforce SSL: `Entire site`
  - User synchronization settings
- Store menu item (MageBridge Root)

## Environment Variables

You can customize the configuration by setting these environment variables in `.env`:

| Variable                | Default            | Description         |
| ----------------------- | ------------------ | ------------------- |
| `MAGEBRIDGE_HOST`       | `store.dev.local`  | OpenMage hostname   |
| `MAGEBRIDGE_API_USER`   | `magebridge_api`   | API username        |
| `MAGEBRIDGE_API_KEY`    | `ChangeTheAp1K3y`  | API key             |

## Test Data Seeding

The `seed-test-data.sh` script populates test data for E2E testing and pagination verification.

### Usage

```bash
docker compose exec joomla \
  /workspace/.devcontainer/joomla/seed-test-data.sh
```

### What It Seeds

|Table|Records|Purpose|
|---|---|---|
|Logs|55|Pagination testing (3 pages with default 20/page)|
|Products|35|Pagination testing (2 pages)|
|Stores|30|Pagination testing (2 pages)|
|URLs|25|Pagination testing (2 pages)|
|Usergroups|22|Pagination testing (2 pages)|

### Features

- **Idempotent**: Only inserts data into empty tables
- **CI-Ready**: Automatically used in GitHub Actions workflows
- **Environment Variables**: Supports custom DB credentials

```bash
# Custom database configuration
DB_HOST=mysql DB_USER=root DB_PASS=secret TABLE_PREFIX=jos_ \
  ./seed-test-data.sh
```

## System Check

> Components->`MageBridge`->`System Check`

Ensure all necessary checks have passed!
