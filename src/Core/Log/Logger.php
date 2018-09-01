<?php
namespace GameX\Core\Log;

use \Monolog\Logger as MonologLogger;
use \Exception;

class Logger extends MonologLogger{
    public function exception(Exception $e) {
        return $this->error('Exception', ['exception' => $e]);
    }
}
