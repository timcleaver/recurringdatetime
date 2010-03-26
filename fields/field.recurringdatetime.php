<?php
	require_once( EXTENSIONS . '/datetime/fields/field.datetime.php' );
	/**
	 * The field that will be generated to accept user input when an instance
	 * of this is added to a section.
	 *
	 * @author Timothy Cleaver
	 * @version 0.0.1
	 * @since 2.07
	 */
	Class fieldRecurringDateTime extends fieldDateTime {
		/**
		 * Declare the recurrence interval values.
		 */

		/**
		 * Construct a new instance of this field.
		 *
		 * @param reference $parent
		 *	a reference to the parent of this field.
		 */
		function __construct(&$parent){
			parent::__construct($parent);
			$this->_name = __('Recurring Date-Time');
		}

		/**
		 * Accessor for the valid interval units. This is implemented as a method
		 * because PHP cannot natively declare an array constant. Using a method
		 * prevents accidental changes to the array. The contents of this array is
		 * localized.
		 *
		 * @return array[int]string
		 *	the array of valid interval units.
		 */
		protected function options() {
			return array('never', 'day', 'fortnight', 'week', 'month', 'year');
		}

		/**
		 * Add the required header information to the page.
		 */
		protected function addHeader() {
			Administration::instance()->Page->addScriptToHead(URL . '/extensions/datetime/assets/jquery-ui.js', 100, true);
			Administration::instance()->Page->addScriptToHead(URL . '/extensions/recurringdatetime/assets/recurringdatetime.js', 201, false);
			Administration::instance()->Page->addStylesheetToHead(URL . '/extensions/datetime/assets/datetime.css', 'screen', 202, false);
		}

		/**
		 * Add the title. We overload this so that we don't offer help. We are doing
		 * it differently and the help is inaccurate for us.
		 *
		 * @param XMLElement &$wrapper
		 *	the parent element of the xml document to add this to.
		 */
		protected function addTitle(&$wrapper) {
			$wrapper->setValue($this->get('label'));
		}
	
		/**
		 * Add the recurrence selector to the input xml element. consruct the selector
		 * based on the input form submission data. If a selection has been made, then
		 * reflect this in the current display.
		 *
		 * @param XMLElement &$wrapper
		 *	the xml element to append this to.
		 * @param array $data
		 *	the form data to process.
		 * @param number $index
		 *	the index of this date instance.
		 * @return XMLElement
		 *	return the constructed xml element.
		 */
		protected function addPublishRecurrenceSelector(XMLElement &$wrapper, array $data, $index) {
			if (!is_int($index)) {
				die(__(sprintf('Passed %s as third argument to %s expecting integer on line: %d of file: %s', $index, __FUNCTION__, __LINE__, __FILE__)));
			}
			$fields = array();
			$selector = null;
			if($data != null and isset($data['unit']) and is_array($data['unit'])) {
				// the option is selected when its current index unit value equals the current option
				$selector = create_function('$data, $option, $index', 'return $data[\'unit\'][$index - 1] == $option;');
			} elseif($data != null and isset($data['unit'])) {
				$selector = create_function('$data, $option, $index', 'return $data[\'unit\'] == $option;');
			} else {
				// since no interval has been selected then default to none
				$selector = create_function('$data, $option, $index', 'return $option == \'never\';');
			}
			foreach($this->options() as $option) {
				// only prepend every to the display for non 'never' enrties.
				$fields[$option] = array($option, $selector($data, $option, $index), __(( $option == 'never' ? '' : 'Every ') . ucwords($option)), '', '', '');
			}
			$span = new XMLElement('span', null, array('class' => 'interval'));
			$span->appendChild(new XMLElement('em', __('repeat'), array()));
			$select = Widget::Select($this->getFieldName() . '[unit][]', $fields);
			$span->appendChild($select);
			$wrapper->appendChild($span);
			return $span;
		}

		/**
		 * Add the content area to the inpt xml element. Construct the text area
		 * based on the input form submission data.
		 *
		 * @param XMLElement &$wrapper
		 *	the xml element to append this to.
		 * @param array $data
		 *	the form data to process.
		 * @param number $index
		 *	the index of this date instance.
		 * @return XMLElement
		 *	return the constructed xml element.
		 */
		protected function addPublishContent(XMLElement &$wrapper, array $data, $index) {
			if (!is_int($index)) {
				die(__(sprintf('Passed %s as third argument to %s expecting integer on line: %d of file: %s', $index, __FUNCTION__, __LINE__, __FILE__)));
			}
			$span = new XMLElement('span', null, array('class' => 'content'));
			$span->appendChild(new XMLElement('em', __('content'), array()));
			$content = Widget::Textarea($this->getFieldName() . '[content][]', 5, 60, is_array($data['content']) ? $data['content'][$index - 1] : $data['content']);
			$span->appendChild($content);
			$wrapper->appendChild($span);
			return $span;
		}
	
		/**
		 * Displays publish panel in content area.
		 *
		 * @param XMLElement $wrapper
		 *	the XMLElement to which the display of this will be appended.
		 * @param mixed $data (optional)
		 *	any existing data for this entry. this defaults to null.
		 * @param mixed $flagWithError (optional)
		 *	the error structure to append to should an error occur. this defaults to
		 *	null.
		 * @param string $fieldnamePrefix (optional)
		 *	text to prepend to the field name of this in the display. this defaults
		 *	to null.
		 * @param string $fieldnameSuffix (optional)
		 *	text to append to the field name of this in the display. this defaults
		 *	to null.
		 */
		function displayPublishPanel(XMLElement &$wrapper, array $data=null, $flagWithError=null, $fieldnamePrefix=null, $fieldnameSuffix=null) {
			$this->addHeader();
			// add the additional header for this.
			Administration::instance()->Page->addStylesheetToHead(URL . '/extensions/recurringdatetime/assets/recurringdatetime.css', 'screen', 202, false);
			$this->addTitle($wrapper);

			if ($data == null) {
				$data = array();
			}
			$this->ensureArrayValues($data);
			// have to override the default styles applied by the field manager/field classes
			// so that this appears as the datetime field for css and javascript.
			$wrapper->setAttribute('class', 'field field-datetime ui-sortable');
			$count = 1;
			// we always want to print at least one instance, even when data is empty.
			do {
				$label = $this->addPublishLabel($wrapper, $data, $count);
				$this->addPublishStart($label, $data, $count);
				$label = $this->addPublishLabel($wrapper, $data, $count);
				$this->addPublishEnd($label, $data, $count);
				$label = $this->addPublishLabel($wrapper, $data, $count);
				$this->addPublishRecurrenceSelector($label, $data, $count);
				$label = $this->addPublishLabel($wrapper, $data, $count);
				$this->addPublishContent($label, $data, $count);
				$label = $this->addPublishLabel($wrapper, $data, $count);
				$this->addPublishSettings($label, $count);
			} while($count++ < count($data['start']));
			$this->addPublishNewLink($wrapper);
		}

		/**
		 * Creates database field table.
		 *
		 * @return boolean
		 *	true if the creation was successful, false otherwise.
		 */
		function createTable() {
			return Symphony::Database()->query(
				"CREATE TABLE IF NOT EXISTS `tbl_entries_data_" . $this->get('id') . "` (
					`id` int(11) unsigned NOT NULL auto_increment,
					`entry_id` int(11) unsigned NOT NULL,
					`start` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
					`end` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
					`unit` enum('year', 'month', 'fortnight', 'week', 'day', 'never') NOT NULL DEFAULT 'never',
					`content` text DEFAULT '',
					`interval` int DEFAULT 1,
				PRIMARY KEY (`id`),
				KEY `entry_id` (`entry_id`)
				);"
			);
		}
	
		/**
		 * Prepares field values for database. This create an multidimensional
		 * array structure. The keys in the top-level array are the column
		 * names. The values are each an array of values for that column.
		 *
		 * @param array $data
		 *	the form input data to process.
		 * @param mixed $status
		 *	the status to return from this function.
		 * @param boolean $simulate (optional)
		 *	true if the processing should be simulated, false otherwise. this
		 *	defaults to false and is ignored in this particular implementation.
		 * @param mixed $entry_id (optional)
		 *	the id of the entry in the database to make.
		 * @return array[string]
		 *	the processed data as an array structured appropriately for insertion
		 *	into the database.
		 */
		function processRawFieldData(array $data, &$status, $simulate=false, $entry_id=null) {
			if (!is_bool($simulate)) {
				die(__(sprintf('Passed %s as third argument to %s expecting boolean on line: %d of file: %s', $simulate, __FUNCTION__, __LINE__, __FILE__)));
			}
			$fields = parent::processRawFieldData($data, $status, $simulate, $entry_id);
			// if the parent returns no fields, we do the same.
			if($fields == null) {
				return null;
			}
			// add additional arrays for the unit and interval fields
			$fields['unit'] = array();
			// because we don't currently ask about interval information in the gui
			// we just use the setting for the interval column from mysql which is 1.
			$fields['interval'] = array();
			$fields['content'] = array();

			for($i = 0; $i < count($data['start']); $i++) {
				if(!empty($data['start'][$i])) {
					// append the values of the current element of the form data to the
					// array structure
					$fields['unit'][] = $data['unit'][$i];
					// we don't currently ask for interval information. it is always 1
					// which is what we set the default to be for this column.
					$fields['interval'][] = 1;
					$fields['content'][] = $this->cleanValue($data['content'][$i]);
				}
			}
			return $fields;
		}

		/**
		 * Add a formatted date-time element to the input xml element. For every repetition
		 * between the start and end time we add another entry with the time of that repetition.
		 *
		 * @param XMLElement $wrapper
		 *	the xml element to append the formatted date element to.
		 *	data from the input data
		 * @param number $index
		 *	the index of the current entry
		 * @param array $entry
		 *	the start and end entry array.
		 * @return XMLElement
		 *	the constructed date element.
		 */
		protected function addFormattedDateTime(XMLElement &$wrapper, $index, array $entry) {
			if (!is_int($index)) {
				die(__(sprintf('Passed %s as second argument to %s expecting integer on line: %d of file: %s', $index, __FUNCTION__, __LINE__, __FILE__)));
			}
			$date = new XMLElement('date');
			$date->setAttribute('timeline', $index);
			// set the default atrtribute type to exact
			$date->setAttribute('type', 'exact');
			$this->addFormattedTime($date, 'start', strtotime($entry['start']));
			// sentinal time which means end hasn't been set and thus
			// there cannot be any end time or repetitions
			if($entry['end'] == "0000-00-00 00:00:00") {
				$wrapper->appendChild($date);
				return $date;
			}
			// if there is no repetition unit then simply add the end date
			// and return
			if ($entry['unit'] == 'never') {
				$this->addFormattedTime($date, 'end', strtotime($entry['end']));
				// over write the default type as this is a range.
				$date->setAttribute('type', 'range');
				$wrapper->appendChild($date);
				return $date;
			}
			$date->setAttribute('type', 'recurrant');
			// this is a holdover for pre 5.3.0 versions of php that do not have the inbuilt
			// DatePeriod and DateInterval objects which are designed exactly for this stiuation
			for($current = new DateTime($entry['start']); $current <= new DateTime($entry['end']); $current->modify('+' . $entry['interval'] . ' ' . $entry['unit'])) {
				// because we are stuck with php 5.2 we have to format the string as a unix timestamp as
				// the direct accessor doesn't exist
				$recurrence = $this->addFormattedTime($date, 'recurrence', $current->format('U'));
				$this->addFormattedContent($recurrence, $entry['content']);
			}
			$this->addFormattedTime($date, 'end', strtotime($entry['end']));
			$wrapper->appendChild($date);
			return $date;
		}

		/**
		 * Add the formatted content xml element to the input parent xml element.
		 *
		 * @param XMLElement $wrapper
		 *	the xml element to append the formatted content xml to.
		 * @param string $content
		 *	the content to append to the wrapper
		 * @return XMLElement
		 *	the constructed content xml element.
		 */
		protected function addFormattedContent(XMLElement &$wrapper, $content) {
			if (!is_string($content)) {
				die(__(sprintf('Passed %s as second argument to %s expecting string on line: %d of file: %s', $content, __FUNCTION__, __LINE__, __FILE__)));
			}
			$content = new XMLElement('content', $content);
			$wrapper->appendChild($content);
			return $content;
		}

		/**
		 * Generate data source output. Given a simple array structure that maps columns
		 * to arrays of their values, construct the xml output of this field.
		 *
		 * @param XMLElement $wrapper
		 *	the xml element to append the formatted content xml to.
		 * @param array $data
		 *	the aarray of data to transform to entries and add as formatted date times.
		 * @param boolean $encode
		 *	true if the data is to be encoded.
		 */
		public function appendFormattedElement(XMLElement &$wrapper, array $data, $encode=false) {
			if (!is_bool($encode)) {
				die(__(sprintf('Passed %s as third argument to %s expecting boolean on line: %d of file: %s', $encode, __FUNCTION__, __LINE__, __FILE__)));
			}
			$this->ensureArrayValues($data);
			$datetime = new XMLElement($this->get('element_name'));
			$transformed = $this->toEntryArray($data);

			// generate XML
			foreach ($transformed as $index => $entry) {
				$this->addFormattedDateTime($datetime, $index, $entry);
			}
			// append date and time to data source
			$wrapper->appendChild($datetime);
		}
	}
