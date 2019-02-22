<?php // (C) Copyright Bobbing Wide 2013-2017


/**
 * Determine if the field has a value
 *
 * @param mixed $post_meta
 * @param string $name the field name - just in cases
 * @return bool true if we think the field has a value
 */

function bw_field_has_value( $post_meta, $name ) {
	if ( is_array( $post_meta ) ) {
		if ( count( $post_meta ) ) {
			$field_has_value = $post_meta[0];
		} else {
			$field_has_value = false;
		}
	} else {
		$field_has_value = $post_meta;
	}
	return( $field_has_value );

}
/**
 * Implement the [bw_fields] shortcode
 *
 * Display the chosen fields with labels, separator and field values
 *
 * 
 * @uses get_post_meta, bw_format_meta
 * 
 * @TODO resolve quandary - when the field names are specified are we allowed to override the #theme setting? ie. Ignore #theme => false ?
 * Answer: For "taxonomy" type fields the #theme setting may not be defined. BUT these fields have to be specified separately anyway.
 
 * @TODO decide best way to deal with nested usage of this shortcode or multiple usage of this shortcode
 * when the post we're trying to process is different from the main post. 
 * This happens when we are combining information from multiple posts into one output. 
 * Current solution is to not produce anything, not even 'Not single'
 * 
 * @param array $atts - shortcode parameters
 * @param string $content - not expected
 * @param string $tag - shortcode tag when invoked for shortcode expansion
 * @return string formatted metadata
 * 
 */
function bw_metadata( $atts=null, $content=null, $tag=null ) {
	if ( !oik_is_shortcode_expansion_necessary() ) {
		return "Not today thank you.";
	}
  $post_id = bw_array_get( $atts, "id", null );
  if ( $post_id ) {
    $single = true;
  } else { 
    $post_id = bw_current_post_id();
    if ( $post_id === bw_global_post_id() ) {
      $single = true;
    } else {
      $single = is_single( $post_id );
      
    }   
  }
    
  bw_trace2( null, null, true, BW_TRACE_VERBOSE );
  bw_backtrace( BW_TRACE_VERBOSE );
  if ( $single ) {
    //bw_backtrace();
    //p( "Fields for $post_id "); 
    $name = bw_array_get_from( $atts, "fields,0", NULL );
    if ( null == $name ) {
      $names = bw_get_field_names( $post_id );
      
    } else {
      $name = wp_strip_all_tags( $name, TRUE );
      $names = explode( ",", $name );
      
    }
    if ( count( $names ) ) {
      foreach ( $names as $name ) {
				$theme_it = bw_get_field_data_arg( $name, "#theme", true );
        if ( $theme_it ) {
        
          /**
           * We have to cater for "taxonomy" fields as well
           */
          $type = bw_query_field_type( $name );
          if ( $type === "taxonomy" ) {
            // bw_custom_column_taxonomy( $name, $post_id );
            bw_format_taxonomy( $name, $post_id );
          } else { 
            //bw_custom_column_post_meta( $column, $post_id );
						
            $post_meta = get_post_meta( $post_id, $name, FALSE );
            bw_trace2( $post_meta, "post_meta", false, BW_TRACE_VERBOSE );
						if ( false == bw_get_field_data_arg( $name, "#theme_null", true ) ) {
							$theme_it = bw_field_has_value( $post_meta, $name );
						}
						
						if ( $theme_it ) {
							
							$customfields = array( $name => $post_meta ); 
							bw_format_meta( $customfields );
						}
          }  
        } else {
          bw_theme_object_property( $post_id, $name, $atts );
        }  
      }
    } else {
      bw_trace2( "Invalid use of $tag. No field names to process for $post_id", null, true, BW_TRACE_WARNING );
    }
  } else {
    //e( "Not single" );
  }
  return( bw_ret() );
}

/**
 * Implement help hook for [bw_fields] 
 */
function bw_fields__help( $shortcode="bw_fields" ) {
  return( "Format custom fields, with labels" ); 
}

/**
 * Implement syntax hook for [bw_fields]
 * 
 * Note: The parameter for listing the field names is now fields= since name= is used by get_posts()
 * However, in [bw_field] we continue to support the name= parameter for backward compatibility with content generated by oik-tunes
 *
 */
function bw_fields__syntax( $shortcode="bw_fields" ) {
  $syntax = array( "fields,0" => bw_skv( null, "<i>field names</i>", "CSV list of field names. Default: all registered fields" )
                 , "id" => bw_skv( null, "<i>ID</i>", "Post ID to use to access the fields" )
                 ); 
  return( $syntax );
}
