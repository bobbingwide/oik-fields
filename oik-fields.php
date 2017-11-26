<?php
/*
Plugin Name: oik fields
Plugin URI: https://www.oik-plugins.com/oik-plugins/oik-fields
Description:  Field formatting for custom post type meta data, plus [bw_field] & [bw_fields], [bw_new] and [bw_related] shortcodes, and 'virtual' fields
Depends: oik base plugin
Version: 1.50.0
Author: bobbingwide
Author URI: https://www.oik-plugins.com/author/bobbingwide
Text Domain: oik-fields
Domain Path: /languages/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

    Copyright 2011-2017 Bobbing Wide (email : herb@bobbingwide.com )

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2,
    as published by the Free Software Foundation.

    You may NOT assume that you can use any other version of the GPL.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    The license for this software can likely be found here:
    http://www.gnu.org/licenses/gpl-2.0.html

*/

/**
 * Implement "oik_loaded" action for oik-fields
 *
 * Loads the required include files from the oik base plugin then invokes the "oik_fields_loaded" action
 * Plugins dependent upon oik-fields should implement the "oik_fields_loaded" action rather than "oik_loaded"
 * 
 * For performance reasons plugins which are only responsible for formatting fields for display or form input should wait for other actions before they load their code:
 * "oik_pre_theme_field" - load functions used to display fields  
 * "oik_pre_form_field" - load functions used to create form fields
 * 
 * Notes: 
 * - In oik-fields version 1.18 the bw_theme_field() function was implemented in the wrong place. 
 * - In version 1.19 we deliver the include file ( includes/bw_fields.inc ) in both oik and oik-fields, expecting to load it from the oik base plugin.
 * - In version 1.40 and oik v2.3 we're still delivering the file in two places.
 * - In version 1.50, dependent upon oik v3.2.1, we'll use the shared library file "bw_fields"
 */
function oik_fields_init() {
	if ( oik_fields_check_oik_version() ) {
		oik_require( "includes/bw_register.php" );
		oik_require( "includes/bw_metadata.php" );
		oik_require_lib( "bw_fields" );
	} else {
		oik_require( "includes/bw_register.inc" ); // Yes, we know it's deprecated
		oik_require( "bw_metadata.inc" ); // Yes, we know it's deprecated
		oik_require( "includes/bw_fields.inc" ); // Yes, we know it's deprecated
	}
		
  $path = oik_path( "shortcodes/oik-fields.php", "oik-fields" );
  bw_add_shortcode( 'bw_field', 'bw_field', oik_path( "shortcodes/oik-field.php", "oik-fields"));
  bw_add_shortcode( 'bw_fields', 'bw_metadata', $path, false );
  bw_add_shortcode( 'bw_new', 'bw_new', oik_path( "shortcodes/oik-new.php", "oik-fields" ), false);
  bw_add_shortcode( 'bw_related', 'bw_related', oik_path( "shortcodes/oik-related.php", "oik-fields" ), false ); 
	bw_add_shortcode( 'bw_group', 'bw_group', oik_path( "shortcodes/oik-group.php", "oik-fields" ), false );
  add_action( "bw_metadata", "oik_fields_bw_metadata" );
  add_action( "oik_fields_loaded", "oik_fields_oik_fields_loaded", 9 );
	add_filter( "oik_shortcode_atts", "oik_fields_shortcode_atts", 10, 3 );
  ///add_filter( 'no_texturize_shortcodes', "oik_fields_no_texturize_shortcodes" );
  //remove_filter( 'the_content', 'wptexturize' );
  /**
   * Inform plugins that oik-fields has been loaded
   *
   * Some plugins may choose to defer their initialization until they know that oik-fields is active.
   */
  do_action( 'oik_fields_loaded' );
}

/**
 * Checks the required version of oik
 */ 
function oik_fields_check_oik_version( $version="3.2.1") {
	$active_version = oik_version();
  $active = version_compare( $active_version, $version, "ge" ); 
  bw_trace2( $active_version, $version, true );
	return $active;
}

/**
 * Implement "no_texturize_shortcodes" for oik-fields
 *
 * With WordPress 3.9.2 it was possible to pass a parameter of meta_compare=">=" to the [bw_related] shortcode
 * In WordPress 4.0 the wptexturize logic converted the quotes to HTML entities
 * which prevented the shortcode from being parsed correctly.
 * 
 * This logic somehow prevents the 'messing up', which I believe was unintentional... i.e. a bug.
 *
 * @param array - array of shortcodes that should not be texturized
 * @return array - as before with bw_related added
 */
