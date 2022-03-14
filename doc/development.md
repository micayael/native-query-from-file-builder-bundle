Development
===========

Install dependencies
--------------------

~~~
composer install
~~~

Testing
-------

~~~
vendor/bin/phpunit
~~~

Code Review
-----------

* phpstan levels: https://phpstan.org/user-guide/rule-levels

~~~
vendor/bin/phpstan analyse src tests --level LEVEL
~~~

* phpmd: https://phpmd.org/download/index.html

~~~
vendor/bin/phpmd ./ text .phpmd-ruleset.xml --exclude var,vendor
~~~

Full Documentation and examples
-------------------------------

- [Introduction](https://github.com/micayael/native-query-from-file-builder-bundle/blob/master/README.md)
- [Define your queries](https://github.com/micayael/native-query-from-file-builder-bundle/blob/master/doc/defining_queries.md)
- [Use your queries](https://github.com/micayael/native-query-from-file-builder-bundle/blob/master/doc/using_queries.md)
- [Examples](https://github.com/micayael/native-query-from-file-builder-bundle/blob/master/doc/examples.md)
- [Extending the bundle](https://github.com/micayael/native-query-from-file-builder-bundle/blob/master/doc/using_queries.md) - pending
- [Development](https://github.com/micayael/native-query-from-file-builder-bundle/blob/master/doc/development.md)
