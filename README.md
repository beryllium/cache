Beryllium\Cache
===============

This library provides an implementation of PSR-16 "Simple Cache", with some added functionality.

Also included are several helper cache clients (APCClient, MemcachedClient, FilecacheClient) that may be of some assistance.

Basic Usage
-----------

1. Instantiate a cache client
1. Instantiate Beryllium\Cache with the cache client in the constructor
1. If desired, set a custom TTL or custom prefix

```php
$client = new Beryllium\Cache\Client\FilecacheClient(__DIR__ . '/cache/');
$cache  = new Beryllium\Cache\Cache($client);

// One Hour Time-To-Live
$cache->setTtl(3600);

// Prefix Filenames with extra information, such as 'www-'
$cache->setPrefix('www-');
```

Now you can get/set items in the cache:

```php
$cache->set('latest-news-items', $newsArray);

$cache->get('latest-news-items', []);
```

Extra Functionality
-------------------

Beryllium\Cache allows you to set a default TTL that gets passed into the caching client, as well as a key prefix that gets attached to each key. This prefix can be helpful for namespacing, for example.

In the above example, the file created on-disk for the cache would be named `%current_dir%/cache/www-latest-news-items_file.cache`.