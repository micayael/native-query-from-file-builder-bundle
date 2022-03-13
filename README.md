NativeQueryFromFileBuilderBundle
================================

![symfony54](https://github.com/micayael/native-query-from-file-builder-bundle/actions/workflows/symfony54.yml/badge.svg)
![symfony60](https://github.com/micayael/native-query-from-file-builder-bundle/actions/workflows/symfony6.yml/badge.svg)
![[Scrutinizer Quality Score](https://scrutinizer-ci.com/g/micayael/native-query-from-file-builder-bundle/badges/quality-score.png)](https://scrutinizer-ci.com/g/micayael/native-query-from-file-builder-bundle/)
![StyleCI](https://github.styleci.io/repos/142354406/shield?style=flat)](https://github.styleci.io/repos/142354406/shield?style=flat)
![![Packagist](https://img.shields.io/packagist/v/micayael/native-query-from-file-builder-bundle.svg)](https://packagist.org/packages/micayael/native-query-from-file-builder-bundle)
![License](https://img.shields.io/packagist/l/micayael/native-query-from-file-builder-bundle.svg)
![![Latest Stable Version](https://poser.pugx.org/micayael/native-query-from-file-builder-bundle/v/stable)](https://packagist.org/packages/micayael/native-query-from-file-builder-bundle)
![![Total Downloads](https://poser.pugx.org/micayael/native-query-from-file-builder-bundle/downloads)](https://packagist.org/packages/micayael/native-query-from-file-builder-bundle)
![![PHP from Packagist](https://img.shields.io/packagist/php-v/micayael/native-query-from-file-builder-bundle.svg)](https://packagist.org/packages/micayael/native-query-from-file-builder-bundle)

This bundle let you write your SQL SELECT sentences into yaml files 
for better organization and then execute them within the application

Installation
------------

### Applications that use Symfony Flex

Open a command console, enter your project directory and execute:

* Symfony <5.4

```console
$ composer require micayael/native-query-from-file-builder-bundle
```

* Symfony >=5.4

```console
$ composer require micayael/native-query-from-file-builder-bundle:~2.0
```

### Applications that don't use Symfony Flex

#### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

* Symfony <5.4

```console
$ composer require micayael/native-query-from-file-builder-bundle
```

* Symfony >=5.4

```console
$ composer require micayael/native-query-from-file-builder-bundle:~2.0
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

#### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Micayael\NativeQueryFromFileBuilderBundle\NativeQueryFromFileBuilderBundle(),
        );

        // ...
    }

    // ...
}
```

Configuration
-------------

### Applications that use Symfony Flex

```yaml
native_query_from_file_builder:
    sql_queries_dir: '%kernel.project_dir%/config/app/queries' # optional (default: '%kernel.project_dir%/config/app/queries')
    default_connection: default # see your doctrine.yaml to select the connection you want to use by default - optional default: 'default')
    file_extension: yml # yaml file extension - optional (default: yaml)
    cache_sql: true # caches sql statements to avoid processing yaml files in each request. Recommended for production - optional (default: true)
```

* Symfony 5.4

It is possible to define different configurations for different environments by replicating the file inside the folder corresponding to the environment or with a syntax like the following:

```yaml
native_query_from_file_builder:
  default_connection: secondary_connection
  cache_sql: true

when@dev:
  native_query_from_file_builder:
    cache_sql: false
```

### Applications that don't use Symfony Flex

Add your configuration in app/config/config.yml

Full Documentation and examples
-------------------------------

- [Define your queries](doc/defining_queries.md)
- [Use your queries](doc/using_queries.md)
- [Extending the bundle](doc/using_queries.md)
