FROM alpine:3.20

ARG SASS_VERSION=1.83.4
ARG TARGETARCH

RUN set -eux; \
    case "${TARGETARCH}" in \
      amd64) arch=x64 ;; \
      arm64) arch=arm64 ;; \
      *) echo "Unsupported architecture: ${TARGETARCH}" >&2; exit 1 ;; \
    esac; \
    wget -qO- "https://github.com/sass/dart-sass/releases/download/${SASS_VERSION}/dart-sass-${SASS_VERSION}-linux-${arch}-musl.tar.gz" \
      | tar -xz -C /opt; \
    ln -s /opt/dart-sass/sass /usr/local/bin/sass; \
    sass --version

ARG UID=1000
ARG GID=1000
RUN addgroup -g ${GID} app && adduser -D -u ${UID} -G app app

USER app
WORKDIR /var/www
ENTRYPOINT ["sass"]