function oik_fields_no_texturize_shortcodes( $shortcodes ) {
  $shortcodes[] = "bw_related";
  return( $shortcodes );
}

/**
 * Implement "oik_shortcode_atts" for oik-fields
 *
 * Converts Standard relational operators into those needed by WordPress's use of MySQL
 *
 * - This code implements a workaround for TRAC #29608
 * - The function is shortcode insensitive. 
 * - It applies for any use of the meta_compare attribute
 * - See {@link https://en.wikipedia.org/wiki/Relational_operator#Standard_relational_operators}
 * - It doesn't cater for "between", where we need to convert it to a meta_query later on
 *
 * @param array $atts shortcode attributes
 * @param string $content 
 * @param string shortcode
 * @return array updated array of $atts
 */
function oik_fields_shortcode_atts( $atts, $content, $tag ) {
	bw_trace2( null, null, true, BW_TRACE_DEBUG );
	$meta_compare = bw_array_get( $atts, "meta_compare", null );
	if ( $meta_compare ) {
		$meta_compare = strtolower( $meta_compare );
		$conversions = array( "eg" => "=" , "ne" => "<>", "gt" => ">", "lt" => "<", "ge" => ">=", "le" => "<" );
		$meta_compare = strtr( $meta_compare, $conversions );
		$atts['meta_compare'] = $meta_compare;
		
		if ( $meta_compare == "between" ) {
			$atts = oik_fields_shortcode_atts_between( $atts, $content, $tag );
		}	
	}
	//bw_trace2();
	return( $atts );
}

/** 
 * Implement "oik_shortcode_atts" for meta_compare=between 
 *
 * This could be a separate filter function
 *
 * @param array $atts shortcode parameters
 * @param string $content 
 * @param string $tag
 * @return updated atts array
 */
function oik_fields_shortcode_atts_between( $atts, $content, $tag ) {
	$meta_key = bw_array_get( $atts, "meta_key", null );
	$meta_value = bw_array_get( $atts, "meta_value", null );
	$and = bw_array_get( $atts, "and", null );
	
	if ( $meta_key && $meta_value && $and ) {
		$meta_query = array();
		$meta_query[] = array( "key" => $meta_key
												 , "value" => array( $meta_value, $and )
												 , "compare" => "BETWEEN"
												 );
		$atts['meta_query'] = $meta_query;
	}
	return( $atts );
}


/**
 * Return the value from a fields #args array, setting the default if not defined
 *
 * @param string $field - the field name - which is expected to be defined in $bw_fields
 * @param string $key - the key to the #args array. e.g. #theme
 * @param mixed $default - the default value
 * @return mixed - the value found/defaulted or false if the field is not defined
 */
function bw_get_field_data_arg( $field, $key, $default=true ) {
  global $bw_fields;
  $value = bw_array_get( $bw_fields, $field, false );
  if ( $value ) {
    $value = bw_array_get( $value["#args"], $key, $default );
  }  
  return( $value );
}

/**
 * Return the array of field names for the selected post
 *
 * @param ID $post_id 
 * @return array - registered field names for the post type
 */
function bw_get_field_names( $post_id ) {
  $post_type = get_post_type( $post_id );
  global $bw_mapping;
  $names= array(); 
  //bw_trace( $bw_mapping, __FUNCTION__, __LINE__, __FILE__, "bw_mapping" );
  if ( isset(  $bw_mapping['field'][$post_type] )) {
    foreach ( $bw_mapping['field'][$post_type] as $field ) {
      $names[] = $field;
    }
  }
  return( $names );
}

/**
 * Implement "oik_pre_theme_field" for oik-fields 
 */
function oik_fields_pre_theme_field() {
  oik_require( "includes/oik-fields.inc", "oik-fields" );
	oik_require( "includes/oik-fields-serialized.php", "oik-fields" );
}

/**
 * Implement "oik_pre_form_field" for oik_fields
 */
function oik_fields_pre_form_field() {
  oik_fields_pre_theme_field();
  oik_require( "includes/oik-form-fields.php", "oik-fields" );
}

/**
 * Implement "bw_validate_functions" filter for oik-fields   
 *
 * @param array $fields - array of fields
 * @return filtered array of fields
 */
function oik_fields_validate_functions( $fields ) {
  oik_require( "includes/oik-fields-validation.php", "oik-fields" );
  //oik_fields_validation_loaded();
  return ( $fields ); 
}

/**
 * Template tag to return the header image for a specific page
 *
 * If none is specified then it doesn't return anything
 * so should we then call custom_header logic?
 * 
 * @TODO - retest since "name=" is now "fields="
 */
