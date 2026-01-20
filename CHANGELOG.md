# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Initial release
- GDPR-compliant cookie consent modal
- Multiple storage backends (Cookie, Doctrine, Combined)
- Stimulus.js controller with Turbo support
- Twig helpers for consent-aware rendering
- Session enforcement (prevent session cookies without consent)
- Google Consent Mode v2 integration
- Embed components (YouTube, Vimeo, Google Maps, etc.)
- Audit logging for consent actions
- Multiple themes (Tabler, Bootstrap, Plain)
- Controller attributes (`#[ConsentRequired]`, `#[ConsentStateless]`)
- Policy versioning for re-consent on changes

## [0.1.0] - TBD

### Added
- First public release

[Unreleased]: https://github.com/jostkleigrewe/cookie-consent-bundle/compare/v0.1.0...HEAD
[0.1.0]: https://github.com/jostkleigrewe/cookie-consent-bundle/releases/tag/v0.1.0
