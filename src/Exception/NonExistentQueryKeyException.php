<?php

namespace Micayael\NativeQueryFromFileBuilderBundle\Exception;

class NonExistentQueryKeyException extends \Exception
{
    public function __construct($queryKey)
    {
        $message = sprintf('El queries solicitado "%s" no existe', $queryKey);

        parent::__construct($message);
    }
}
