# Dev Containers

## Requirements

- [Docker Engine](https://docs.docker.com/install/)
- [Docker Compose V2](https://docs.docker.com/compose/cli-command/)
- [mkcert](https://github.com/FiloSottile/mkcert)
- [Visual Studio Code](https://code.visualstudio.com/)
- Bash

## Getting Start

```sh
# set up TLS certs in Host
mkdir -p .secrets
mkcert -cert-file .secrets/cert.pem -key-file .secrets/key.pem '*.dev.local'

# set up hosts in Host
echo "127.0.0.1 store.dev.local www.dev.local" | sudo tee -a /etc/hosts

# starting container or open folder in container
docker compose up -d

# install OpenMage
./openmage/install.sh
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