if ( !(function_exists( "bw_header_image" ))) {
  function bw_header_image() {
    oik_require( "shortcodes/oik-fields.php", "oik-fields" );
    return( bw_metadata( array( "name" => "bw_header_image" )));
  } 
}

/**
 * Simple wrapper to the_meta() for displaying the meta data 
 * The best way of displaying this would be to put it into a text widget
 * then it would work regardless of the content being displayed
 *
 */
function bw_meta( $atts = null ) {
  the_meta();
  return;  
}

/**
 * Implement "oik_admin_menu" for oik-fields
 *
 * Sets the plugin server.
 */
function oik_fields_admin_menu() {
  oik_register_plugin_server( __FILE__ );
}

/**
 * Dependency checking for oik-fields. 
 * 
 * Version | Dependency
 * ------- | ---------------
 * v1.18   | oik v2.0
 * v1.19   | oik v2.1-alpha
 * v1.20   | is needed with oik v2.1-beta.0102 - this dependency checking is not yet developed.
 * v1.31   | has same code for bw_fields.inc as oik v2.1-beta.0121
 * v1.35   | oik v2.2-beta
 * v1.36   | oik v2.2
 * v1.39   | has been tested with oik v2.3 but does not require it
 * v1.50.0 | expects oik v3.2.1 but will mostly work with an earlier version.
 */ 
function oik_fields_activation() {
  static $plugin_basename = null;
  if ( !$plugin_basename ) {
    $plugin_basename = plugin_basename(__FILE__);
    add_action( "after_plugin_row_oik-fields/oik-fields.php", "oik_fields_activation" ); 
    if ( !function_exists( "oik_plugin_lazy_activation" ) ) { 
      require_once( "admin/oik-activation.php" );
    }
  }  
  $depends = "oik:3.2.1";
  oik_plugin_lazy_activation( __FILE__, $depends, "oik_plugin_plugin_inactive" );
}

/**
 * Implement "bw_field_functions" filter
 * 
 * Add support for the "_" value indicating the placement of fields.
 * This is used in the format= parameter for [bw_pages]
 * 
 * @param array $fields - array of field functions
 * @return array $fields - original array with "_" field formatting added
 *
 */
function oik_fields_field_functions( $fields ) {
  $fields['_'] = "bw_field_function_fields"; 
  //bw_trace2( $fields, "fields");
  return( $fields ); 
}

/**
 * Format the fields (format="_") 
 *
 * This should just be a case of calling the bw_metadata() function for [bw_fields] shouldn't it? 
 * This makes use of the fields= parameter passed from the invoking shortcode.
 * e.g. [ bw_pages format="T _" fields="_dtib_rating" ]
 */
function bw_field_function_fields( $post, &$atts, $f ) {
  //bw_trace2();
  $atts['id'] = $post->ID;
  oik_require( "shortcodes/oik-fields.php", "oik-fields" );
  e( bw_metadata( $atts ) );
} 

/** 
 * Implement "bw_metadata" action for bw_fields.
 * 
 * @TODO **?** should this be a filter? 
 */
function oik_fields_bw_metadata( $post_id ) {
  $atts['id'] = $post_id;
  oik_require( "shortcodes/oik-fields.php", "oik-fields" );
  e( bw_metadata( $atts ) );
}

/**
 * Implement "oik_fields_loaded" action for oik-fields
 *
 * Here we register the virtual fields that may be used directly in the bw_fields shortcode
 * or can be registered as a virtual field of a post type
 * For example the "_file_size" virtual field could be applied to an attachment such as a PDF
 *
 * The "featured" image is the full size image
 * The "thumbnail" is the thumbnail sized version of the featured image
 *
 */
