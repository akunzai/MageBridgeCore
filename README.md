# MageBridgeCore

[![Build Status][ci-badge]][ci]

[ci]: https://github.com/akunzai/MageBridgeCore/actions?query=workflow%3ACI
[ci-badge]: https://github.com/akunzai/MageBridgeCore/workflows/CI/badge.svg

This is the repository for the MageBridge Open Core. It includes the main Joomla! component as well as vital plugins, plus the Magento extension. With it, a fully bridged site can be built.

## Requirements

- PHP >= 8.1
- [Composer](https://getcomposer.org/)
- [Joomla!](https://www.joomla.org/) 3.10.x or 4.x or 5.0.x
- [OpenMage](https://github.com/OpenMage/magento-lts) 19.x or 20.x

## Build

> build the Joomla! extension. The `pkg_magebridge.zip` will be stored in the `dist/` directory

```sh
./build.sh
```

## Installation

### Joomla!

> see [here](./.devcontainer/joomla/) for details

Navigate to `System->Install->Extensions` in Joomla! backend and upload the package file `pkg_magebridge.zip` to install

> You can get notified once a new version is released and update this extension through Joomla! admin UI

### OpenMage

> [modman](https://github.com/colinmollenhour/modman) is required,
> see [here](./.devcontainer/openmage/) for details

```sh
# install
modman clone MageBridge https://github.com/akunzai/MageBridgeCore.git

# update
modman update MageBridge
```

## History of this extension

- [Jisse Reitsma](https://github.com/jissereitsma) created the [MageBridgeCore](https://github.com/MageBridge/MageBridgeCore)
- [Charley Wu](https://github.com/akunzai) continue the [MageBridgeCore](https://github.com/akunzai/MageBridgeCore) and make it compatible with Joomla! 4.x.
