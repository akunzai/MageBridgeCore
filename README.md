# MageBridgeCore

[![Build Status][build-badge]][build]

[build]: https://github.com/akunzai/MageBridgeCore/actions/workflows/build.yml
[build-badge]: https://github.com/akunzai/MageBridgeCore/actions/workflows/build.yml/badge.svg

This is the repository for the MageBridge Open Core. It includes the main Joomla! component as well as vital plugins, plus the Magento extension. With it, a fully bridged site can be built.

## Requirements

- PHP >= 8.3
- [Composer](https://getcomposer.org/)
- [Joomla!](https://www.joomla.org/) 5.x or 6.x
- [OpenMage](https://github.com/OpenMage/magento-lts) 20.x

## Getting Started

```sh
# install dependencies
composer install --ignore-platform-reqs

# check coding style
composer run lint

# static code analysis
composer run phpstan

# bundle the Joomla! extension. The `pkg_magebridge.zip` can be found in the `dist/` directory
./bundle.sh
```

## Installation

### Joomla

> see [joomla setup document](./.devcontainer/joomla/) for details

Navigate to `System->Install->Extensions` in Joomla! backend and upload the package file `pkg_magebridge.zip` to install

> You can get notified once a new version is released and update this extension through Joomla! admin UI

### OpenMage

> [modman](https://github.com/colinmollenhour/modman) is required,
> see [OpenMage setup document](./.devcontainer/openmage/) for details

```sh
# install
modman clone MageBridge https://github.com/akunzai/MageBridgeCore.git

# update
modman update MageBridge
```

## History of this extension

- [Jisse Reitsma](https://github.com/jissereitsma) created the [MageBridgeCore](https://github.com/MageBridge/MageBridgeCore)
- [Charley Wu](https://github.com/akunzai) continue the [MageBridgeCore](https://github.com/akunzai/MageBridgeCore) and make it compatible with Joomla! 4.x
