#!/usr/bin/env bash
set -euo pipefail

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
IMAGE="php:8.1-cli@sha256:76e563191d1ade120313a8736df24154d21da5155c0756f147c0b01bd19d9087"
USE_SUDO_DOCKER="${USE_SUDO_DOCKER:-0}"
COMPOSER_URL="https://getcomposer.org/download/2.8.10/composer.phar"
COMPOSER_SHA256="28dbb6bd8bef31479c7985b774c130a8bda37dbe63c35b56f6cb6bc377427573"

docker_cmd() {
    if [[ "${USE_SUDO_DOCKER}" == "1" ]]; then
        sudo docker "$@"
    else
        docker "$@"
    fi
}

fail_deprecation_output() {
    local label="$1"
    local file="$2"
    local stripped
    stripped="$(mktemp)"
    sed -E 's/\x1b\[[0-9;]*[[:alpha:]]//g' "${file}" >"${stripped}"
    if grep -E '(^|[[:space:]])(PHP[[:space:]]+)?(Warning|Deprecated|Notice):' "${stripped}" >/dev/null; then
        echo "${label}: output contains Warning, Deprecated, or Notice lines" >&2
        grep -E '(^|[[:space:]])(PHP[[:space:]]+)?(Warning|Deprecated|Notice):' "${stripped}" >&2 || true
        rm -f "${stripped}"
        exit 1
    fi
    rm -f "${stripped}"
}

INNER_SCRIPT="$(mktemp)"
trap 'rm -f "${INNER_SCRIPT}"' EXIT

cat >"${INNER_SCRIPT}" <<'EOF'
#!/usr/bin/env bash
set -euo pipefail

export COMPOSER_ALLOW_SUPERUSER=1
export DEBIAN_FRONTEND=noninteractive

fail_deprecation_output() {
    local label="$1"
    local file="$2"
    local stripped
    stripped="$(mktemp)"
    sed -E 's/\x1b\[[0-9;]*[[:alpha:]]//g' "${file}" >"${stripped}"
    if grep -E '(^|[[:space:]])(PHP[[:space:]]+)?(Warning|Deprecated|Notice):' "${stripped}" >/dev/null; then
        echo "${label}: output contains Warning, Deprecated, or Notice lines" >&2
        grep -E '(^|[[:space:]])(PHP[[:space:]]+)?(Warning|Deprecated|Notice):' "${stripped}" >&2 || true
        rm -f "${stripped}"
        exit 1
    fi
    rm -f "${stripped}"
}

