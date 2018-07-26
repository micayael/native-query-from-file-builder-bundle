NativeQueryFromFileBuilderBundle
================================

[![Build Status](https://api.travis-ci.org/micayael/native-query-from-file-builder-bundle.svg)](https://travis-ci.org/micayael/native-query-from-file-builder-bundle)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/micayael/native-query-from-file-builder-bundle/badges/quality-score.png)](https://scrutinizer-ci.com/g/micayael/native-query-from-file-builder-bundle/)
[![Packagist](https://img.shields.io/packagist/v/micayael/native-query-from-file-builder-bundle.svg)](https://packagist.org/packages/micayael/native-query-from-file-builder-bundle)
![License](https://img.shields.io/packagist/l/micayael/native-query-from-file-builder-bundle.svg)

[![Latest Stable Version](https://poser.pugx.org/micayael/native-query-from-file-builder-bundle/v/stable)](https://packagist.org/packages/micayael/native-query-from-file-builder-bundle)
[![Total Downloads](https://poser.pugx.org/micayael/native-query-from-file-builder-bundle/downloads)](https://packagist.org/packages/micayael/native-query-from-file-builder-bundle)

This bundle let you write your SQL SELECT sentences into yaml files 
for better organization and then execute them within the application

Installation
------------

### Applications that use Symfony Flex

Open a command console, enter your project directory and execute:

```console
$ composer require micayael/native-query-from-file-builder-bundle
```

### Applications that don't use Symfony Flex

#### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require micayael/native-query-from-file-builder-bundle
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

The only configuration is the path of the folder that contains the yaml files

### Applications that use Symfony Flex

Write the folder where you will store your queries in config/native_query_from_file_builder.yaml

```yaml
native_query_from_file_builder:
    sql_queries_dir: '%kernel.project_dir%/config/queries'
```

### Applications that don't use Symfony Flex

Write the folder where you will store your queries in app/config/config.yml

```yaml
native_query_from_file_builder:
    sql_queries_dir: '%kernel.root_dir%/config/queries'
```

Examples
--------

Write your queries into files stored in your "sql_queries_dir" folder.

- products.yaml

```yaml
products: |
    SELECT
        p.id,
        p.name,
        p.price
    FROM products p
```