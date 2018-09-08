<?php
namespace GameX\Core;

class Utils {
    public static function logBacktrace() {
        $backtrace = array_slice(
            debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS),
            0
        );
        array_shift($backtrace);
        $lines = [];
        foreach ($backtrace as $item) {
            $line = "\t";
            if (array_key_exists('file', $item) && array_key_exists('line', $item)) {
                $line .= $item['file'] . ':' . $item['line'] . ' ';
            }
            if (array_key_exists('class', $item)) {
                $line .= $item['class'] . $item['type'] . $item['function'];
            } else {
                $line .= $item['function'];
            }

            $lines[] = $line;
        }
        return $lines;
    }
    
    /**
     * @param int $length
     * @return string
     */
    public static function generateToken($length = 32) {
        return bin2hex(random_bytes($length));
    }
}
