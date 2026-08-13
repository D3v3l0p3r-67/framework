# TODO

## Server

- Revisit the anonymous `Session::getUserId()` fallback value (`2`). Changing it to `null` requires an audit of all callers.
- Rotate all migrated user passwords: their former plaintext values remain recoverable from Git history even though the live database now contains hashes.

## Client

- Audit server-rendered templates and add HTML sanitization before inserting them into the DOM.
- Tighten the Content Security Policy by removing inline scripts/styles and choosing either local Monaco assets or the CDN, not both.
- Split `client/js/client.js` into testable modules while preserving the public `Framework` API.
- Add browser/unit tests for request serialization, responses, tabs, confirmations, network failures, and XSS payloads.
- Add user-visible network errors, timeouts, loading state, retry support, and duplicate-submit protection.
- Sequence `Application.ColdStart` and deep-link requests to avoid response-order races.
- Remove obsolete tracked client artifacts after confirming they are not part of deployment.
