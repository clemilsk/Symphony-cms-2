<?php



	require_once EXTENSIONS . '/languages/lib/class.langfile.php';

	Final Class LangFileAll extends LangFile {

		private $cache = null;

		private $allowed_keys = array('name', 'iso-639-1', 'iso-639-2t', 'iso-639-2b', 'iso-639-3', 'iso-639-6');

		/**
		 * Return all languages as array.
		 *
		 * @param $keys = an array with values tot return
		 *
		 * @return array - resulting languages
		 */
		public function listAll($keys = null) {
			if ($this->cache === null) {
				if ($keys === null) {
					$keys = $this->allowed_keys;
				}
				elseif (!is_array($keys)) {
					$keys = array($keys);
				}

				$keys = array_intersect($keys, $this->allowed_keys);

				$result = array();

				$doc = new DOMDocument();
				$doc->load($this->getFile());
				$xPath = new DOMXPath($doc);

				foreach ($xPath->query("/languages/lang") as $lang) {
					/** @var $lang DOMElement */

					$r    = array();
					$code = $lang->getAttribute('code');

					foreach ($keys as $key) {
						$r[$key] = $xPath->query($key, $lang)->item(0)->nodeValue;
					}

					$result[$code] = $r;
				}

				$this->cache = $result;
			}

			return $this->cache;
		}

		private function getFile() {
			return EXTENSIONS . '/languages/resources/languages/languages.xml';
		}
	}
