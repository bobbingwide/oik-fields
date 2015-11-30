<?php // (C) Copyright Bobbing Wide 2015

/**
 * Implement [bw_group] shortcode to count posts by group
 * 
 * Implement advanced counting to show the count of posts grouped by a particular field
 * 
 * With a shortcode something like this
 * 
 * [bw_group post_type=competitor meta_key=_event meta_value=52 field=playing_status ]
 *
 * @TODO Count the number of different values for a group of fields
 * 
 * {@link http://geekgirllife.com/alphabetical-index-of-posts-in-wordpress/}
 *
 *
 *	 
 */
function bw_group( $atts=null, $content=null, $tag=null ) {
	$class_name = bw_group_load_class( $atts );
	if ( class_exists( $class_name ) ) {
		$groups = new $class_name();
		$groups->process( $atts );
	} else {
		p( "Class $class_name missing" );
	}	
	return( bw_ret() );
}

/**
 * Return the class to handle this request
 *
 * @param array $atts
 * @return string class name to handle the request 
 */
function bw_group_load_class( $atts ) {
	$taxonomy = array( "class" => "OIK_fields_groups_taxonomy"
									, "plugin" => "oik-fields"
									, "file" => "includes/class-oik-fields-groups-taxonomy.php"
									);
	$select = array( "class" => "OIK_fields_groups_select"
								, "plugin" => "oik-fields"
								, "file" => "includes/class-oik-fields-groups-select.php" 
								);
	$default = array( "class" => "OIK_fields_groups" 
								, "plugin" => "oik-fields"
								, "file" => "includes/class-oik-fields-groups.php"
								);								
	$groups = array( "taxonomy" => $taxonomy, "select" => $select );
	$groups = apply_filters( "oik_query_field_groups", $groups );
	//print_r( $groups );
	// OIK_autoload->loads( $groups );
	$field = bw_array_get( $atts, "field", null );
	$field_type = bw_query_field_type( $field );
	$class = bw_array_get( $groups, $field_type, $default );
	//print_r( $class );
	$class_name = $class['class' ];
	do_action( "oik_autoload", $class_name, $groups );
	
	
	if ( !class_exists( $class_name ) ) {
		$file = oik_path( $class['file'], $class['plugin'] );
		if ( file_exists( $file ) ) {
			require_once( $file );
		}
	}
	return( $class_name );
}


function bw_group__help( $shortcode="bw_group" ) {
	return( "Display summary of selected items" );
}

function bw_group__syntax( $shortcode="bw_group" ) {
	$syntax = array( "field" => bw_skv( null, "<i>Field</i>", "Field or taxonomy to group by" )
								, "uo" => bw_skv( "u",	"o|d|s", "Display type" )
								);
	return( $syntax );
}
		

