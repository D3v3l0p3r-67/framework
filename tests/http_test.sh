#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/.."

port="${TEST_HTTP_PORT:-18765}"
base_url="http://127.0.0.1:${port}"
server_log="$(mktemp)"
response_body="$(mktemp)"

cleanup() {
    if [[ -n "${server_pid:-}" ]]; then
        kill "$server_pid" 2>/dev/null || true
        wait "$server_pid" 2>/dev/null || true
    fi
    rm -f "$server_log" "$response_body"
}
trap cleanup EXIT

php -S "127.0.0.1:${port}" -t server >"$server_log" 2>&1 &
server_pid=$!

for _ in {1..30}; do
    if curl --silent --output /dev/null "$base_url/"; then
        break
    fi
    sleep 0.1
done

assert_status() {
    local expected="$1"
    local request="$2"
    local actual

    actual="$(curl --silent --show-error --output "$response_body" --write-out '%{http_code}' \
        --request POST --data-urlencode "request=$request" "$base_url/")"

    if [[ "$actual" != "$expected" ]]; then
        echo "Expected HTTP $expected, received $actual." >&2
        cat "$response_body" >&2
        cat "$server_log" >&2
        exit 1
    fi
}

assert_status 400 '{invalid'
assert_status 404 '{"actionKey":"Missing.Run","actionParameters":[{}]}'
assert_status 401 '{"actionKey":"User.Login","actionParameters":[{"username":"missing","password":"invalid"}]}'

echo "HTTP integration tests passed."
