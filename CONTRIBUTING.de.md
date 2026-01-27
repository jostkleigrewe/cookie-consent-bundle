# Contributing

Danke, dass du das Cookie Consent Bundle verbessern möchtest! 🎉

**[English Version](CONTRIBUTING.md)**

## Schnellstart

```bash
composer install
composer ci
```

## Entwicklungsablauf

1. Feature-Branch erstellen
2. Änderungen vornehmen und Tests ergänzen (falls sinnvoll)
3. `composer ci` ausführen
4. Pull Request erstellen

## Code-Stil

- PHP: PSR-12 (4 Leerzeichen)
- Twig: snake_case Dateinamen (z. B. `modal.html.twig`)
- CSS: bestehende Component-Namen in `assets/` beibehalten
- Zweisprachige Inline-Kommentare bei Bedarf:
  - `DE: ...`
  - `EN: ...`

## Tests

- PHPUnit ist der Standard
- Tests in `tests/` ablegen, `*Test.php` verwenden
- Tests nahe am jeweiligen Namespace halten

## Dokumentation

- Dokumentation bei neuen Features oder Konfig-Optionen anpassen
- Kurze, klare Beispiele bevorzugen
- Deutsche und englische Doku synchron halten

## Pull Requests

Bitte enthalten:

- Zusammenfassung der Änderungen
- Schritte zur Prüfung (Commands oder manuell)
- Konfig-Änderungen (falls vorhanden)
- Hinweise zu UI-Verhalten (wenn Templates/Modal betroffen sind)

## Fragen

Bitte Issue oder Discussion auf GitHub eröffnen.
