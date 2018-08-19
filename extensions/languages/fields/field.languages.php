<?php



	if (!defined('__IN_SYMPHONY__')) {
		die('<h2>Symphony Error</h2><p>You cannot directly access this file</p>');
	}

	require_once EXTENSIONS . '/languages/extension.driver.php';

	class FieldLanguages extends Field {

		/*------------------------------------------------------------------------------------------------*/
		/*  Definition  */
		/*------------------------------------------------------------------------------------------------*/

		public function __construct() {
			parent::__construct();

			$this->_name     = 'Languages';
			$this->_required = 'yes';
		}

			public function canFilter()
			{
				return true;
			}

		/*------------------------------------------------------------------------------------------------*/
		/*  Settings  */
		/*------------------------------------------------------------------------------------------------*/

		public function get($setting = null) {
			if (is_null($setting)) {
				return $this->_settings;
			}

			if ($setting == 'available_codes') {
				$available_codes = explode(',', $this->_settings['available_codes']);
				$available_codes = array_map('trim', $available_codes);
				if (!is_array($available_codes)) {
					$available_codes = array();
				}

				return $available_codes;
			}

			if (!isset($this->_settings[$setting])) {
				return null;
			}

			return $this->_settings[$setting];
		}

		public function set($setting, $value) {
			if ($setting == 'available_codes') {
				if (is_array($value)) {
					$value = implode(',', $value);
				}
			}

			$this->_settings[$setting] = $value;
		}

		public function displaySettingsPanel(XMLElement &$wrapper, $errors = null) {
			parent::displaySettingsPanel($wrapper, $errors);

			$available_codes = $this->get('available_codes');

			$options = Extension_Languages::findOptions($available_codes);

			$label = Widget::Label(__('Available languages'));
			$label->appendChild(
				Widget::Select(
					'fields[' . $this->get('sortorder') . '][available_codes][]',
					$options,
					array('multiple' => 'multiple')
				)
			);

			$wrapper->appendChild($label);

			$div = new XMLElement('div', null, array('class' => 'three columns'));
			$this->appendAllowMultiple($div);
			$this->appendRequiredCheckbox($div);
			$this->appendShowColumnCheckbox($div);
			$wrapper->appendChild($div);
		}

		protected function appendAllowMultiple(XMLElement &$wrapper) {
			// Allow selection of multiple items
			$label = Widget::Label();
			$label->setAttribute('class', 'column');
			$input = Widget::Input('fields[' . $this->get('sortorder') . '][allow_multiple_selection]', 'yes', 'checkbox');
			if ($this->get('allow_multiple_selection') == 'yes') {
				$input->setAttribute('checked', 'checked');
			}
			$label->setValue($input->generate() . ' ' . __('Allow selection of multiple options'));

			$wrapper->appendChild($label);
		}

		public function commit() {
			if (!parent::commit()) {
				return false;
			}

			$id     = $this->get('id');
			$handle = $this->handle();

			if ($id === false) {
				return false;
			}

			$fields['field_id'] = $id;

			$available_codes                    = $this->get('available_codes');
			$fields['available_codes']          = empty($available_codes) ? '' : implode(',', $available_codes);
			$fields['allow_multiple_selection'] = $this->get('allow_multiple_selection') ? $this->get('allow_multiple_selection') : 'no';

			Symphony::Database()->query("DELETE FROM `tbl_fields_{$handle}` WHERE `field_id` = '{$id}' LIMIT 1");

			return Symphony::Database()->insert($fields, "tbl_fields_{$handle}");
		}



		/*------------------------------------------------------------------------------------------------*/
		/*  Publish  */
		/*------------------------------------------------------------------------------------------------*/

		public function displayPublishPanel(XMLElement &$wrapper, $data = null, $flagWithError = null, $fieldnamePrefix = null, $fieldnamePostfix = null, $entry_id = NULL) {
			$selected = isset($data['value']) ? $data['value'] : array();
			if (!is_array($selected)) {
				$selected = array_map('trim', explode(',', $selected));
			}

			$options = Extension_Languages::findOptions($selected);

			$available_codes = $this->get('available_codes');

			foreach ($options as $idx => $option) {
				if (!in_array($option[0], $available_codes)) {
					unset($options[$idx]);
				}
			}

			$fieldname = 'fields' . $prefix . '[' . $this->get('element_name') . ']' . $postfix;
			if ($this->get('allow_multiple_selection') == 'yes') {
				$fieldname .= '[]';
			}

			$label = Widget::Label($this->get('label'));
			if ($this->get('required') != 'yes') {
				$label->appendChild(new XMLElement('i', __('Optional')));
			}
			$label->appendChild(
				Widget::Select($fieldname, $options, ($this->get('allow_multiple_selection') == 'yes' ? array(
					'multiple' => 'multiple'
				) : null
				))
			);

			// Error
			if (!is_null($flagWithError)) {
				$wrapper->appendChild(Widget::Error($label, $flagWithError));
			}
			else {
				$wrapper->appendChild($label);
			}
		}



		/*------------------------------------------------------------------------------------------------*/
		/*  Input  */
		/*------------------------------------------------------------------------------------------------*/

		public function checkPostFieldData($data, &$message, $entry_id = null) {
			$message = null;
			if (!is_array($data)) {
				$data = array($data);
			}

			$available_codes = $this->get('available_codes');
			$not_allowed     = array();

			foreach ($data as $code) {
				if (!in_array($code, $available_codes)) {
					$not_allowed[] = $code;
				}
			}

			if (!empty($not_allowed)) {
				$msg = array();

				$options = Extension_Languages::findOptions();

				foreach ($not_allowed as $code) {
					foreach ($options as $details) {
						if ($details[0] == $code) {
							$msg[] = $details[2];
							break;
						}
					}
				}

				$message = __('Languages `%s` are not allowed.', array(implode(', ', $msg)));

				return self::__INVALID_FIELDS__;
			}

			return parent::checkPostFieldData($data, $message, $entry_id);
		}

		public function processRawFieldData($data, &$status, &$message = null, $simulate = false, $entry_id = null) {
			if (is_array($data)) {
				$data = implode(',', $data);
			}

			return parent::processRawFieldData($data, $status, $message, $simulate, $entry_id);
		}



		/*------------------------------------------------------------------------------------------------*/
		/*  Output  */
		/*------------------------------------------------------------------------------------------------*/

		public function appendFormattedElement(XMLElement &$wrapper, $data, $encode = false, $mode = null, $entry_id = null) {
			if (!is_array($data) || empty($data) || is_null($data['value'])) {
				return;
			}

			$selected = array_map('trim', explode(',', $data['value']));

			$result = new XMLElement($this->get('element_name'));

			foreach ($selected as $code) {
				$result->appendChild(new XMLElement('item', $code));
			}

			$wrapper->appendChild($result);
		}

		public function prepareTableValue($data, XMLElement $link = null, $entry_id = null) {
			$selected = array_map('trim', explode(',', $data['value']));

			$options = Extension_Languages::findOptions();
			$value   = array();

			foreach ($selected as $code) {
				foreach ($options as $details) {
					if ($details[0] == $code) {
						$value[] = $details[2];
						break;
					}
				}
			}

			$value = implode(', ', $value);

			return parent::prepareTableValue(array('value' => $value), $link, $entry_id);
		}



		/*------------------------------------------------------------------------------------------------*/
		/*  Internal  */
		/*------------------------------------------------------------------------------------------------*/

		public function appendFieldSchema(XMLElement $f) {
		}
	}
