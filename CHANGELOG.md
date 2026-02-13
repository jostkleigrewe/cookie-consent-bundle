# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

### Changed

### Removed

## [0.4.0] - 2026-02-13

### Added
- Showcase layout can now automatically extend the application's `base.html.twig` if available.
- New `@CookieConsent/showcase_base.html.twig` as fallback for the showcase.
- Alias `cookie-consent:cleanup` for `cookie-consent:prune-logs` command.

### Changed
- Showcase controller now correctly pre-fills preferences based on category/vendor `default` and `required` settings.
- Improved Bootstrap and Plain theme category templates for better layout consistency.
- Updated documentation (README and CONTRIBUTING) in both English and German.

## [0.3.0] - 2026-01-26

### Added
- PHPUnit Bridge setup with functional/kernel tests
- PHPStan configuration (level 6) and CI integration
- Composer scripts: `test`, `phpstan`, `ci`
- Modal position variants via `ui.position` (center/top/bottom/corners)
- ORM entities for current consent and audit log (`cookie_consent`, `cookie_consent_log`)
- Doctrine ORM storage adapter and audit log persistence with optional `user_id`
- Log retention config (`logging.retention_days`) and `cookie-consent:prune-logs` command
- Doctrine ORM + Symfony Console as required dependencies
- Optional vendor-level consent per category (new preference schema)
- Twig component classes for embed templates (use `<twig:...>` or `component()`)
- Integration guide (Twig components, attributes, helpers, data attributes)
- Docblock examples for embed Twig components (template usage)

### Changed
- CI workflow runs `composer ci` for validation, tests, and static analysis
- Audit logging persistence now uses Doctrine ORM when available
- Preference payload now uses `{ allowed, vendors }` per category
- Vendor lists now auto-open when a category is enabled and auto-close on disable
- Vendor defaults are applied when enabling a category in the modal
- Tabler vendor entries use a switch layout with reduced emphasis
- Embed placeholders now use category/vendor labels from config
- Embed components accept optional vendor parameters with docs examples
- Embed documentation now uses Twig component tags instead of include paths
- Documentation consolidated: installation content merged into Getting Started and new Integration quick start added
- Documentation refreshed: configuration pages now link to the Integration guide and embed examples consolidated

### Removed
- Vendor list toggle button and `ui.vendors` configuration options

## [0.2.0] - 2026-01-25

### Added
- Examples for Bundle-Configuration
- New Confirmation Values ui.variant,ui.theme,ui.density 

### Change
- Structure-Change for Layout and Templates


## [0.1.0] - 2026-01-23

### Added
- GDPR-compliant cookie consent modal with Stimulus.js controller
- Multiple storage backends: Cookie, Doctrine, or Combined
- Twig helpers: `cookie_consent_has()`, `cookie_consent_modal()`, etc.
- Session enforcement (prevent session cookies without consent)
- Google Consent Mode v2 integration
- Embed components (YouTube, Vimeo, Google Maps, Spotify, Twitter/X, Instagram, TikTok, etc.)
- Audit logging for consent actions
- Multiple themes: Tabler (light/dark/compact), Bootstrap, Plain
- Controller attributes: `#[ConsentRequired]`, `#[ConsentStateless]`
- Policy versioning for automatic re-consent on category changes
- Lazy script loading with `data-consent-category` attribute
- Settings button component to re-open modal
- Browser events: `cookie-consent:changed`, `cookie-consent:open`

[Unreleased]: https://github.com/jostkleigrewe/cookie-consent-bundle/compare/v0.4.0...HEAD
[0.4.0]: https://github.com/jostkleigrewe/cookie-consent-bundle/compare/v0.3.0...v0.4.0
[0.3.0]: https://github.com/jostkleigrewe/cookie-consent-bundle/compare/v0.2.0...v0.3.0
[0.2.0]: https://github.com/jostkleigrewe/cookie-consent-bundle/compare/v0.1.0...v0.2.0
[0.1.0]: https://github.com/jostkleigrewe/cookie-consent-bundle/releases/tag/v0.1.0
