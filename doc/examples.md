NativeQueryFromFileBuilderBundle
================================

Examples: clients.yaml
----------------------

### Simple SQL

**SQL TEMPLATE:**
~~~sql
clients: |
    SELECT *
    FROM clients
~~~

**PHP:**
~~~php
$sql = $this->helper->getSqlFromYamlKey('clients:clients');
~~~

~~~sql
SELECT * FROM clients
~~~

---

### Required Params

**SQL TEMPLATE:**
~~~sql
client_by_slug: |
    SELECT *
    FROM clients
    WHERE slug = :slug
~~~

**PHP:**
~~~php
$params = [
    'slug' => 'jhon-doe',
];

$sql = $this->helper->getSqlFromYamlKey('clients:client_by_slug', $params);
~~~

**SQL:**
~~~sql
SELECT * FROM clients WHERE slug = :slug
~~~

---

### Snippets (optionals & required): without filters

**SQL TEMPLATE:**
~~~sql
clients_optional_filters:
    base: |
        SELECT *
        FROM clients c
        @[clients_optional_filters.params]
        ORDER BY c.id DESC
        
    params:
        - firstname = :firstname
        - lastname = :lastname
~~~

**PHP:**
~~~php
$params = [];

$sql = $this->helper->getSqlFromYamlKey('clients:clients_optional_filters.base', $params);
~~~

**SQL:**
~~~sql
SELECT * FROM clients c ORDER BY c.id DESC
~~~

---

### Snippets (optionals & required): with one optional filter

**SQL TEMPLATE:**
~~~sql
clients_optional_filters:
    base: |
        SELECT *
        FROM clients c
        @[clients_optional_filters.params]
        ORDER BY c.id DESC
        
    params:
        - firstname = :firstname
        - lastname = :lastname
~~~

**PHP:**
~~~php
$params = [
    'firstname' => 'Jhon',
];

$sql = $this->helper->getSqlFromYamlKey('clients:clients_optional_filters.base', $params);
~~~

**SQL:**
~~~sql
SELECT * FROM clients c WHERE (firstname = :firstname) ORDER BY c.id DESC
~~~

---

### Snippets (optionals & required): with more than one optional filter

**SQL TEMPLATE:**
~~~sql
clients_optional_filters:
    base: |
        SELECT *
        FROM clients c
        @[clients_optional_filters.params]
        ORDER BY c.id DESC
        
    params:
        - firstname = :firstname
        - lastname = :lastname
~~~

**PHP:**
~~~php
$params = [
    'firstname' => 'Jhon',
    'lastname' => 'Doe',
];

$sql = $this->helper->getSqlFromYamlKey('clients:clients_optional_filters.base', $params);
~~~

**SQL:**
~~~sql
SELECT * FROM clients c WHERE (firstname = :firstname) AND (lastname = :lastname) ORDER BY c.id DESC
~~~

---

### Snippets (optionals & required): optional filters and WHERE clause included

**SQL TEMPLATE:**
~~~sql
clients_optional_filters_and_where_included:
    base: |
        SELECT *
        FROM clients c
        WHERE YEAR(birthday) > :year
        @[clients_optional_filters.params]

    params:
        - firstname = :firstname
        - lastname = :lastname
~~~

**PHP:**
~~~php
$params = [
    'year' => 1983,
    'firstname' => 'Jhon',
    'lastname' => 'Doe',
];

$sql = $this->helper->getSqlFromYamlKey('clients:clients_optional_filters_and_where_included.base', $params);
~~~

**SQL:**
~~~sql
SELECT * FROM clients c WHERE YEAR(birthday) > :year AND (firstname = :firstname) AND (lastname = :lastname)
~~~

---

### Snippets (optionals & required): with required keys and no filters

**SQL TEMPLATE:**
~~~sql
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
~~~

**PHP:**
~~~php
$sql = $this->helper->getSqlFromYamlKey('clients:clients_required_key.base');
~~~

**SQL:**
~~~sql
SELECT c.id, c.firstname as name, c.lastname FROM clients c
~~~

---

### Snippets (optionals & required): with required key and filters

**SQL TEMPLATE:**
~~~sql
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
~~~

**PHP:**
~~~php
$params = [
    'firstname' => 'Jhon',
];

$sql = $this->helper->getSqlFromYamlKey('clients:clients_required_key.base', $params);
~~~

**SQL:**
~~~sql
SELECT c.id, c.firstname as name, c.lastname FROM clients c WHERE (c.firstname = :firstname)
~~~

---

### Snippets (optionals & required): with required keys and filters

