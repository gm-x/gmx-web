<?php
namespace GameX\Core\Log;

use \Monolog\Logger as MonologLogger;

class Logger extends MonologLogger {
    
    /**
     * @param \Throwable|\Exception $e
     * @return bool
     */
    public function exception($e) {
        return $this->error('Exception', ['exception' => $e]);
    }
}
