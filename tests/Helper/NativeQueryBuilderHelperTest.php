<?php

namespace Micayael\NativeQueryFromFileBuilderBundle\Tests\Helper;

use Micayael\NativeQueryFromFileBuilderBundle\Exception\NonExistentQueryDirectoryException;
use Micayael\NativeQueryFromFileBuilderBundle\Exception\NonExistentQueryFileException;
use Micayael\NativeQueryFromFileBuilderBundle\Helper\NativeQueryBuilderHelper;
use PHPUnit\Framework\TestCase;

class NativeQueryBuilderHelperTest extends TestCase
{
    /**
     * @var NativeQueryBuilderHelper
     */
    private $helper;

    protected function setUp()
    {
        $this->helper = new NativeQueryBuilderHelper(__DIR__.'/../queries');
    }

    /**
     * @expectedException \Micayael\NativeQueryFromFileBuilderBundle\Exception\NonExistentQueryDirectoryException
     */
    public function testNonExistentQueryDirectoryException()
    {
        $helper = new NativeQueryBuilderHelper(__DIR__.'/../non_existent');

        $helper->getSqlFromYamlKey('queries:persona', []);
    }

    public function testNonExistentQueryDirectoryExceptionMessage()
    {
        try {
            $helper = new NativeQueryBuilderHelper(__DIR__.'/../non_existent');
            $helper->getSqlFromYamlKey('queries:persona', []);
        } catch (NonExistentQueryDirectoryException $e) {
            $this->assertRegExp(
                '/El directorio configurado ".+" no existe. Favor verifique la configuración del bundle "native_query_from_file_builder.sql_queries_dir"/',
                $e->getMessage()
            );
        }
    }

    /**
     * @expectedException \Micayael\NativeQueryFromFileBuilderBundle\Exception\NonExistentQueryFileException
     */
    public function testNonExistentQueryFileException()
    {
        $this->helper->getSqlFromYamlKey('non_existent:persona', []);
    }

    public function testNonExistentQueryFileExceptionMessage()
    {
        try {
            $this->helper->getSqlFromYamlKey('non_existent:persona', []);
        } catch (NonExistentQueryFileException $e) {
            $this->assertRegExp(
                '/El archivo de queries solicitado ".+" no existe/',
                $e->getMessage()
            );
        }
    }

    public function testSimpleSql()
    {
        $sql = $this->helper->getSqlFromYamlKey('queries:persona', []);

        $this->assertEquals('SELECT * FROM persona', $sql);
    }

    public function testPersonaBySlugSql()
    {
        $params = [
            'slug' => 'jhon-doe',
        ];

        $sql = $this->helper->getSqlFromYamlKey('queries:persona_by_slug', $params);

        $this->assertEquals('SELECT * FROM persona WHERE slug = :slug', $sql);
    }

    public function testSqlWithOptionalFilterNombre()
    {
        $params = [
            'nombre' => 'Jhon',
        ];

        $sql = $this->helper->getSqlFromYamlKey('queries:personas1.base', $params);

        $this->assertEquals('SELECT * FROM persona p WHERE nombre = :nombre', $sql);
    }

    public function testSqlWithOptionalFiltersNombreAndApellido()
    {
        $params = [
            'nombre' => 'Jhon',
            'apellido' => 'Doe',
        ];

        $sql = $this->helper->getSqlFromYamlKey('queries:personas1.base', $params);

        $this->assertEquals('SELECT * FROM persona p WHERE nombre = :nombre AND apellido = :apellido', $sql);
    }

    public function testSqlWithOptionalFiltersWhereIncluded()
    {
        $params = [
            'nombre' => 'Jhon',
            'apellido' => 'Doe',
        ];

        $sql = $this->helper->getSqlFromYamlKey('queries:personas2.base', $params);

        $this->assertEquals('SELECT * FROM persona p WHERE nombre = :nombre AND apellido = :apellido', $sql);
    }

    public function testSqlWithRequiredKeyAndWithNoFilters()
    {
        $sql = $this->helper->getSqlFromYamlKey('queries:persona2.base');

        $this->assertEquals('SELECT p.nombre, p.apellido FROM persona p', $sql);
    }

    public function testSqlWithRequiredKeyAndFilters()
    {
        $params = [
            'nombre' => 'Jhon',
            'apellido' => 'Doe',
        ];

        $sql = $this->helper->getSqlFromYamlKey('queries:persona2.base', $params);

        $this->assertEquals('SELECT p.nombre, p.apellido FROM persona p WHERE nombre = :nombre AND apellido = :apellido', $sql);
    }

    public function testSqlWithRequiredKeysAndFilters()
    {
        $sql = $this->helper->getSqlFromYamlKey('queries:persona3.base');

        $this->assertEquals('SELECT p.documento, p.nombre, p.apellido FROM persona p', $sql);
    }
}
