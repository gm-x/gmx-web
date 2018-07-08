<?php
namespace GameX\Core\Lang\Interfaces;

interface Provider {
    /**
     * @return string
     */
    public function getAcceptLanguageHeader();

    /**
     * @return string|null
     */
    public function getSessionLang();

    /**
     * @param string $lang
     */
    public function setSessionLang($lang);
}
