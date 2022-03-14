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

- [Introduction](/micayael/native-query-from-file-builder-bundle/blob/master/README.md)
- [Define your queries](/micayael/native-query-from-file-builder-bundle/blob/master/doc/defining_queries.md)
- [Use your queries](/micayael/native-query-from-file-builder-bundle/blob/master/doc/using_queries.md)
- [Examples](/micayael/native-query-from-file-builder-bundle/blob/master/doc/examples.md)
- [Extending the bundle](/micayael/native-query-from-file-builder-bundle/blob/master/doc/using_queries.md) - pending
- [Development](/micayael/native-query-from-file-builder-bundle/blob/master/doc/development.md)
