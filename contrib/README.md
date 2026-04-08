# Symfony Flex Recipe

Dieses Verzeichnis enthält das **Symfony Flex Recipe** für das Cookie Consent Bundle.

## Was macht das Recipe?

Bei `composer require jostkleigrewe/cookie-consent-bundle` erstellt Flex automatisch:

| Datei | Zweck |
|-------|-------|
| `config/routes/cookie_consent.yaml` | Registriert Bundle-Routen |
| `config/packages/cookie_consent.yaml` | Beispiel-Konfiguration |
| `assets/controllers.json` | Aktiviert Stimulus Controller |

## Recipe einreichen

### 1. Fork erstellen

```bash
# Repository forken auf GitHub
https://github.com/symfony/recipes-contrib

# Lokal klonen
git clone git@github.com:DEIN-USER/recipes-contrib.git
cd recipes-contrib
```

### 2. Recipe-Dateien kopieren

```bash
# Aus diesem Bundle-Verzeichnis
cp -r contrib/symfony/recipes-contrib/jostkleigrewe .
```

Die Struktur muss exakt so sein:

```
recipes-contrib/
└── jostkleigrewe/
    └── cookie-consent-bundle/
        └── 0.4/
            ├── manifest.json
            ├── config/
            │   ├── packages/
            │   │   └── cookie_consent.yaml
            │   └── routes/
            │       └── cookie_consent.yaml
            └── assets/
                └── controllers.json
```

### 3. Pull Request erstellen

```bash
git checkout -b jostkleigrewe-cookie-consent-bundle-0.4
git add jostkleigrewe/
git commit -m "Add recipe for jostkleigrewe/cookie-consent-bundle 0.4"
git push origin jostkleigrewe-cookie-consent-bundle-0.4
```

Dann PR auf GitHub erstellen: https://github.com/symfony/recipes-contrib/pulls

### 4. PR-Beschreibung

```markdown
## Package

- **Name:** jostkleigrewe/cookie-consent-bundle
- **Version:** 0.4
- **Packagist:** https://packagist.org/packages/jostkleigrewe/cookie-consent-bundle
- **Repository:** https://github.com/jostkleigrewe/cookie-consent-bundle

## Description

GDPR/DSGVO-compliant cookie consent management bundle for Symfony 8.
Includes Google Consent Mode v2 support, Twig components, and Stimulus.js integration.

## Recipe adds

- Routes configuration for consent update endpoint
- Default package configuration with common categories
- Stimulus controller registration for interactive consent modal
```

### 5. Validierung

Das Recipe-Repository hat automatische Validierung. Falls Fehler auftreten:

- `manifest.json` muss valides JSON sein
- Paketname muss auf Packagist existieren
- Version muss mit einem Release-Tag übereinstimmen

## Lokales Testen (ohne veröffentlichtes Recipe)

Bis das Recipe akzeptiert ist, können User manuell installieren:

```bash
# 1. Bundle installieren
composer require jostkleigrewe/cookie-consent-bundle

# 2. Routen manuell erstellen
cat > config/routes/cookie_consent.yaml << 'EOF'
cookie_consent:
    resource:
        path: '@CookieConsentBundle/Controller/'
        namespace: Jostkleigrewe\CookieConsentBundle\Controller
    type: attribute
EOF

# 3. Konfiguration kopieren
cp vendor/jostkleigrewe/cookie-consent-bundle/docs/examples/cookie_consent.yaml config/packages/

# 4. Stimulus Controller aktivieren (assets/controllers.json manuell bearbeiten)
```

## Versions-Mapping

| Bundle Version | Recipe Version |
|----------------|----------------|
| 0.4.x          | 0.4            |
| 0.3.x          | (kein Recipe)  |

Bei neuen Major/Minor-Versionen: Neues Recipe-Verzeichnis erstellen (z.B. `0.5/`).
