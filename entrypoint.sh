#!/usr/bin/env bash
set -euo pipefail

: "${CANARY_DB_HOST:=db}"
: "${CANARY_DB_PORT:=3306}"
: "${CANARY_DB_NAME:=canary}"
: "${CANARY_DB_USER:=canary}"
: "${CANARY_DB_PASSWORD:=canary}"
: "${CANARY_SERVER_NAME:=astarOT}"
: "${CANARY_SERVER_IP:=127.0.0.1}"
: "${CANARY_SERVER_LOCATION:=BRA}"
: "${CANARY_LOGIN_PORT:=7171}"
: "${CANARY_GAME_PORT:=7172}"
: "${CANARY_STATUS_PORT:=7173}"
: "${CANARY_STATUS_TIMEOUT:=5000}"
: "${CANARY_LEGACY_1100_GAME_PORT:=0}"
: "${CANARY_LEGACY_860_GAME_PORT:=0}"
: "${CANARY_DATA_PACK:=data-otservbr-global}"

escape_lua() {
	printf '%s' "$1" | sed 's/\\/\\\\/g; s/"/\\"/g'
}

require_uint() {
	local name="$1"
	local value="$2"
	if [[ ! "$value" =~ ^[0-9]+$ ]]; then
		echo "Invalid ${name}: '${value}'. Use only unsigned integer values." >&2
		exit 1
	fi
}

wait_for_database() {
	for attempt in $(seq 1 90); do
		if MYSQL_PWD="$CANARY_DB_PASSWORD" mysql \
			--protocol=tcp \
			-h "$CANARY_DB_HOST" \
			-P "$CANARY_DB_PORT" \
			-u "$CANARY_DB_USER" \
			"$CANARY_DB_NAME" \
			-e "SELECT 1" >/dev/null 2>&1; then
			return 0
		fi

		echo "Waiting for database (${attempt}/90)"
		sleep 2
	done

	echo "Database did not become available." >&2
	exit 1
}

table_exists() {
	local table_name="$1"
	MYSQL_PWD="$CANARY_DB_PASSWORD" mysql \
		--protocol=tcp \
		-h "$CANARY_DB_HOST" \
		-P "$CANARY_DB_PORT" \
		-u "$CANARY_DB_USER" \
		"$CANARY_DB_NAME" \
		-N -B \
		-e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = '${table_name}'"
}

require_uint "CANARY_DB_PORT" "$CANARY_DB_PORT"
require_uint "CANARY_LOGIN_PORT" "$CANARY_LOGIN_PORT"
require_uint "CANARY_GAME_PORT" "$CANARY_GAME_PORT"
require_uint "CANARY_STATUS_PORT" "$CANARY_STATUS_PORT"
require_uint "CANARY_STATUS_TIMEOUT" "$CANARY_STATUS_TIMEOUT"
require_uint "CANARY_LEGACY_1100_GAME_PORT" "$CANARY_LEGACY_1100_GAME_PORT"
require_uint "CANARY_LEGACY_860_GAME_PORT" "$CANARY_LEGACY_860_GAME_PORT"

mkdir -p /canary/data/XML
cat > /canary/config.lua <<EOF
serverName = "$(escape_lua "$CANARY_SERVER_NAME")"
ip = "$(escape_lua "$CANARY_SERVER_IP")"
location = "$(escape_lua "$CANARY_SERVER_LOCATION")"
loginProtocolPort = ${CANARY_LOGIN_PORT}
gameProtocolPort = ${CANARY_GAME_PORT}
legacy1100GameProtocolPort = ${CANARY_LEGACY_1100_GAME_PORT}
legacy860GameProtocolPort = ${CANARY_LEGACY_860_GAME_PORT}
statusProtocolPort = ${CANARY_STATUS_PORT}
statusTimeout = ${CANARY_STATUS_TIMEOUT}
worldType = "pvp"
dataPackDirectory = "$(escape_lua "$CANARY_DATA_PACK")"
mysqlHost = "$(escape_lua "$CANARY_DB_HOST")"
mysqlPort = ${CANARY_DB_PORT}
mysqlUser = "$(escape_lua "$CANARY_DB_USER")"
mysqlPass = "$(escape_lua "$CANARY_DB_PASSWORD")"
mysqlDatabase = "$(escape_lua "$CANARY_DB_NAME")"
passwordType = "argon2"
EOF

cat > /canary/data/XML/vocations.xml <<'EOF'
<?xml version="1.0" encoding="UTF-8"?>
<vocations>
	<vocation id="0" name="None" fromvoc="0" />
	<vocation id="1" name="Sorcerer" fromvoc="1" />
	<vocation id="2" name="Druid" fromvoc="2" />
	<vocation id="3" name="Paladin" fromvoc="3" />
	<vocation id="4" name="Knight" fromvoc="4" />
	<vocation id="5" name="Master Sorcerer" fromvoc="1" />
	<vocation id="6" name="Elder Druid" fromvoc="2" />
	<vocation id="7" name="Royal Paladin" fromvoc="3" />
	<vocation id="8" name="Elite Knight" fromvoc="4" />
</vocations>
EOF

cat > /canary/data/XML/groups.xml <<'EOF'
<?xml version="1.0" encoding="UTF-8"?>
<groups>
	<group id="1" name="player" access="0" maxdepotitems="0" maxvipentries="0" />
	<group id="6" name="god" access="1" maxdepotitems="0" maxvipentries="200" />
</groups>
EOF

wait_for_database

if [[ "$(table_exists accounts)" -eq 0 || "$(table_exists players)" -eq 0 ]]; then
	echo "Importing schema.sql into ${CANARY_DB_NAME}"
	MYSQL_PWD="$CANARY_DB_PASSWORD" mysql \
		--protocol=tcp \
		-h "$CANARY_DB_HOST" \
		-P "$CANARY_DB_PORT" \
		-u "$CANARY_DB_USER" \
		"$CANARY_DB_NAME" < /canary/schema.sql
fi

exec "$@"
