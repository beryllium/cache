Beryllium\Cache
===============

This library provides an implementation of PSR-16 "Simple Cache",
with some added functionality.

Also included are several helper cache clients (ApcuClient,
FilecacheClient, MemcachedClient, MemoryClient) that may be of some
assistance.

PSR-16 "Common Interface for Caching Libraries" can be found here:
https://www.php-fig.org/psr/psr-16/

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

Beryllium\Cache allows you to set a default TTL that gets passed into
the caching client, as well as a key prefix that gets attached to each
key. This prefix can be helpful for namespacing, for example.

In the above example, the file created on-disk for the cache would be
named `%current_dir%/cache/www-latest-news-items_file.cache`.

Cache Client Classes
--------------------

### ApcuClient

APCu (Alternate PHP Cache - user) is a way of using the PHP memory stack
as a cross-process caching system. Items added to the stack can be
accessed by other processes that are using the same stack.

I'm not sure if that entirely made sense, but regardless, it can be a
very powerful and fast caching system.

Requires the APCu extension to be installed and enabled. Note that the
extension is disabled by default in CLI mode.

Configuration:

```php
$client = new Beryllium\Cache\Client\ApcuClient();
$cache  = new Beryllium\Cache\Cache($client);
$cache->setPrefix('apcu-cache:');
```

Because this extension is disabled on the CLI, to run the unit tests for
this client you have to set an INI value on the command line:

`php -d apc.enable_cli=1 vendor/bin/phpunit`

### FilecacheClient

Filesystems are a great way to cache certain types of data. I don't know
what those types are, but I'm quite sure the statement is true.

Configuration:

```php
$path   = __DIR__ . '/../../somewhere/over/the/rainbow';
$client = new Beryllium\Cache\Client\FilecacheClient($path);
$cache  = new Beryllium\Cache\Cache($client);
$cache->setPrefix('what-a-wonderful-world-');
```

Most of the reason for the existence of a "prefix" feature in this lib
is wrapped up in the original conception of the FilecacheClient. :)

If the path doesn't exist, that might result in an error state - but the
lib will make an attempt to create it & ensure that it's writeable.

Because this client uses PHP's serialize/unserialize functionality, it
should be considered quite a bit unsafe. Any process that has access to
modify the cache files could potentially hijack the PHP process when
`unserialize` is called. At least, several documented vulnerabilities in
other projects seem to be traceable back to unsafe unserialization.

Help would be appreciated in making things a bit safer in that regard.

### MemcachedClient

Memcache is a powerful library for caching. Some of that power has been
"hidden" in this client implementation, but thanks to PSR-16 you should
be able to find a different implementation that has that power if you
need it. Or, if you see a power feature you need and feel it can be
added to this client, PRs are welcome :)

Configuration:

```php
$memcached = new \Memcached();
$verifier  = new \Beryllium\Cache\Client\ServerVerifier\MemcacheServerVerifier();
$client    = new Beryllium\Cache\Client\MemcachedClient(
    $memcached,
    $verifier
);

$client->addServer('localhost', 11211);
$cache = new Beryllium\Cache\Cache($client);
```

The "verifier" attempts to ensure that the server is online before it
tries to interact with it. This behaviour is a holdover from a previous
implementation & requirement. Perhaps it can be eliminated?

Adding servers can be done on the Memcached class itself - if you have
multiple servers, this would actually be the better way to go.

Both of the parameters to MemcachedClient's constructor are optional. If
you just need a basic Memcache configuration, you could do this:

```php
$client = new Beryllium\Cache\Client\MemcachedClient();
$client->addServer('localhost', 11211);
$cache = new Beryllium\Cache\Cache($client);
```

Then, the Memcached object and the server verifier object will be
instantiated inside the constructor.

### MemoryClient

This client stores cached items in an array inside itself. Mostly useful
for tests, but could have some use as a way of accelerating processing
within individual requests.

Configuration:

```php
$client = new Beryllium\Cache\Client\MemoryClient();
$cache  = new Beryllium\Cache\Cache($client);
```