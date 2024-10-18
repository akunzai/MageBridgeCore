# OpenMage Setup

## Create API Role

> System->`Web Services`->`SOAP/XML-RPC Roles`->`Add New Role`

- Role Name: `MageBridge`
- Resource Access: `All`

## Create API User

> System->`Web Services`->`SOAP/XML-RPC Users`->`Add New User`

- User Name: `magebridge_api`
- First Name: `Mage`
- Last Name: `Bridge`
- Email: `magebridge@example.com`
- API Key: `ChangeTheAp1K3y`
- User Role: `MageBridge`

## Configuration

> System->Configuration->Services->`MageBridge`

### MageBridge Core - API

> Once Joomla! accesses MageBridge, the API configuration is automatically configured

- Joomla! API auto-detect: `Yes`
- Auto-configure allowed IPs: `No`

### MageBridge Core - Debugging

> once you needs debug problems

- Debug Log: `Yes`
- Print errors: `Yes`

## System Check

> CMS->`MageBridge`->`System Check`

Ensure all necessary checks have passed!
