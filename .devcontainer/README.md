# Dev Containers

## Requirements

- [Docker Engine](https://docs.docker.com/install/)
- [Docker Compose](https://docs.docker.com/compose/cli-command/)
- [Visual Studio Code](https://code.visualstudio.com/)
- Bash

## Getting Start

```sh
# set up TLS certs in Host
mkdir -p .secrets
mkcert -cert-file .secrets/cert.pem -key-file .secrets/key.pem '*.dev.local'
cp "$(mkcert -CAROOT)/rootCA.pem" .secrets/ca.pem

# set up hosts in Host
echo "127.0.0.1 www.dev.local store.dev.local" | sudo tee -a /etc/hosts

# starting container (Joomla 6, default)
docker compose up -d

# starting container (Joomla 5)
docker compose build --build-arg JOOMLA_VERSION=5.4.3 --build-arg PHP_VERSION=8.3 joomla
docker compose up -d

# starting container for debug
# > use VSCode to attach running joomla container for Xdebug
docker compose -f compose.yml -f compose.debug.yml up -d

# install or update the OpenMage module
./openmage/install.sh

# install or update the Joomla extension
./joomla/install.sh

# force re-install the Joomla extension
./joomla/install.sh --force

# seed test data
./joomla/seed-test-data.sh

# run e2e test
pnpm -C ../e2e test
```

## Admin URLs

- [OpenMage](https://store.dev.local/admin/)
- [Joomla!](https://www.dev.local/administrator/)

## Credentials

- Username: `admin`
- Password: `ChangeTheP@ssw0rd`

## Setup

- [OpenMage](./openmage/)
- [Joomla!](./joomla/)
