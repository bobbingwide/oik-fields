<?php

/**
 * @package oik-fields
 * @copyright (C) Copyright Bobbing Wide 2023
 *
 * Unit tests to load all the PHP files for PHP 8.2
 */
class Tests_load_php extends BW_UnitTestCase
{

	/**
	 * set up logic
	 *
	 * - ensure any database updates are rolled back
	 * - we need oik-googlemap to load the functions we're testing
	 */
	function setUp(): void 	{
		parent::setUp();
	}

	function test_load_admin_php() {
		oik_require( 'admin/oik-activation.php', 'oik-fields');
		$this->assertTrue( true );
	}



	function test_load_includes_php() {
		oik_require( 'includes/class-oik-fields-groups.php', 'oik-fields');
		oik_require( 'includes/class-oik-fields-groups-taxonomy.php', 'oik-fields');
		oik_require( 'includes/oik-fields.inc', 'oik-fields');
		oik_require( 'includes/oik-fields-serialized.php', 'oik-fields');
		oik_require( 'includes/oik-fields-validation.php', 'oik-fields');
		oik_require( 'includes/oik-fields-virtual.php', 'oik-fields');
		oik_require( 'includes/oik-fields-virtual-author.php', 'oik-fields');
		oik_require( 'includes/oik-fields-virtual-google-map.php', 'oik-fields');
		oik_require( 'includes/oik-form-fields.php', 'oik-fields');
		$this->assertTrue( true );
	}


	function test_load_shortcodes_php() {
		oik_require( 'shortcodes/oik-field.php', 'oik-fields');
		oik_require( 'shortcodes/oik-fields.php', 'oik-fields');
		oik_require( 'shortcodes/oik-group.php', 'oik-fields');
		oik_require( 'shortcodes/oik-new.php', 'oik-fields');
		oik_require( 'shortcodes/oik-related.php', 'oik-fields');
		$this->assertTrue( true );
	}

	function test_load_plugin_php() {
		oik_require( 'oik-fields.php', 'oik-fields');
		$this->assertTrue( true );
	}
}



