<?php

namespace GameX\Core\Lang;

use \Locale;
use \o80\i18n\Provider;
use \o80\i18n\CantLoadDictionaryException;

class LangProvider implements Provider {
	private $path = '.';

	private $loadedLang = null;

	function __construct() {}

	/**
	 * @param string $path The path of the directory containing the dictionaries files
	 */
	public function setLangsPath($path) {
		$this->path = $path;
	}

	/**
	 * Load the best dictionary looking at the prefered languages given in parameter.
	 *
	 * @param array $langs Ordered list of accepted languages, prefered ones are first
	 * @return array|null The dictionary or null if not found
	 * @throws CantLoadDictionaryException Thrown when there is no files in the directories path
	 */
	public function load($langs) {
		// List file names
		$files = $this->listLangFiles();
		$this->loadedLang = null;

		if (empty($files)) {
			throw new CantLoadDictionaryException(CantLoadDictionaryException::NO_DICTIONARY_FILES);
		}

		foreach ($langs as $lang) {
			$loaded = $this->loadMatchingFile($files, $lang);
			$dict = $loaded['dict'];
			$loadedLang = $loaded['lang'];
			if ($dict !== null) {
				$this->loadedLang = $loadedLang;
				return $dict;
			}
		}

		return null;
	}

	/**
	 * List the files from the {@code path} directory and sort them by filename size desc.
	 *
	 * @return array Array of files found
	 */
	public function listLangFiles() {
		$files = array_diff(scandir($this->path), array('..', '.'));
		uasort($files, function ($a, $b) {
			return strlen($a) < strlen($b);
		});
		$files = array_filter($files, function($file) {
			return substr($file, -5) === '.json';
		});
		return $files;
	}

	/**
	 * Parse a JSON file from the {@code path} directry.
	 *
	 * @param string $filename The name of the file
	 * @return array The dictionary
	 */
	private function loadFile($filename) {
		return json_decode(file_get_contents($this->path . '/' . $filename), true);
	}

	/**
	 * Load the best dictionary looking at the language code given in parameter.
	 *
	 * @param array $files The array of dictionary file names
	 * @param string $lang The language code
	 * @return array|null The dictionary found for the given language code, or null if there is no match.
	 */
	private function loadMatchingFile($files, $lang) {
		// Check all file names
		foreach ($files as $file) {
			// Extract locale from filename
			$fileLocale = substr($file, 0, strlen($file) - 5);

			if ($this->localeFilterMatches($lang, $fileLocale)) { // Check if filename matches $lang
				return array('dict' => $this->loadFile($file), 'lang' => $fileLocale);
			}
		}

		return null;
	}

	/**
	 * This method gives the code of loaded lang. It must be called AFTER the "load" method.
	 *
	 * @return string The code of the loaded lang.
	 */
	public function getLoadedLang() {
		return $this->loadedLang;
	}

	protected function localeFilterMatches($lang, $fileLocale) {
		return class_exists('Locale')
			? Locale::filterMatches($lang, $fileLocale)
			: true;
	}
}
