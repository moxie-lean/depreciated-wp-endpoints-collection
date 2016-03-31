# WP Endpoint 

> Query against a WordPress site and get a JSON response with a
> collection of data associated with your request.

## Getting Started

The easiest way to install this package is by using composer from your terminal:

```bash
composer require moxie-lean/wp-endpoints-archive
```

Or by adding the following lines on your `composer.json` file

```json
"require": {
  "moxie-lean/wp-endpoints-archive": "dev-master"
}
```

This will download the files from the [packagist site](https://packagist.org/packages/moxie-lean/wp-endpoints-archive)
and set you up with the latest version located on master branch of the repository.

After that you can include the `autoload.php` file in order to
be able to autoload the class during the object creation.

```php
include '/vendor/autoload.php';

\Lean\Endpoints\Collection::init();
```

