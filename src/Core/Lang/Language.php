<?php
namespace GameX\Core\Lang;

use \GameX\Core\Lang\Interfaces\Loader;
use \GameX\Core\Lang\Interfaces\Provider;
use \GameX\Core\Lang\Exceptions\BadLanguageException;
use \GameX\Core\Lang\Exceptions\CantReadException;

class Language {
    const HTTP_ACCEPT_PATTERN = '/([[:alpha:]]{1,8}(?:-[[:alpha:]|-]{1,8})?)(?:\\s*;\\s*q\\s*=\\s*(?:1\\.0{0,3}|0\\.\\d{0,3}))?\\s*(?:,|$)/i';

    /**
     * @var Loader
     */
    protected $loader;

    /**
     * @var Provider
     */
    protected $provider = null;

    /**
     * @var string[]
     */
    protected $languages = [];

    /**
     * @var string
     */
    protected $userLanguage = 'en';

    /**
     * @var array
     */
    protected $dictionary = [];

    /**
     * Lang constructor.
     * @param Loader $loader
     * @param Provider $provider
     * @param string[] $languages
     * @param string $default
     * @throws CantReadException|BadLanguageException
     */
    public function __construct(Loader $loader, Provider $provider, array $languages = ['en' => 'English'], $default = 'en') {
        $this->loader = $loader;
        $this->provider = $provider;
        $this->languages = $languages;
        $this->userLanguage = $this->checkUserLanguage($default);
        if (!$this->userLanguage) {
            throw new BadLanguageException();
        }
    }

    /**
     * @param string $lang
     * @return $this
     */
    public function setUserLang($lang) {
        if ($this->exists($lang)) {
            $this->provider->setSessionLang($lang);
        }
        return $this;
    }

    /**
     * @param $section
     * @param $key
     * @param array $args
     * @return string
     */
    public function format($section, $key, $args = null) {
        $message = $this->getMessage($section, $key);
        if (!$message) {
            return sprintf('{%s.%s}', $section, $key);
        }
        return $args !== null && is_array($args) && !empty($args)
            ? vsprintf($message, $args)
            : $message;
    }

    /**
     * @return string
     */
    public function getUserLanguage() {
        return $this->userLanguage;
    }

    /**
     * @return string[]
     */
    public function getLanguages() {
        return $this->languages;
    }

    /**
     * @param string $default
     * @return string
     */
    protected function checkUserLanguage($default) {
        $language = $this->provider->getSessionLang();
        if ($language && $this->exists($language)) {
            return $language;
        }
        $language = $this->provider->getAcceptLanguageHeader();
        if ($language) {
            preg_match_all(self::HTTP_ACCEPT_PATTERN, $language, $matches);
            foreach($matches[1] as $match) {
                $language = str_replace('-', '_', $match);
                if ($language && $this->exists($language)) {
                    return $language;
                }
            }
        }

        return $this->exists($default) ? $default : null;
    }

    protected function exists($lang) {
        return array_key_exists($lang, $this->languages);
    }

    /**
     * @param string $section
     * @param string $key
     * @return string|null
     */
    protected function getMessage($section, $key) {
        if (!array_key_exists($section, $this->dictionary)) {
            try {
                $this->dictionary[$section] = (array)$this->loader->loadSection($this->userLanguage, $section);
            } catch (CantReadxception $e) {
                $this->dictionary[$section] = [];
            }
        }

        return array_key_exists($key, $this->dictionary[$section]) ? $this->dictionary[$section][$key] : null;
    }
}
