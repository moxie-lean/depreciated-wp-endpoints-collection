# WP Endpoint Collection

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

## Features

- Avoid to query post that are on draft, private or any other
  state that is not publish.
- Avoid to query post with passwords protections.

By default returns only a collection of posts but any post type can be
specifed to be returned or a collection of multiple post types can be
specifed as well.

## Usage.

The default URL is:

````
/wp-json/leean/v1/collection
```

By default the collection is the list of all the posts, you can use most
of the WP_Query params in order to update your results, for example by
default uses setting that specifies the number of reading post on the
settings page.

## Examples of usage

Get only the latest 3 posts.

````
wp-json/leean/v1/collection?posts_per_page=3
```

How about get the latest 3 posts that belongs to the author with the ID
1.

````
wp-json/leean/v1/collection?posts_per_page=3&author=1
```

Or just get the posts that belong to the author with the ID 1.

````
wp-json/leean/v1/collection?author=1
```

Get the all the posts ordered by ID from lowest to highest values.

````
wp-json/leean/v1/collection?orderby=ID&order=ASC
```

Get all the posts and pages published on the site.

````
wp-json/leean/v1/collection?post_type[]=post&post_type[]=page
```
