#!/bin/env bash
set -e

if [[ "$1" == apache2* ]]; then
    uid="$(id -u)"
    gid="$(id -g)"
    if [[ "${uid}" = '0' ]]; then
        case "$1" in
        apache2*)
            user="${APACHE_RUN_USER:-www-data}"
            group="${APACHE_RUN_GROUP:-www-data}"

            # strip off any '#' symbol ('#1000' is valid syntax for Apache)
            pound='#'
            user="${user#"${pound}"}"
            group="${group#"${pound}"}"

            # set user if not exist
            if ! id "${user}" &>/dev/null; then
                # get the user name
                : "${USER_NAME:=www-data}"
                # change the user name
                [[ "${USER_NAME}" != "www-data" ]] &&
                    usermod -l "${USER_NAME}" www-data &&
                    groupmod -n "${USER_NAME}" www-data
                # update the user ID
                groupmod -o -g "${user}" "${USER_NAME}"
                # update the user-group ID
                usermod -o -u "${group}" "${USER_NAME}"
            fi
            ;;
        *)
            user='www-data'
            group='www-data'
            ;;
        esac
    else
        user="${uid}"
        group="${gid}"
    fi
    if [[ ! -e '/var/www/html/app/etc/config.xml' ]]; then
        sourceTarArgs=(
            --create
            --file -
            --directory /usr/src/openmage
            --one-file-system
            --owner "${user}" --group "${group}"
        )
        targetTarArgs=(
            --extract
            --file -
            --directory /var/www/html
        )
        if [[ "${uid}" != '0' ]]; then
            # avoid "tar: .: Cannot utime: Operation not permitted" and "tar: .: Cannot change mode to rwxr-xr-x: Operation not permitted"
            targetTarArgs+=(--no-overwrite-dir)
        fi
        # shellcheck disable=2312
        tar "${sourceTarArgs[@]}" . | tar "${targetTarArgs[@]}"
        echo >&2 "OpenMage has been successfully copied to /var/www/html"
    fi
fi

exec "$@"
