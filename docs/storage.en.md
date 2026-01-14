# Consent Storage (EN)

Language switch: [Deutsch](storage.de.md) | [Index](index.en.md)

## Cookie (default)

No extra setup required. Consent is stored in the `cookie_consent` cookie.

## Doctrine

Set `storage: doctrine` or `storage: both` and create the table:

```sql
CREATE TABLE cookie_consent (
  id VARCHAR(64) PRIMARY KEY,
  preferences JSON NOT NULL,
  policy_version VARCHAR(32) NOT NULL,
  decided_at DATETIME NULL
);
```

The identifier is stored in `cookie_consent_id` (configurable via `identifier_cookie`).

## Combined

`storage: both` writes to cookie and database; reads prefer the cookie and fall back to the database.
