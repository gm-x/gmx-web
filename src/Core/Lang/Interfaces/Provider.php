<?php

namespace GameX\Core\Lang\Interfaces;

interface Provider
{
    /**
     * @return string
     */
    public function getAcceptLanguageHeader();

    /**
     * @return string|null
     */
    public function getLang();

    /**
     * @param string $lang
     */
    public function setLang($lang);
}
