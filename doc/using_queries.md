NativeQueryFromFileBuilderBundle
================================

Use your queries
----------------

You can use an special service injecting **NativeQueryBuilderInterface $nativeQueryBuilder** 
on your constructor repository or on your action controller declaration.

This service has two method you can use:

- `$nativeQueryBuilder->findOneFromSqlKey(string $key, array $params = [], ResultSetMappingBuilder $rsm = null)`

    - **$key:** the query key you want to use, for example *products:product_by_slug*
    - **$params:** an array of params to set it on the query
    - **$rsm:** a ResultSetMappingBuilder object to hidrate you ResultSet into an Entity. https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/reference/native-sql.html#the-resultsetmapping

    This could return:

    1. An associative array 
    2. An entity object if you pass a $rsm object
    3. null when no results

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
