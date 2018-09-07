<?php
namespace GameX\Core;

use \Psr\Container\ContainerInterface;

class Utils {
    /**
     * @var ContainerInterface
     */
    protected static $container;

    /**
     * @param ContainerInterface $container
     */
    public static function setContainer(ContainerInterface $container) {
        self::$container = $container;
    }

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
        /** @var \GameX\Core\Log\Logger $logger */
        $logger = self::$container->get('log');
        $logger->debug('Persistences ' . $_SERVER['REQUEST_URI'] . PHP_EOL . implode(PHP_EOL, $lines));
    }
}