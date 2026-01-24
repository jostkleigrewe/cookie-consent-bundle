# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]


## [0.2.0] - 2026-01-24

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

[Unreleased]: https://github.com/jostkleigrewe/cookie-consent-bundle/compare/v0.1.0...HEAD
[0.1.0]: https://github.com/jostkleigrewe/cookie-consent-bundle/releases/tag/v0.1.0
[0.2.0]: https://github.com/jostkleigrewe/cookie-consent-bundle/releases/tag/v0.2.0
