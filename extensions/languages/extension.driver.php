<?php



	if (!defined('__IN_SYMPHONY__')) {
		die('<h2>Symphony Error</h2><p>You cannot directly access this file</p>');
	}

	require_once EXTENSIONS . '/languages/lib/class.languages.php';

	class Extension_Languages extends Extension {

		public $field_table = 'tbl_fields_languages';



		/*------------------------------------------------------------------------------------------------*/
		/*  Installation  */
		/*------------------------------------------------------------------------------------------------*/

		public function install() {
			return Symphony::Database()->query(sprintf(
				"CREATE TABLE `%s` (
					`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
					`field_id` INT(11) UNSIGNED NOT NULL,
					`available_codes` VARCHAR(255) NULL,
					`allow_multiple_selection` enum('yes','no') NOT NULL default 'no',
					PRIMARY KEY (`id`),
					KEY `field_id` (`field_id`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
				$this->field_table
			));
		}

		public function uninstall() {
			try {
				Symphony::Database()->query(sprintf(
					"DROP TABLE `%s`",
					$this->field_table
				));
			} catch (DatabaseException $dbe) {
				// table doesn't exist
			}
		}



		/*------------------------------------------------------------------------------------------------*/
		/*  Delegates  */
		/*------------------------------------------------------------------------------------------------*/

		public function getSubscribedDelegates() {
			return array(
				array(
					'page'     => '/frontend/',
					'delegate' => 'ManageEXSLFunctions',
					'callback' => 'dManageEXSLFunctions'
				),

			);
		}

		public function dManageEXSLFunctions($context) {
			$context['manager']->addFunction(
				'Extension_Languages::fileExists',
				'extension_languages',
				'fileExists'
			);
		}



		/*------------------------------------------------------------------------------------------------*/
		/*  Public interface  */
		/*------------------------------------------------------------------------------------------------*/

		public static function fileExists($code) {
			return is_file(Languages::local()->getFileName($code)) ? 1 : 0;
		}

		/**
		 * Get language options for in desired language. Useful to build a Select widget.
		 *
		 * @param array|string|int $selected - An array of language codes to mark as selected
		 * @param array            $pool     (optional) - An array of language codes to be displayed. Leave null to show all.
		 * @param string           $lang     (optional) - Get language names for this language.
		 *
		 * @return array
		 */
		public static function findOptions($selected = array(), $pool = null, $lang = null) {
			if (!is_array($selected)) {
				$selected = array($selected);
			}

			$native = Languages::all()->listAll('name');
			if (is_array($pool)) {
				$native = array_intersect_key($native, array_flip($pool));
			}

			if ($lang === null) {
				$lang = Lang::get();
			}

			// try to get languages in author language
			$local = Languages::local()->listAll($lang);

			// if languages not found, use english ones
			if ($local === false) {
				$local = Languages::local()->listAll();
			}

			$options = array();

			foreach ($native as $code => $info) {
				$options[] = array(
					$code,
					in_array($code, $selected),
					sprintf(
						"[%s] %s%s",
						strtoupper($code),
						isset($local[$code]) ? $local[$code] . ' || ' : '',
						$info['name']
					),
				);
			}

			return $options;
		}
	}