**SQL TEMPLATE:**
~~~sql
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
~~~

**PHP:**
~~~php
$params = [
    'firstname' => 'Jhon',
    'lastname' => 'Doe',
];

$sql = $this->helper->getSqlFromYamlKey('clients:clients_required_keys.base', $params);
~~~

**SQL:**
~~~sql
SELECT c.id, c.firstname as name, c.lastname, YEAR(c.birthday) as year FROM clients c WHERE (c.firstname = :firstname) AND (c.lastname = :lastname)
~~~

---

### Special filters: in()

**SQL TEMPLATE:**
~~~sql
clients_special_filters:
    base: |
        SELECT
            *
        FROM clients c
            @[clients_special_filters.params]

    params:
        - in: firstname IN(:firstnames)
~~~

**PHP:**
~~~php
$params = [
    'firstnames' => ['Jhon', 'Mary', 'Steven'],
];

$sql = $this->helper->getSqlFromYamlKey('clients:clients_special_filters.base', $params);
~~~

**SQL:**
~~~sql
SELECT * FROM clients c WHERE (firstname IN(:firstnames_0,:firstnames_1,:firstnames_2))
~~~

---

### Subqueries - Multipart Query: With any subquery (postgres version)

**SQL TEMPLATE:**
~~~sql
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
~~~

**PHP:**
~~~php
$params = [
    'firstname' => 'Jhon',
    'date' => '2018-01-01',
];

$sql = $this->helper->getSqlFromYamlKey('clients:clients_any_subquery.base', $params);
~~~

**SQL:**
~~~sql
SELECT c.firstname, c.lastname FROM clients c WHERE c.sold > ANY(SELECT s.amount FROM sale s WHERE (s.date > :date) ORDER BY s.amount DESC LIMIT 10) AND (c.firstname = :firstname)
~~~

---

### Pagination

**SQL TEMPLATE:**
~~~sql
clients_pagination:
    base: |
        SELECT *
        @{clients_pagination.from}
        @[clients_pagination.params]
        ORDER BY s.date DESC

    count: |
        SELECT count(1)
        @{clients_pagination.from}
        @[clients_pagination.params]

    from:
        FROM clients c
        JOIN sales s on c.id = s.client_id

    params:
        - c.date >= :min_date
        - like: c.firstname like :name
~~~

**PHP:**
~~~php
$params = [
    'name' => 'Jhon',
    'min_date' => '2018-01-01',
];

$sqlCount = $this->helper->getSqlFromYamlKey('clients:clients_pagination.count', $params);
$sqlList = $this->helper->getSqlFromYamlKey('clients:clients_pagination.base', $params);
~~~

**SQL:**
~~~sql
SELECT count(1) FROM clients c JOIN sales s on c.id = s.client_id WHERE (c.date >= :min_date) AND (c.firstname like :name)
SELECT * FROM clients c JOIN sales s on c.id = s.client_id WHERE (c.date >= :min_date) AND (c.firstname like :name) ORDER BY s.date DESC
~~~

---

### Pagination: order by

**SQL TEMPLATE:**
~~~sql
clients_pagination_orderby:
    base: |
        SELECT *
        @{clients_pagination.from}
        @[clients_pagination.params]
        ORDER BY :orderby

    count: |
        SELECT count(1)
        @{clients_pagination.from}
        @[clients_pagination.params]
        
    from:
        FROM clients c
        JOIN sales s on c.id = s.client_id

    params:
        - c.date >= :min_date
        - like: c.firstname like :name
~~~

**PHP:**
~~~php
$params = [
    'name' => 'Jhon',
    'min_date' => '2018-01-01',
    'orderby' => 'c.date desc, c.id asc',
];

$sqlCount= $this->helper->getSqlFromYamlKey('clients:clients_pagination_orderby.count', $params);
$sqlList = $this->helper->getSqlFromYamlKey('clients:clients_pagination_orderby.base', $params);
~~~

**SQL:**
~~~sql
SELECT count(1) FROM clients c JOIN sales s on c.id = s.client_id WHERE (c.date >= :min_date) AND (c.firstname like :name)
SELECT * FROM clients c JOIN sales s on c.id = s.client_id WHERE (c.date >= :min_date) AND (c.firstname like :name) ORDER BY c.date desc, c.id asc
~~~

Full Documentation and examples
-------------------------------

- [Introduction](README.md)
- [Define your queries](doc/defining_queries.md)
- [Use your queries](doc/using_queries.md)
- [Examples](doc/examples.md)
- [Extending the bundle](doc/using_queries.md) - pending
- [Development](doc/development.md)
