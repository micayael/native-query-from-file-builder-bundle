<?php

namespace Micayael\NativeQueryFromFileBuilderBundle\Exception;

class NonExistentQueryDirectoryException extends \Exception
{
    public function __construct($directory)
    {
        $message = sprintf(
            'El directorio configurado "%s" no existe. Favor verifique la configuración del bundle "native_query_from_file_builder.sql_queries_dir"',
            $directory
        );

        parent::__construct($message);
    }
}
