# Repository Guidelines

## Project Structure & Module Organization
- `src/` holds the Symfony bundle code (attributes, consent services, controllers, event subscribers, Twig helpers).
- `assets/` contains front-end sources (Stimulus controller and CSS) used by AssetMapper/Encore.
- `templates/` provides the default Twig modal template.
- `config/` ships bundle configuration defaults and service wiring.
- `translations/` contains UI strings for the consent modal.
- `docs/` collects supporting documentation; `public/` is for any packaged static assets.

## Build, Test, and Development Commands
- `composer install` installs PHP dependencies for the bundle.
- `composer require --dev phpunit/phpunit` if you are adding tests in a fresh clone.
- `vendor/bin/phpunit` runs the test suite (once `tests/` exists).
- Front-end assets are consumed via AssetMapper or Encore from `assets/`; no build step is required unless you add a bundling pipeline.

## Coding Style & Naming Conventions
- PHP code follows PSR-12 style with 4-space indentation.
- Namespaces align with folders under `src/` (e.g., `Jostkleigrewe\CookieConsentBundle\EventSubscriber`).
- Twig templates use snake_case filenames (e.g., `modal.html.twig`).
- CSS class names follow the bundle’s component naming in `assets/`.
- Use bilingual inline comments when needed, prefixed with `DE:` and `EN:` on separate lines.
- In German docs, use proper umlauts (UTF-8) instead of `ae/oe/ue`.

## Testing Guidelines
- PHPUnit is the intended framework (see `composer.json`).
- Place tests in `tests/` using `*Test.php` class names.
- Keep unit tests close to the corresponding namespace (e.g., consent services, event subscribers).

## Commit & Pull Request Guidelines
- Recent history uses short, informal messages (e.g., `WIP`). There is no enforced convention.
- Prefer descriptive, imperative commit messages (e.g., `Add consent storage adapter`).
- PRs should include a summary, steps to verify (commands or manual checks), and any config changes.
- If UI behavior changes, include a short note on expected modal behavior and affected templates.

## Security & Configuration Tips
- Be careful when changing consent enforcement or stateless path handling; these can affect session behavior.
- Document any new config keys in `docs/` and update the README examples if defaults change.

## Project Intent
- This repository is used to learn and explore new Symfony 8 features for apps and bundles.
