<?php
namespace GameX\Core\AccessFlags;

class Helper {
    public static function readFlags($flags) {
        $result = 0;
        for ($i = 0, $l = strlen($flags); $i < $l; $i++) {
            $f = ord($flags[$i]);
            if ($f >= 97 && $f <= 122) {
                $result |= (1 << ($f - 97));
            }
        }

        return $result;
    }

    public static function getFlags($flags) {
        $result = '';
        for ($i = 0; $i <= 32; $i++) {
            if ( ($flags  & ( 1 << $i ) ) > 0 ) {
                $result .= chr($i + 97);
            }
        }
        return $result;
    }
}
