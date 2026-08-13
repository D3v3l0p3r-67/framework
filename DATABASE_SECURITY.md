# Database security boundaries

`Framework\Core\Database` separates parameter values from SQL structure:

- Values supplied to CRUD helpers are bound through PDO placeholders.
- Table names, column names, selected columns, and ordering expressions are validated before interpolation.
- `update()` and `delete()` reject empty condition arrays; intentional full-table deletion must use the visibly destructive `deleteAll()` or `truncate()` method.
- `deleteByIds()` accepts an array and creates one placeholder per ID.
- Multi-step writes should use `transaction()`, which commits the callback result or rolls back and rethrows on failure.

## Intentional raw SQL boundaries

Two APIs intentionally accept SQL text and therefore must never receive untrusted fragments:

- `Database::run()` is used for fixed, application-owned SQL and accepts a separate parameter array.
- `Database::raw()` is used by the schema helper in `server/core/Table.php`. It executes application-owned DDL and does not accept parameter values.

`SqlEditor.ExecuteQuery` is an intentionally privileged administrative capability that accepts a complete SQL statement from its caller. Its action authorization must remain restricted to administrators. Adding an explicit administrator-only action policy is tracked in `TODO.md` because enforcing it can change existing role behavior.

## Review checklist

When adding database code:

1. Prefer a CRUD helper over handwritten SQL.
2. Pass every value separately from the SQL string.
3. Never pass request data as a table name, column name, ordering expression, or SQL mode.
4. Use `transaction()` when several writes must succeed or fail together.
5. Do not log bound values; they may contain secrets or personal data.
6. Add a regression test for invalid identifiers, rollback behavior, and destructive operations.
