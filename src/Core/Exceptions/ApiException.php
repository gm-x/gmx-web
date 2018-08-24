<?php
namespace GameX\Core\Exceptions;

use \Exception;

class ApiException extends Exception {
    const ERROR_SERVER = 1;
    const ERROR_INVALID_TOKEN = 2;
    const ERROR_VALUE_REQUIRED = 3;
    const ERROR_VALIDATION = 4;
}
