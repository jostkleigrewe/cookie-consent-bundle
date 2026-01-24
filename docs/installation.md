# Installation

## Requirements

- PHP 8.2+
- Symfony 7.0+

## Step 1: Install the Bundle

```bash
composer require jostkleigrewe/cookie-consent-bundle
```

## Step 2: Enable the Bundle

If you're not using Symfony Flex, add the bundle to `config/bundles.php`:

```php
return [
    // ...
    Jostkleigrewe\CookieConsentBundle\CookieConsentBundle::class => ['all' => true],
];
```

## Step 3: Create Configuration

Copy the example configuration to your project:

```bash
cp vendor/jostkleigrewe/cookie-consent-bundle/docs/examples/cookie_consent.yaml config/packages/
```

Or create `config/packages/cookie_consent.yaml` manually. See [Configuration](configuration.md) for all options.

## Step 4: Import Routes

Add to `config/routes.yaml`:

```yaml
cookie_consent:
    resource: '@CookieConsentBundle/config/routes.php'
```

## Step 5: Include Assets

Add the CSS to your base template:

```twig
<link rel="stylesheet" href="{{ asset('bundles/cookieconsent/styles/cookie_consent.css') }}">
```

Or import in your asset pipeline.

## Step 6: Add Modal to Template

In your base layout (e.g., `base.html.twig`):

```twig
{{ cookie_consent_modal() }}
```

## Next Steps

- [Configuration Options](configuration.md)
- [Customization](customization.md)
