<?php



	require_once EXTENSIONS . '/languages/lib/class.langfile.php';

	Final Class LangFileLocal extends LangFile {

		private $cache = array();

		/**
		 * Return localised language names for requested code.
		 *
		 * @param $code = target language code
		 *
		 * @return array - resulting languages
		 */
		public function listAll($code = 'en') {
			if (!isset($this->cache[$code])) {
				$result = array();

				$file = $this->getFileName($code);

				if (!is_file($file) || !is_readable($file)) {
					return false;
				}

				$doc = new DOMDocument();
				$doc->load($file);
				$xPath = new DOMXPath($doc);

				foreach ($xPath->query("/languages/lang") as $lang) {
					/** @var $lang DOMElement */
					$result[$lang->getAttribute('code')] = $lang->nodeValue;
				}

				$this->cache[$code] = $result;
			}

			return $this->cache[$code];
		}

		public function getFileName($code) {
			return EXTENSIONS . "/languages/resources/languages/languages.$code.xml";
		}
	}
