<?php

	function processXML($xml, $xsl) {
		// Load the XML source
		$xmlDoc = new DOMDocument;
		$xmlDoc->load($xml);

		$xslDoc = new DOMDocument;
		$xslDoc->load($xsl);

		// Configure the transformer
		$proc = new XSLTProcessor;
		$proc->importStyleSheet($xslDoc); // attach the xsl rules

		return $proc->transformToXML($xmlDoc);
	}

	function checkFile($file) {
		return is_file($file) && is_readable($file);
	}

	function writeFile($file, $data, $perm = 0644, $mode = 'w') {
		if ((!is_writable(dirname($file)) || !is_readable(dirname($file))) && (!is_readable($file) || !is_writable($file))) {
			return false;
		}

		if (!$handle = fopen($file, $mode)) {
			return false;
		}

		if (fwrite($handle, $data, strlen($data)) === false) {
			return false;
		}

		fclose($handle);

		try {
			if (is_null($perm)) {
				$perm = 0644;
			}
			chmod($file, intval($perm, 8));
		} catch (Exception $ex) {
			// If we can't chmod the file, this is probably because our host is
			// running PHP with a different user to that of the file. Although we
			// can delete the file, create a new one and then chmod it, we run the
			// risk of losing the file as we aren't saving it anywhere. For the immediate
			// future, atomic saving isn't needed by Symphony and it's recommended that
			// if your extension require this logic, it uses it's own function rather
			// than this 'General' one.
			return true;
		}

		return true;
	}

	function transform($src, $trans) {
		if (!checkFile($src)) {
			throw new Exception('Source XML file missing.');
		}

		if (!checkFile($trans)) {
			throw new Exception('XSL transformation file missing.');
		}

		return processXML($src, $trans);
	}

