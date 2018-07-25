<?php

namespace Micayael\NativeQueryFromFileBuilderBundle\Exception;

class NonExistentQueryFileException extends \Exception
{
    public function __construct($filename)
    {
        $message = sprintf('El archivo de queries solicitado "%s" no existe', $filename);

        parent::__construct($message);
    }
}
