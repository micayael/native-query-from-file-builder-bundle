NativeQueryFromFileBuilderBundle
================================

Use your queries
----------------

You can use an special service injecting **NativeQueryBuilderInterface $nativeQueryBuilder** 
on your constructor repository or on your action controller declaration.

This service has three methods you can use:

1. `public function findFromSqlKey(string $key, array $params = [], ?string $orderBy = null, string $connectionName = null, ResultSetMappingBuilder $rsm = null): array;`

  - **$key:** the query key you want to use, for example *products:product_by_slug*
  - **$params:** an array of params to set it on the query
  - **$orderBy:** a string that represents your orderby clause, for example *c.date desc, c.id asc*
  - **$connectionName:** a string with the name of your connection database name. see your doctrine.yaml config file and the *native_query_from_file_builder.default_connection* bundle config 
  - **$rsm:** a ResultSetMappingBuilder object to hidrate your ResultSet into an Entity or DTO. https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/reference/native-sql.html#the-resultsetmapping

  This could return:

  - An associative array
  - An array of entity objects if you pass a $rsm object
  - [] when no results

---

2. `public function findOneFromSqlKey(string $key, array $params = [], string $connectionName = null, ResultSetMappingBuilder $rsm = null);`

      - **$key:** the query key you want to use, for example *products:product_by_slug*
      - **$params:** an array of params to set it on the query
      - **$connectionName:** a string with the name of your connection database name. see your doctrine.yaml config file and the *native_query_from_file_builder.default_connection* bundle config
      - **$rsm:** a ResultSetMappingBuilder object to hidrate you ResultSet into an Entity. https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/reference/native-sql.html#the-resultsetmapping

      This could return:

      1. An associative array 
      2. An entity object if you pass a $rsm object
      3. null when no results

---

3. `public function findScalarFromSqlKey(string $key, array $params = [], string $connectionName = null);`

   - **$key:** the query key you want to use, for example *products:product_by_slug*
   - **$params:** an array of params to set it on the query
   - **$connectionName:** a string with the name of your connection database name. see your doctrine.yaml config file and the *native_query_from_file_builder.default_connection* bundle config

   This could return:

   1. A scalar single data as string, integer, etc
   2. null when no results

## Example

- src/Controller/DefaultController.php

```php
/**
 * @Route("/product/{id}", name="product")
 */
public function productAction(Request $request, NativeQueryBuilderInterface $nativeQueryBuilder, $id)
{
    $product = $nativeQueryBuilder->findOneFromSqlKey('products:product_by_slug', ['id' => $id]);
    
    if(!$product){
        throw new NotFoundHttpException();
    }
    
    return $this->render('default/index.html.twig', [
        'products' => $product
    ]);
}
```

- SQL: `SELECT * FROM products WHERE id = '1';`

- Example data:

```php
array:4 [
  "id" => "1"
  "name" => "product 1"
  "price" => "1000"
  "detail" => "this is a test"
]
```
Full Documentation and examples
-------------------------------

- [Introduction](https://github.com/micayael/native-query-from-file-builder-bundle/blob/master/README.md)
- [Define your queries](https://github.com/micayael/native-query-from-file-builder-bundle/blob/master/doc/defining_queries.md)
- [Use your queries](https://github.com/micayael/native-query-from-file-builder-bundle/blob/master/doc/using_queries.md)
- [Examples](https://github.com/micayael/native-query-from-file-builder-bundle/blob/master/doc/examples.md)
- [Extending the bundle](https://github.com/micayael/native-query-from-file-builder-bundle/blob/master/doc/using_queries.md) - pending
- [Development](https://github.com/micayael/native-query-from-file-builder-bundle/blob/master/doc/development.md)
