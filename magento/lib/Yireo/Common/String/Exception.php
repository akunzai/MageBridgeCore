<?php

namespace Yireo\Common\String;

use Exception as TargetException;

/**
 * Class Exception.
 */
class Exception
{
    /**
     * @var TargetException
     */
    private $exception;

    /**
     * Exception constructor.
     */
    public function __construct(TargetException $exception)
    {
        $this->exception = $exception;
    }

    /**
     * Convert an exception into a string.
     */
    public function printException()
    {
        echo get_class($this->exception) . ': ';
        echo $this->exception->getMessage();
        echo "\n";
        echo $this->exception->getTraceAsString();
    }
}
