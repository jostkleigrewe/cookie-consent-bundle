# Contributing

Thanks for your interest in improving Cookie Consent Bundle! 🎉

**[Deutsche Version](CONTRIBUTING.de.md)**

## Quick start

```bash
composer install
composer ci
```

## Development workflow

1. Create a feature branch
2. Make changes with tests (if applicable)
3. Run `composer ci`
4. Open a pull request

## Code style

- PHP: PSR-12 (4 spaces)
- Twig: snake_case filenames (e.g. `modal.html.twig`)
- CSS: follow existing component naming in `assets/`
- Use bilingual inline comments when needed:
  - `DE: ...`
  - `EN: ...`

## Tests

- PHPUnit is the default framework
- Place tests in `tests/` with `*Test.php` names
- Keep tests close to the corresponding namespace

## Documentation

- Update docs when adding features or config options
- Prefer clear, short examples
- Keep English and German docs in sync

## Pull requests

Please include:

- Summary of changes
- How to verify (commands or manual steps)
- Config changes (if any)
- UI behavior notes (if templates or modal behavior change)

## Questions

Open an issue or start a discussion on GitHub.
