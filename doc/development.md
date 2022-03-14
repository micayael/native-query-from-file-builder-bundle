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

- [Introduction](README.md)
- [Define your queries](doc/defining_queries.md)
- [Use your queries](doc/using_queries.md)
- [Examples](doc/examples.md)
- [Extending the bundle](doc/using_queries.md) - pending
- [Development](doc/development.md)
