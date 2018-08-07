<?php

namespace Micayael\NativeQueryFromFileBuilderBundle\Tests\Helper;

use Micayael\NativeQueryFromFileBuilderBundle\Exception\NonExistentQueryDirectoryException;
use Micayael\NativeQueryFromFileBuilderBundle\Exception\NonExistentQueryFileException;
use Micayael\NativeQueryFromFileBuilderBundle\Exception\NonExistentQueryKeyException;
use Micayael\NativeQueryFromFileBuilderBundle\Helper\NativeQueryBuilderHelper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class NativeQueryBuilderHelperTest extends TestCase
{
    /**
     * @var NativeQueryBuilderHelper
     */
    private $helper;

    protected function setUp()
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $cache = null;

        $this->helper = new NativeQueryBuilderHelper($eventDispatcher, $cache, __DIR__.'/../queries');
    }

    public function testNonExistentQueryDirectoryException()
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $cache = null;

        $helper = new NativeQueryBuilderHelper($eventDispatcher, $cache, __DIR__.'/../non_existent');

        $params = [];

        $this->expectException(NonExistentQueryDirectoryException::class);
        $this->expectExceptionMessageRegExp('/El directorio configurado ".+" no existe. Favor verifique la configuración del bundle "native_query_from_file_builder.sql_queries_dir"/');

        $helper->getSqlFromYamlKey('clients:product', $params);
    }

    public function testNonExistentQueryFileException()
    {
        $params = [];

        $this->expectException(NonExistentQueryFileException::class);
        $this->expectExceptionMessageRegExp('/El archivo de queries solicitado ".+" no existe/');

        $this->helper->getSqlFromYamlKey('non_existent:client', $params);
    }

    public function testNonExistentQueryKey()
    {
        $params = [];

        $this->expectException(NonExistentQueryKeyException::class);
        $this->expectExceptionMessageRegExp('/El queries solicitado ".+" no existe/');

        $this->helper->getSqlFromYamlKey('clients:non_existent', $params);
    }

    public function testSimpleSql()
    {
        $params = [];

        $sql = $this->helper->getSqlFromYamlKey('clients:clients', $params);

        $this->assertEquals('SELECT * FROM clients', $sql);
    }

    //----------------------------------------------------------------------------------------------
    // Required Params
    //----------------------------------------------------------------------------------------------

    public function testSqlWithRequiredParams()
    {
        $params = [
            'slug' => 'jhon-doe',
        ];

        $sql = $this->helper->getSqlFromYamlKey('clients:client_by_slug', $params);

        $this->assertEquals('SELECT * FROM clients WHERE slug = :slug', $sql);
    }

    //----------------------------------------------------------------------------------------------
    // Snippets (optionals & required)
    //----------------------------------------------------------------------------------------------

    public function testSqlWithOptionalFiltersNotUsingFilters()
    {
        $params = [];

        $sql = $this->helper->getSqlFromYamlKey('clients:clients_optional_filters.base', $params);

        $this->assertEquals('SELECT * FROM clients c ORDER BY c.id DESC', $sql);
    }

    public function testSqlWithOptionalFilterName()
    {
        $params = [
            'firstname' => 'Jhon',
        ];

        $sql = $this->helper->getSqlFromYamlKey('clients:clients_optional_filters.base', $params);

        $this->assertEquals('SELECT * FROM clients c WHERE (firstname = :firstname) ORDER BY c.id DESC', $sql);
    }

    public function testSqlWithOptionalFiltersNameAndLastname()
    {
        $params = [
            'firstname' => 'Jhon',
            'lastname' => 'Doe',
        ];

        $sql = $this->helper->getSqlFromYamlKey('clients:clients_optional_filters.base', $params);

        $this->assertEquals('SELECT * FROM clients c WHERE (firstname = :firstname) AND (lastname = :lastname) ORDER BY c.id DESC', $sql);
    }

    public function testSqlWithOptionalFiltersAndWhereIncluded()
    {
        $params = [
            'year' => 1983,
            'firstname' => 'Jhon',
            'lastname' => 'Doe',
        ];

        $sql = $this->helper->getSqlFromYamlKey('clients:clients_optional_filters_and_where_included.base', $params);

        $this->assertEquals('SELECT * FROM clients c WHERE YEAR(birthday) > :year AND (firstname = :firstname) AND (lastname = :lastname)', $sql);
    }

    public function testSqlWithRequiredKeysAndNoFilters()
    {
        $sql = $this->helper->getSqlFromYamlKey('clients:clients_required_key.base');

        $this->assertEquals('SELECT c.id, c.firstname as name, c.lastname FROM clients c', $sql);
    }

    public function testSqlWithRequiredKeyAndFilters()
    {
        $params = [
            'firstname' => 'Jhon',
        ];

        $sql = $this->helper->getSqlFromYamlKey('clients:clients_required_key.base', $params);

        $this->assertEquals('SELECT c.id, c.firstname as name, c.lastname FROM clients c WHERE (c.firstname = :firstname)', $sql);
    }

    public function testSqlWithRequiredKeysAndFilters()
    {
        $params = [
            'firstname' => 'Jhon',
            'lastname' => 'Doe',
        ];

        $sql = $this->helper->getSqlFromYamlKey('clients:clients_required_keys.base', $params);

        $this->assertEquals('SELECT c.id, c.firstname as name, c.lastname, YEAR(c.birthday) as year FROM clients c WHERE (c.firstname = :firstname) AND (c.lastname = :lastname)', $sql);
    }

    //----------------------------------------------------------------------------------------------
    // Special filters
    //----------------------------------------------------------------------------------------------

    public function testSqlWithWhereIn()
    {
        $params = [
            'firstnames' => ['Jhon', 'Mary', 'Steven'],
        ];

        $sql = $this->helper->getSqlFromYamlKey('clients:clients_special_filters.base', $params);

        $this->assertEquals('SELECT * FROM clients c WHERE (firstname IN(:firstnames_0,:firstnames_1,:firstnames_2))', $sql);
    }

    //----------------------------------------------------------------------------------------------
    // Subqueries - Multipart Query
    //----------------------------------------------------------------------------------------------

    public function testSqlWithAnySubquery()
    {
        $params = [
            'firstname' => 'Jhon',
            'date' => '2018-01-01',
        ];

        $sql = $this->helper->getSqlFromYamlKey('clients:clients_any_subquery.base', $params);

        $this->assertEquals('SELECT c.firstname, c.lastname FROM clients c WHERE c.sold > ANY(SELECT s.amount FROM sale s WHERE (s.date > :date) ORDER BY s.amount DESC LIMIT 10) AND (c.firstname = :firstname)', $sql);
    }

    //----------------------------------------------------------------------------------------------
    // Pagination
    //----------------------------------------------------------------------------------------------

    public function testSqlWithPagination()
    {
        $params = [
            'name' => 'Jhon',
            'min_date' => '2018-01-01',
        ];

        $sql = $this->helper->getSqlFromYamlKey('clients:clients_pagination.count', $params);

        $this->assertEquals('SELECT count(1) FROM clients c JOIN sales s on c.id = s.client_id WHERE (c.date >= :min_date) AND (c.firstname like :name)', $sql);

        $sql = $this->helper->getSqlFromYamlKey('clients:clients_pagination.base', $params);

        $this->assertEquals('SELECT * FROM clients c JOIN sales s on c.id = s.client_id WHERE (c.date >= :min_date) AND (c.firstname like :name) ORDER BY s.date DESC', $sql);
    }
}
