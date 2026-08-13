#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/.."

find server client tests -path 'server/core/vendor' -prune -o -type f -name '*.php' -print0 \
    | xargs -0 -n1 -P4 php -l >/dev/null
node --check client/js/client.js
php tests/response_factory_test.php
php tests/database_test.php
php tests/request_factory_test.php
php tests/action_test.php
php tests/user_test.php
tests/http_test.sh

echo "All checks passed."
