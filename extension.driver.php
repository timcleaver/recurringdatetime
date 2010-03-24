<?php
	/**
	 * An extension to provide a field that allows users to specify a recurring
	 * datetime.
	 *
	 * @author Timothy Cleaver
	 * @version 0.0.1
	 * @since 2.07
	 */
	Class extension_recurringdatetime extends Extension {
		/**
		 * Accessor for the meta-data of this extension.
		 *
		 * @return array[string]mixed
		 *	an array structure reflecting the meta-data of this extension.
		 */
		public function about() {
			return array(
				'name'			=> 'Recurring Date-Time',
				'version'		=> '0.0.1',
				'release-date'	=> '2010-03-4',
				'author'		=> array(
					'name'			=> 'Timothy Cleaver',
					'website'		=> 'http://symphony-cms.com/',
					'email'			=> 'tim@randb.com.au'
				),
				'type'			=> 'Field, Interface',
				'description'	=> 'A field for the specification of recurring date-times',
				'compatibility' => array(
				    '2.0.7' => true
				)
			);
		}

		/**
		 * Uninstall this extension. This will remove any trace of the existence
		 * of this function from the database.
		 */
		public function uninstall() {
			Symphony::Database()->query("DROP TABLE `tbl_fields_recurringdatetime`");
		}

		/**
		 * Install this extension. Constructs the required tables in the database
		 * to make symphony aware of this extension and allow it to instantiate its
		 * properties.
		 *
		 * @return boolean
		 *	true on success, false otherwise.
		 */
		public function install() {
			return Symphony::Database()->query("
				CREATE TABLE IF NOT EXISTS `tbl_fields_recurringdatetime` (
					`id` int(11) unsigned NOT NULL auto_increment,
					`field_id` int(11) unsigned NOT NULL,
					`format` text,
					`prepopulate` enum('yes','no') NOT NULL default 'yes',
					`allow_multiple_dates` enum('yes','no') NOT NULL default 'yes',
					PRIMARY KEY  (`id`),
					KEY `field_id` (`field_id`)
				)
			");
		}
	}
	
?>
