<?php // (C) Copyright Bobbing Wide 2013, 2014
/**
 * Implement the [bw_field] and [bw_fields] shortcode
 * 
 * @uses get_post_meta, bw_format_meta
 * 
 * @TODO resolve quandary - when the field names are specified are we allowed to override the #theme setting? ie. Ignore #theme => false ?
 * 
 * 
 * @param array $atts - shortcode parameters
 * @param string $content - not expected
 * @param string $tag - shortcode tag when invoked for shortcode expansion
 * @return string formatted metadata
 * 
 */
function bw_metadata( $atts=null, $content=null, $tag=null ) {
  //bw_trace2( );
  $post_id = bw_array_get_dcb( $atts, "id", null, "bw_current_post_id" );
  //bw_backtrace();
  //p( "Fields for $post_id "); 
  $name = bw_array_get( $atts, "fields", NULL );
  if ( null == $name ) {
    $names = bw_get_field_names( $post_id );
    
  } else {
    $name = wp_strip_all_tags( $name, TRUE );
    $names = explode( ",", $name );
    
  }
  if ( count( $names ) ) {
    //oik_require( "bobbforms.inc" );
    foreach ( $names as $name ) {
      if ( bw_get_field_data_arg( $name, "#theme", true ) ) {
        $post_meta = get_post_meta( $post_id, $name, FALSE );
        bw_trace2( $post_meta );
        $customfields = array( $name => $post_meta ); 
        bw_format_meta( $customfields );
      }  
    }
  } else {
    p( "Invalid use of $tag. No field names to process for $post_id" );
  }
  return( bw_ret() );
}
 
/**
 * Implement help hook for [bw_field]
 */
function bw_field__help( $shortcode="bw_field" ) {
  return( "Format custom fields" ); 
}

/**
 * Implement help hook for [bw_fields]  - synonum for [bw_field]
 */
function bw_fields__help( $shortcode="bw_fields" ) {
  return( "Format custom fields" ); 
}

/**
 * Implement syntax hook for [bw_field]
 * 
 * Note: The parameter for listing the field names is now field= since name= is used by get_posts()
 */
function bw_field__syntax( $shortcode="bw_field" ) {
  $syntax = array( "field" => bw_skv( null, "<i>field names</i>", "CSV list of field names. Default: all registered fields" )
                 , "id" => bw_skv( null, "<i>ID</i>", "Post ID to use to access the fields" )
                 ); 
  return( $syntax );
}

/**
 * Implement syntax hook for [bw_fields] 
 */
function bw_fields__syntax( $shortcode="bw_fields" ) {
  return( bw_field__syntax( $shortcode ));
}  
