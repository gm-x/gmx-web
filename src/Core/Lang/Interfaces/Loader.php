<?php
namespace GameX\Core\Lang\Interfaces;

use \GameX\Core\Lang\Exceptions\CantReadException;

interface Loader {
    /**
     * @param string $language
     * @param string $section
     * @return array
     * @throws CantReadException
     */
    public function loadSection($language, $section);
}
