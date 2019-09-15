Beryllium\Cache 2.0
===================

This library provides an implementation of PSR-16 "Simple Cache",
with some added functionality.

Several cache clients are included:

* ApcuClient
* FilecacheClient
* MemcachedClient
* MemoryClient

As well as some wrappers that can provide additional functionality:

* **IgnoreThrowablesWrapper**: Intercepts all `Throwable`s emanating
  from its wrapped cache client and silently suppresses them.
* **CascadeWrapper**: Allows multiple cache clients to work in unison
  in order to deliver hyper-local cache response times, gradually moving
  outward to less-local/slower caches.

Documentation for the PSR-16 "Common Interface for Caching Libraries"
can be found here: https://www.php-fig.org/psr/psr-16/

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

**Default TTL:**

Beryllium\Cache allows you to set a default TTL that gets passed into
the caching client, as well as a key prefix that gets attached to each
key. This prefix can be helpful for namespacing, for example.

In the above example, the file created on-disk for the cache would be
named `%current_dir%/cache/www-latest-news-items_file.cache`.

**Prefix:**

Beryllium\Cache supports 'prefixing' keys with a specific string, which
may be useful in some caching systems. For example, it could be useful
in the FilecacheClient to ensure that you can always identify cached
files.

Cache Client Classes
--------------------

### ApcuClient

APCu (Alternate PHP Cache - user) is a way of using the shared PHP
memory stack as a cross-process caching system. Items added to the stack
can be accessed by other processes that are using the same stack.

I'm not sure if that entirely made sense, but regardless, it can be a
very powerful and fast caching system.

Requires the APCu extension to be installed and enabled. Note that the
extension is disabled by default in CLI mode.

**Configuration:**

```php
$client = new Beryllium\Cache\Client\ApcuClient();
$cache  = new Beryllium\Cache\Cache($client);
$cache->setPrefix('apcu-cache:');
```

Because this extension is disabled on the CLI, to run the unit tests for
this client you have to set an INI value on the command line:

`php -d apc.enable_cli=1 vendor/bin/phpunit`

If you attempt to load this extension on a system where APCu is not
installed, the class will generate fatal errors due to methods not being
found. If you need it to fail silently, e.g., if you're using a Cascade
wrapper, you can wrap it in the `IgnoreThrowablesWrapper` to silence
the fatals. Or install APCu. Whichever works for you.

### FilecacheClient

Filesystems are a great way to cache certain types of data. I don't know
what those types are, but I'm quite sure the statement is true.

**Configuration:**

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

I've broken out the serialize/unserialize functionality into methods, so
if you want, you could extend the FilecacheClient and override the
serialization mechanism.

### MemcachedClient

Memcache is a powerful library for caching. Some of that power has been
"hidden" in this client implementation, but thanks to PSR-16 you can
expect the cache to behave in a predictable way.

**Quick Configuration:**

```php
$client = new Beryllium\Cache\Client\MemcachedClient();
$client->addServer('localhost', 11211);
$cache = new Beryllium\Cache\Cache($client);
```

The quick approach instantiates the `Memcached` class directly inside
the constructor.

**Injecting the Dependency:**

If you would prefer, you can also inject the `Memcached` class directly:

```php
$memcached = new \Memcached();
$memcached->addServer('localhost', 11211);

$client = new Beryllium\Cache\Client\MemcachedClient($memcached);
$cache  = new Beryllium\Cache\Cache($client);
```

If you have multiple servers, injecting the object would likely be the
better way to go.

**Server Verifier:**

`MemcachedClient` also comes with a "server verifier" that attempts to
ensure that the server is online before it tries to interact with it.

The "verifier" is completely optional, and can be a bottleneck. This
behaviour is a holdover from the previous implementation. Perhaps it can
be eliminated?

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

### MemoryClient

This client stores cached items in an array inside itself. Mostly useful
for tests, but could have some use as a way of accelerating processing
within individual requests.

**Configuration:**

```php
$client = new Beryllium\Cache\Client\MemoryClient();
$cache  = new Beryllium\Cache\Cache($client);
```

Cache Wrapper Classes
---------------------

### IgnoreThrowablesWrapper

This wrapper encapsulates a noisy Client class and ensures that no
exceptions or errors escape it. Generally this causes the client to fail
silently, which can be useful when you want your cache configuration to
not blow up on a function-not-found error.

Or a path-not-found/path-not-writable error.

**Configuration:**

```php
$client = new Beryllium\Cache\Client\ApcuClient();
$ignore = new Beryllium\Cache\Wrapper\IgnoreThrowablesWrapper($client);
$cache  = new Beryllium\Cache\Cache($ignore);
$cache->setPrefix('apcu-cache:');
```

### CascadeWrapper

The Cascade wrapper lets you stack cache clients together so that one
call to `->get()` will first check the closest/fastest cache
(MemoryClient or ApcuClient), then start looking farther afield to find
the requested data (Memcache, Filecache).

Calling `->set()` will relay the `->set()` to each client in sequence.

If `->enableBackfill()` is called, a successful call to `->get()` will
also `set` all the cache targets, ensuring the full cache is populated.

**Configuration:**

```php
$client1 = new Beryllium\Cache\Client\MemoryClient();
$client2 = new Beryllium\Cache\Client\ApcuClient();
$client3 = new Beryllium\Cache\Client\MemcachedClient();
$client4 = new Beryllium\Cache\Client\FilecacheClient('/mnt/tmp/cache');
$cascade = new Beryllium\Cache\Wrapper\CascadeWrapper(
    $client1,
    $client2,
    $client3,
    $client4
);

$client3->addServer('localhost', 11211);

$cache = new Beryllium\Cache\Cache($cascade);
```
