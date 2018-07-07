<?php
namespace GameX\Core\Lang;

use \Slim\Http\Request;
use \GameX\Core\Session\Session;

class Language {
    const HTTP_ACCEPT_PATTERN = '/([[:alpha:]]{1,8}(?:-[[:alpha:]|-]{1,8})?)(?:\\s*;\\s*q\\s*=\\s*(?:1\\.0{0,3}|0\\.\\d{0,3}))?\\s*(?:,|$)/i';

    /**
     * @var string
     */
    protected $baseDir;

    /**
     * @var Session|null
     */
    protected $session = null;

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
     * @param $baseDir
     * @param Session|null $session
     * @param Request|null $request
     * @param string $default
     * @throws CantReadJSONException|BadLanguageException
     */
    public function __construct($baseDir, Session $session = null, Request $request = null, $default = 'en') {
        $this->baseDir = $baseDir;
        $this->session = $session;
        $this->languages = $this->loadJson($this->baseDir . DIRECTORY_SEPARATOR . 'languages.json');
        $this->userLanguage = $this->getUserLanguage($session, $request, $default);
        if (!$this->userLanguage) {
            throw new BadLanguageException();
        }
    }

    /**
     * @param string $lang
     * @return $this
     */
    public function setUserLang($lang) {
        if ($this->session && $this->exists($lang)) {
            $this->session->set('lang', $lang);
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
     * @param Session $session
     * @param Request $request
     * @param string $default
     * @return array
     */
    protected function getUserLanguage(Session $session, Request $request, $default) {
        if ($session && $session->exists('lang')) {
            $language = $session->get('lang');
            if ($this->exists($language)) {
                return $language;
            }
        }
        $language = $request ? $request->getServerParam('HTTP_ACCEPT_LANGUAGE') : null;
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
     * @param string $filePath
     * @return mixed
     * @throws CantReadJSONException
     */
    protected function loadJson($filePath) {
        if (!is_readable($filePath)) {
            throw new CantReadJSONException('Can\'t read file ' . $filePath);
        }

        $data = file_get_contents($filePath);
        if (!$data) {
            throw new CantReadJSONException('Can\'t read file ' . $filePath);
        }
        $data = json_decode($data, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new CantReadJSONException('Can\'t read file ' . $filePath);
        }
        return $data;
    }

    /**
     * @param string $section
     * @param string $key
     * @return string|null
     */
    protected function getMessage($section, $key) {
        if (!array_key_exists($section, $this->dictionary)) {
            try {
                $this->dictionary[$section] = (array)$this->loadJson(
                    $this->baseDir . DIRECTORY_SEPARATOR . $this->userLanguage . DIRECTORY_SEPARATOR . $section . '.json'
                );
            } catch (CantReadJSONException $e) {
                $this->dictionary[$section] = [];
            }
        }

        return array_key_exists($key, $this->dictionary[$section]) ? $this->dictionary[$section][$key] : null;
    }
}