apt-get update
apt-get install -y --no-install-recommends ca-certificates curl git unzip libzip-dev
docker-php-ext-install zip
rm -rf /var/lib/apt/lists/*

php -v | tee /tmp/php-version.txt
php -r '
$v = PHP_VERSION;
if (version_compare($v, "8.1.0", "<")) {
    fwrite(STDERR, "PHP {$v} is older than required 8.1\n");
    exit(1);
}
'

for ext in curl dom json libxml mbstring; do
    if ! php -m | grep -qi "^${ext}$"; then
        echo "Missing required PHP extension: ${ext}" >&2
        if [[ "${ext}" == "mbstring" ]]; then
            apt-get update
            apt-get install -y --no-install-recommends libonig-dev
            docker-php-ext-install mbstring
            rm -rf /var/lib/apt/lists/*
        else
            exit 1
        fi
    fi
done

COMPOSER_URL="https://getcomposer.org/download/2.8.10/composer.phar"
COMPOSER_SHA256="28dbb6bd8bef31479c7985b774c130a8bda37dbe63c35b56f6cb6bc377427573"
curl -fsSL -o /tmp/composer.phar "${COMPOSER_URL}"
echo "${COMPOSER_SHA256}  /tmp/composer.phar" | sha256sum -c -

rm -f composer.lock
git config --global --add safe.directory '*'

composer_out="$(mktemp)"
set +e
php /tmp/composer.phar update --no-interaction --prefer-dist --no-progress >"${composer_out}" 2>&1
composer_status=$?
set -e
cat "${composer_out}"
fail_deprecation_output "composer update" "${composer_out}"
if [[ ${composer_status} -ne 0 ]]; then
    rm -f "${composer_out}"
    exit "${composer_status}"
fi
rm -f "${composer_out}"

php scripts/check-cluster-pins.php

samples_out="$(mktemp)"
set +e
{
    sample_count=0
    while IFS= read -r -d '' sample_file; do
        php -l "${sample_file}" || exit 1
        sample_count=$((sample_count + 1))
    done < <(find samples -type f -name '*.php' -print0)
    echo "sample-lint: OK (${sample_count} files)"

    php -r '
require "vendor/autoload.php";
$classes = [
    "Sample\\Pay_Pal_Client",
    "Sample\\AuthorizeIntentExamples\\Create_Order",
    "Sample\\AuthorizeIntentExamples\\Authorize_Order",
    "Sample\\AuthorizeIntentExamples\\Capture_Order",
    "Sample\\CaptureIntentExamples\\Create_Order",
    "Sample\\CaptureIntentExamples\\Capture_Order",
    "Sample\\Error_Sample",
    "Sample\\Get_Order",
    "Sample\\Patch_Order",
    "Sample\\Refund_Order",
];
foreach ($classes as $class) {
    if (!class_exists($class)) {
        fwrite(STDERR, "sample autoload missing: {$class}\n");
        exit(1);
    }
}
echo "sample-autoload: OK (" . count($classes) . " classes)\n";
'
} >"${samples_out}" 2>&1
samples_status=$?
set -e
cat "${samples_out}"
fail_deprecation_output "sample lint/autoload" "${samples_out}"
if [[ ${samples_status} -ne 0 ]]; then
    rm -f "${samples_out}"
    exit "${samples_status}"
fi
if ! grep -E '^sample-lint: OK \([0-9]+ files\)$' "${samples_out}" >/dev/null; then
    echo 'sample lint: missing exact success line' >&2
    rm -f "${samples_out}"
    exit 1
fi
if ! grep -E '^sample-autoload: OK \([0-9]+ classes\)$' "${samples_out}" >/dev/null; then
    echo 'sample autoload: missing exact success line' >&2
    rm -f "${samples_out}"
    exit 1
fi
rm -f "${samples_out}"

audit_out="$(mktemp)"
set +e
php /tmp/composer.phar audit --no-interaction --abandoned=report >"${audit_out}" 2>&1
audit_status=$?
set -e
cat "${audit_out}"
fail_deprecation_output "composer audit" "${audit_out}"
if [[ ${audit_status} -ne 0 ]]; then
    rm -f "${audit_out}"
    exit "${audit_status}"
fi
if ! grep -Fxq 'No security vulnerability advisories found.' "${audit_out}"; then
    echo 'composer audit: missing exact success line' >&2
    rm -f "${audit_out}"
    exit 1
fi
rm -f "${audit_out}"

phpunit_out="$(mktemp)"
set +e
vendor/bin/phpunit --colors=never --no-coverage >"${phpunit_out}" 2>&1
phpunit_status=$?
set -e
cat "${phpunit_out}"
fail_deprecation_output "phpunit" "${phpunit_out}"
if [[ ${phpunit_status} -ne 0 ]]; then
    rm -f "${phpunit_out}"
    exit "${phpunit_status}"
fi
if ! grep -E '^OK \([0-9]+ tests?, [0-9]+ assertions?\)$' "${phpunit_out}" >/dev/null; then
    echo 'phpunit: missing strict OK success line' >&2
    rm -f "${phpunit_out}"
    exit 1
fi
rm -f "${phpunit_out}"

echo 'system-test: all steps passed.'
EOF

chmod +x "${INNER_SCRIPT}"

docker_cmd run --rm \
    -e CLIENT_ID="${CLIENT_ID:-}" \
    -e CLIENT_SECRET="${CLIENT_SECRET:-}" \
    -v "${REPO_ROOT}:/app" \
    -v "${INNER_SCRIPT}:/tmp/system-test-inner.sh:ro" \
    -w /app \
    "${IMAGE}" \
    bash /tmp/system-test-inner.sh
