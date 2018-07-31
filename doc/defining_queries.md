NativeQueryFromFileBuilderBundle
================================

Define your queries
-------------------

Write your queries into files stored in your "%sql_queries_dir%" folder.

- products.yaml

```yaml
clients: |
    SELECT *
    FROM clients

client_by_slug: |
    SELECT *
    FROM clients
    WHERE slug = :slug

clients_optional_filters:
    base: |
        SELECT *
        FROM clients c
        @[clients_optional_filters.params]

    params:
        - firstname = :firstname
        - lastname = :lastname

clients_optional_filters_and_where_included:
    base: |
        SELECT *
        FROM clients c
        WHERE YEAR(birthday) > :year
        @[clients_optional_filters.params]

    params:
        - firstname = :firstname
        - lastname = :lastname

clients_required_key:
    base: |
        SELECT
            c.id,
            @{clients_required_key.extra_columns}
        FROM clients c
          @[clients_required_key.params]

    extra_columns: |
        c.firstname as name,
        c.lastname

    params:
        - c.firstname = :firstname
        - c.lastname = :lastname

clients_required_keys:
    base: |
        SELECT
            c.id,
            @{clients_required_keys.columns}
            @{clients_required_keys.extra_columns}
        FROM clients c
          @[clients_required_keys.params]

    columns: |
        c.firstname as name,
        c.lastname,

    extra_columns: |
        YEAR(c.birthday) as year

    params:
        - c.firstname = :firstname
        - c.lastname = :lastname

clients_special_filters:
    base: |
        SELECT
            *
        FROM clients c
          @[clients_special_filters.params]

    params:
        - in: firstname IN(:firstnames)

clients_any_subquery:
    base: |
        SELECT
            c.firstname,
            c.lastname
        FROM clients c
        WHERE c.sold > ANY(@{clients_any_subquery.most_selled_products})
            @[clients_any_subquery.query_params]
    most_selled_products: |
        SELECT s.amount
        FROM sale s
        @[clients_any_subquery.subquery_params]
        ORDER BY s.amount DESC
        LIMIT 10
    query_params:
        - c.firstname = :firstname
    subquery_params:
        - s.date > :date

```

1. To use an specific query you can use an special dot notation:

    - clients:client_by_slug: query client_by_slug within clients.yaml file
    - clients:clients_required_key.base: query clients_required_key.base at clients.yaml file

2. Query params direcly within the SQL are required like the :slug filter at **clients:client_by_slug**

3. To reuse a snnipet SQL you can use `@{key_to.your_snnipet}` 
like in **clients:clients_required_key.base** SQL example

4. To reference an array of optional filters you can use `@[key_to.your_filters]` 
like **clients:clients_optional_filters.base** SQL example

5. The name of the key to point a query or sub keys like *base* or *filters* are completely irrelevant. Just 
follow your own standard

6. Special filters like in(:array) should be specified like this:

```yaml
    params:
        - in: firstname IN(:firstnames)
```