FROM debian:bookworm-slim

ENV DEBIAN_FRONTEND=noninteractive \
    VCPKG_ROOT=/opt/vcpkg

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        autoconf \
        build-essential \
        ca-certificates \
        cmake \
        curl \
        default-mysql-client \
        git \
        libtool \
        ninja-build \
        pkg-config \
        redis-tools \
        tar \
        unzip \
        zip \
    && rm -rf /var/lib/apt/lists/*

RUN git clone --filter=blob:none --depth 1 https://github.com/microsoft/vcpkg /opt/vcpkg \
    && git -C /opt/vcpkg fetch --unshallow --tags \
    && /opt/vcpkg/bootstrap-vcpkg.sh -disableMetrics

WORKDIR /canary
COPY . /canary

# Build the production image with a faster release profile.
RUN cmake --preset linux-release -DTOGGLE_BIN_FOLDER=OFF -DCMAKE_BUILD_TYPE=Release -DOPTIONS_ENABLE_IPO=OFF \
    && cmake --build --preset linux-release --target canary -j"$(nproc)"

COPY entrypoint.sh /usr/local/bin/canary-entrypoint.sh

ENTRYPOINT ["/bin/bash", "/usr/local/bin/canary-entrypoint.sh"]
CMD ["/canary/canary"]
