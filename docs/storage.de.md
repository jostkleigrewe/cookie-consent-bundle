# Consent-Speicherung (DE)

Language switch: [English](storage.en.md) | [Index](index.de.md)

## Cookie (Standard)

Keine zusätzliche Einrichtung nötig. Consent wird im Cookie `cookie_consent` gespeichert.

## Doctrine

Setze `storage: doctrine` oder `storage: both` und lege die Tabelle an:

```sql
CREATE TABLE cookie_consent (
  id VARCHAR(64) PRIMARY KEY,
  preferences JSON NOT NULL,
  policy_version VARCHAR(32) NOT NULL,
  decided_at DATETIME NULL
);
```

Die ID liegt im Cookie `cookie_consent_id` (konfigurierbar über `identifier_cookie`).

## Combined

`storage: both` schreibt in Cookie und Datenbank; beim Lesen wird zuerst das Cookie genutzt.
