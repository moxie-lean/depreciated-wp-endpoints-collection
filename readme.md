# WP Endpoint Collection

**This package is depreciated. LEAN now uses the WordPress REST API plugins instead**

> Query against a WordPress site and get a JSON response with a
> collection of data associated with your request.

## Getting Started

The easiest way to install this package is by using composer from your terminal:

```bash
composer require moxie-lean/wp-endpoints-collection
```

Or by adding the following lines on your `composer.json` file

```json
"require": {
  "moxie-lean/wp-endpoints-collection": "dev-master"
}
```

This will download the files from the [packagist site](https://packagist.org/packages/moxie-lean/wp-endpoints-collection)
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

By default the collection is the list of posts, you can use most
of the WP_Query params in order to update your results, for example by
default uses setting that specifies the number of reading post on the
settings page.

## Request examples

Get only the latest 3 posts.

```json
wp-json/leean/v1/collection?posts_per_page=3
```

How about get the latest 3 posts that belongs to the author with the ID
1.

```json
wp-json/leean/v1/collection?posts_per_page=3&author=1
```

Or just get the posts that belong to the author with the ID 1.

```json
wp-json/leean/v1/collection?author=1
```

Get the all the posts ordered by ID from lowest to highest values.

```json
wp-json/leean/v1/collection?orderby=ID&order=ASC
```

Get all the posts and pages published on the site.

```json
wp-json/leean/v1/collection?post_type[]=post&post_type[]=page
```

Get the first and second page of the blog section.

```json
wp-json/leean/v1/collection
wp-json/leean/v1/collection?paged=2
```

Get all the posts that has the category ID 2

```json
wp-json/leean/v1/collection?cat=2
```

## Filters

There are filters that can be used on this particular endpoint.

`ln_endpoints_collection_args`. This filter allow you to overwrite the
default arguments used to query inside of the collection so you can
overwrite the default values used on the `WP_Query` before executed.

`ln_endpoints_collection_data`. This filter allow you to overwrite the
data after processing and before is sending it to the client it has 1
parameter that can be used on the filter: 

 - `$data` The original data created by the endpoint, this is an array
   with all the data, for example if you want to return an empty array
if the data has zero items.

 - `$request` An array with the arguments used to create the request.

```php
add_filter('ln_endpoints_collection_data', function( $data ){
  if ( isset( $data['pagination']['items'] ) && $data['pagination']['items'] === 0 ) {
    return [];
  } else {
    return $data;
  }
});
```

`ln_endpoints_collection_item`. Allows you to easily customise the output of each post:

```php
add_filter( 'ln_endpoints_collection_item', function($item, $the_post) {
      if ( $the_post->ID === 1 ) {
        return [
         'message' => 'Nothing here',
        ];
      } else {
        return $item;
      }
    }, 10, 2);;
```

