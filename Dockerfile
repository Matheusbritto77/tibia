ARG CANARY_IMAGE=ghcr.io/opentibiabr/canary:latest
FROM ${CANARY_IMAGE}

USER root

RUN if command -v apt-get >/dev/null 2>&1; then \
        apt-get update \
        && apt-get install -y --no-install-recommends \
            bash \
            ca-certificates \
            default-mysql-client \
            redis-tools \
        && rm -rf /var/lib/apt/lists/*; \
    elif command -v apk >/dev/null 2>&1; then \
        apk add --no-cache \
            bash \
            ca-certificates \
            mariadb-client \
            redis; \
    else \
        echo "Unsupported base image package manager." >&2; \
        exit 1; \
    fi

WORKDIR /canary

COPY entrypoint.sh /usr/local/bin/canary-entrypoint.sh
COPY schema.sql /canary/schema.sql

RUN chmod +x /usr/local/bin/canary-entrypoint.sh

ENTRYPOINT ["/bin/bash", "/usr/local/bin/canary-entrypoint.sh"]
CMD ["/usr/bin/canary"]
