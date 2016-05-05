# Cloud Insight PHP SDK

## Installation
To install this package you will need:

   - PHP 5.5+
   - Enable sockets, mbstring

You must then modify your `composer.json` file and run `composer update` to include the latest version of the package in your project.

```json
    "require": {
      "cloudinsight/cloudinsight-sdk": "~0.0.1"
    }
```

Or you can run the `composer require` command from your terminal.

    $ composer require cloudinsight/cloudinsight-sdk

Make sure your app `require 'vendor/autoload.php'`

## Quick Start Guide

```php
  use CloudInsight\Statsd;

  $statsd = new Statsd;

  #Increment a counter.
  $statsd->increment('page.views');
  
  #Record a gauge 100 of replies
  $statsd->gauge('blogs.replies', 100);

  #Record a gauge 50% of the time.
  $statsd.gauge('users.online', 100, ['users.cloudinsight'], 0.5);
```

document see: [http://docs-ci.oneapm.com/api/php.html](http://docs-ci.oneapm.com/api)
