# Deployment guide

This checklist applies to the security-hardening release that introduces hashed passwords, strict action exposure, CSRF validation, and real HTTP error statuses.

## Before deployment

1. Back up the application files and SQLite database:

   ```bash
   cp server/database/main.db "server/database/main.db.$(date +%Y%m%d%H%M%S).bak"
   ```

2. Confirm the target environment provides PHP 8.2 or newer with `pdo_sqlite`, and Node.js if checks will run on the server.
3. Create `server/config.local.php` from `server/config.example.php`. Keep these production values disabled:

   ```php
   'debug' => false,
   'action_audit' => false,
   ```

4. Serve the application through HTTPS. Session cookies only receive the `Secure` attribute when PHP detects HTTPS.
5. Run the checks against the exact revision being deployed:

   ```bash
   ./scripts/check.sh
   ```

## Deploy and migrate

1. Put the application in maintenance mode or otherwise stop writes.
2. Deploy the new files without replacing the production `server/database/main.db`.
3. Run the transactional password migration once:

   ```bash
   php scripts/migrate_passwords.php
   ```

   A successful first run reports how many passwords were migrated. A repeated run must report `Password migration was already applied.`

4. Ensure the web-server user can write to `server/database/` and `server/logs/`, but cannot expose either directory as downloadable content.
5. Restart PHP-FPM or clear the opcode cache when applicable.
6. Disable maintenance mode.

## Smoke test

Verify all of the following over HTTPS:

- The client loads without browser console errors.
- A valid user can log in and receives a new session cookie.
- An invalid password returns HTTP `401` and does not reveal whether the username exists.
- Refreshing the application retains the authenticated session.
- A state-changing request without `X-CSRF-Token` returns HTTP `403`.
- Normal navigation and a state-changing form submission succeed with the client-provided CSRF token.
- Logout destroys the session and returns to the login screen.
- An unknown action returns HTTP `404` and malformed JSON returns HTTP `400`.
- `server/logs/` contains no passwords, CSRF tokens, or request secrets.

## Required post-deployment action

Rotate every migrated password. Although the live database stores hashes after migration, historical plaintext values may still exist in old Git commits, backups, or deployment artifacts.

## Rollback

1. Put the application back in maintenance mode.
2. Restore both the previous application revision and its matching database backup. Do not restore only one of them because the old login implementation cannot authenticate hashed passwords.
3. Restart PHP and run the previous release smoke test.
4. Preserve failed-release logs for diagnosis, ensuring they are access-controlled.
