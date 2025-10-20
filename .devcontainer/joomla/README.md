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

| Variable | Default | Description |
|----------|---------|-------------|
| `MAGEBRIDGE_HOST` | `store.dev.local` | OpenMage hostname |
| `MAGEBRIDGE_API_USER` | `magebridge_api` | API username |
| `MAGEBRIDGE_API_KEY` | `ChangeTheAp1K3y` | API key |

## System Check

> Components->`MageBridge`->`System Check`

Ensure all necessary checks have passed!
