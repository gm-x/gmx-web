<?php
namespace GameX\Core\Exceptions;

use \Exception;

class ApiException extends Exception {
    const ERROR_GENERIC = 1;
    const ERROR_REQUIRED = 2;
    const ERROR_VALIDATION = 3;
}
