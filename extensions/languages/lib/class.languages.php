<?php

	Final Class Languages {

		private $objects = array();

		private static $instance = null;

		private static function instance() {
			if (!self::$instance instanceof Languages) {
				self::$instance = new Languages;
			}

			return self::$instance;
		}


		/*------------------------------------------------------------------------------------------------*/
		/*  Public interface  */
		/*------------------------------------------------------------------------------------------------*/

		/**
		 * @return LangFileAll
		 */
		public static function all() {
			return self::instance()->getObject('all');
		}

		/**
		 * @return LangFileLocal
		 */
		public static function local() {
			return self::instance()->getObject('local');
		}



		/*------------------------------------------------------------------------------------------------*/
		/*  Internal usage interface  */
		/*------------------------------------------------------------------------------------------------*/

		private function getObject($obj) {
			$obj_id = strtolower($obj);

			if (!array_key_exists($obj_id, $this->objects)) {
				$this->objects[$obj_id] = $this->createObject($obj_id);
			}

			return $this->objects[$obj_id];
		}

		private function createObject($obj_id) {
			$class_name = "langfile{$obj_id}";

			require_once EXTENSIONS . "/languages/lib/class.$class_name.php";

			if (!class_exists($class_name)) {
				throw new Exception("LangFile with id `$obj_id` doesn't exist.");
			}

			return new $class_name();
		}
	}
