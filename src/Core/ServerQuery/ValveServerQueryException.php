<?php
namespace GameX\Core\ServerQuery;

use \Exception;

class ValveServerQueryException extends Exception {
	const ERROR_SOCKET = 1;
	const ERROR_CONNECT = 2;
	const ERROR_WRITE = 3;
	const ERROR_READ = 4;
	const ERROR_DECODE = 5;
	const ERROR_BAD_HEADER = 6;
	const ERROR_DECOMPRESS = 7;
	const ERROR_RCON = 8;
}
