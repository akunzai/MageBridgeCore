# Joomla Setup

## Extension Installation

> System->Install->Extensions

Upload Package File: `pkg_magebridge.zip`

## Enable Extensions

> System->Manage->Extensions

- Plugin: `Authentication - MageBridge`
- Plugin: `Content - MageBridge`
- Plugin: `MageBridge - Core`
- Plugin: `Magento - MageBridge`
- Plugin: `System - MageBridge`
- Plugin: `User - MageBridge`

## Configuration

> Components->`MageBridge`->Configuration

### API

- Hostname: `store.dev.local`
- API User: `magebridge_api`
- API Key: `ChangeTheAp1K3y`

### Bridge

- Protocol: `HTTPS`
- Enforce SSL: `Entire site`

### Users

User synchronization

- Magento Customer Group: `General`
- Joomla! Usergroup: `Registered`
- Username from Email: `Yes`

User importing and exporting

- Website: `Main Website (1)`
- Customer Group: `General`

## Add Root item

> Menus->`Main Menu`->`Add New Menu Item`

### Details

- Title: `Store`
- Alias: `store`
- Menu Item Type: `MageBridge`->`Root`

### Magento Scope

- Store/Store View: `English(default)`

## System Check

> Components->`MageBridge`->`System Check`

Ensure all necessary checks have passed!
