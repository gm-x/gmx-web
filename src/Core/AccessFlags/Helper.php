<?php
namespace GameX\Core\AccessFlags;

class Helper {
    /**
     * @param string $flags
     * @return int
     */
    public static function readFlags($flags) {
        $result = 0;
        for ($i = 0, $l = strlen($flags); $i < $l; $i++) {
            $f = ord($flags[$i]);
            if ($f >= 97 && $f <= 126) {
                $result |= (1 << ($f - 97));
            }
        }

        return $result;
    }

    /**
     * @param int $flags
     * @return string
     */
    public static function getFlags($flags) {
        $result = '';
        for ($i = 0; $i <= 29; $i++) {
            if ( ($flags  & ( 1 << $i ) ) > 0 ) {
                $result .= chr($i + 97);
            }
        }
        return $result;
    }
}
