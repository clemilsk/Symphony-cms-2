<?php

	include "functions.php";

//	$debug = true;
	$debug = false;

	try {
		$languages = transform('source.xml', 'languages.xsl');
	} catch (Exception $e) {
		echo $e->getMessage();
		exit;
	}

	try {
		$languages_en = transform('source.xml', 'languages.en.xsl');
	} catch (Exception $e) {
		echo $e->getMessage();
		exit;
	}

	if ($debug) {
		header("Content-Type:text/xml");
		echo $languages;
//		echo $languages_en;
	}
	else {
		writeFile('output/languages.xml', $languages);
		writeFile('output/languages.en.xml', $languages_en);

		echo 'Success.';
	}

	exit;