function oik_fields_oik_fields_loaded() {
  $field_args = array( "#callback" => "bw_fields_get_file_size"
                     , "#parms" => "_wp_attached_file" 
                     , "#plugin" => "oik-fields"
                     , "#file" => "includes/oik-fields-virtual.php"
                     , "#form" => false
                     , "#hint" => "virtual field"
                     ); 
  bw_register_field( "file_size", "virtual", "File size", $field_args );
  
  $field_args = array( "#callback" => "bw_fields_get_dimensions"
                     , "#parms" => "_wp_attachment_metadata" 
                     , "#plugin" => "oik-fields"
                     , "#file" => "includes/oik-fields-virtual.php"
                     , "#form" => false
                     , "#hint" => "virtual field"
                     ); 
  bw_register_field( "dimensions", "virtual", "Dimensions", $field_args );
  
  $field_args = array( "#callback" => "bw_fields_get_featured_image"
                     , "#parms" => "_thumbnail_id" 
                     , "#plugin" => "oik-fields"
                     , "#file" => "includes/oik-fields-virtual.php"
                     , "#form" => false
                     , "#hint" => "virtual field"
                     , "#label" => false
                     ); 
  bw_register_field( "featured", "virtual", "Featured image", $field_args );
  
  $field_args[ "#callback" ] = "bw_fields_get_thumbnail";
  bw_register_field( "thumbnail", "virtual", "Thumbnail", $field_args );
	
	
  $field_args = array( "#callback" => "bw_fields_get_google_map"
                     , "#parms" => "_post_code,_lat,_long" 
                     , "#plugin" => "oik-fields"
                     , "#file" => "includes/oik-fields-virtual-google-map.php"
                     , "#form" => false
                     , "#hint" => "virtual field"
                     ); 
	bw_register_field( "googlemap", "virtual", "Google map", $field_args );
} 

/**
 * Implement "oik_query_field_types" to return the field types supported by oik-fields
 * 
 * @param array $field_types - array of field types
 * @return array - updated with our values 
 */
function oik_fields_query_field_types( $field_types ) {
  $field_types['text'] = __( 'Text' );
  $field_types['textarea'] = __( 'Textarea' );
  $field_types['select'] = __( 'Select' );
  $field_types['currency'] = __('Currency' );
  $field_types['numeric'] = __( 'Numeric' );
  $field_types['date'] = __( 'Date' );
  $field_types['noderef'] = __( 'noderef - internal link' );
  $field_types['email'] = __( 'Email address' );
  $field_types['URL'] = __( 'URL - external link' );
  $field_types['checkbox'] = __( 'Check box' );
  $field_types['virtual'] = __( 'Virtual' );
  $field_types['sctext'] = __( 'Text with shortcodes' );
  $field_types['sctextarea'] = __( 'Textarea with shortcodes' );
	$field_types['serialized'] = __( 'PHP serialized' );
  return( $field_types );
} 

/**
 * Return the meta_value to use - either the value of the current post or the value of a post meta field of type noderef
 *
 * @param string $meta_value - the specified meta_value 
 * @return ID - the post ID to use 
 */
function oik_fields_default_meta_value_meta_key( $meta_value ) {
  $field_type = bw_query_field_type( $meta_value );
  if ( $field_type == "noderef" ) {
    $meta_value = get_post_meta( bw_global_post_id(), $meta_value, true ); 
  } else {
    bw_trace2( $meta_value, "defaulting meta_value" );
    $meta_value = bw_global_post_id();
  }
  return( $meta_value );
}
 
/**
 * Implement "oik_default_meta_value_noderef" filter for noderef fields
 * 
 * @param string $meta_value - the given value for meta_value= parameter - may be null
 * @param array $atts - other parameters
 * @return string $meta_value - default value if original was no good
 */ 
function oik_fields_default_meta_value_noderef( $meta_value, $atts ) { 
	//bw_trace2();
  if ( !$meta_value ) {
    $meta_value = bw_global_post_id();
  }
  // Let's check for a numeric value. If it's a 
  if ( is_numeric( $meta_value ) ) {
    // They seem to know what they're looking for 
  } else {
    $meta_value = oik_fields_default_meta_value_meta_key( $meta_value ); 
  }
	//bw_trace2( $meta_value, "meta_value", false );
  return( $meta_value );
}  

/**
 * Function to initialise oik-fields when first loaded
 */
function oik_fields_plugin_loaded() {
  add_action( "oik_loaded", "oik_fields_init" );
  add_action( "admin_notices", "oik_fields_activation" );
  add_action( "oik_admin_menu", "oik_fields_admin_menu" );
  add_filter( "bw_field_functions", "oik_fields_field_functions" );
  add_action( "oik_pre_theme_field", "oik_fields_pre_theme_field" ); 
  add_filter( "bw_validate_functions", "oik_fields_validate_functions" );
  add_filter( "oik_query_field_types", "oik_fields_query_field_types" );
  add_filter( "oik_default_meta_value_noderef", "oik_fields_default_meta_value_noderef", 10, 2 );
  add_action( "oik_pre_form_field", "oik_fields_pre_form_field" );
	//add_filter( "oik_query_field_groups", "oik_fields_query_field_groups" );
}

/**
 * Function to invoke when plugin file loaded
 */
oik_fields_plugin_loaded();



