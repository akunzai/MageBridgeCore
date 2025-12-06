# OpenMage Setup

The `install.sh` script automatically configures the following:

- OpenMage installation with admin user
- Sample data (optional)
- MageBridge module deployment
- API Role (`MageBridge`) with full access
- API User (`magebridge_api` / `ChangeTheAp1K3y`)
- MageBridge configuration (auto-detect enabled, auto-configure IPs disabled)

## Manual Configuration (Optional)

### MageBridge Core - Debugging

> System->Configuration->Services->`MageBridge`

Enable these settings when debugging problems:

- Debug Log: `Yes`
- Print errors: `Yes`

## System Check

> CMS->`MageBridge`->`System Check`

Ensure all necessary checks have passed!
