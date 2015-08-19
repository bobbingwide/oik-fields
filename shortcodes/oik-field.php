<?php // (C) Copyright Bobbing Wide 2013, 2014
/**
 * Implement the [bw_field] shortcode
 * 
 * @uses get_post_meta, bw_format_meta
 * 
 * Built from bw_metadata() to re-introduce support for [bw_field name=field] which was used by oik-tunes when creating an oik-track
 * 
 * Since "name" is used by get_posts() we had to change [bw_pages] to use fields= rather than name= when specifying which fields to format. 
 * We also changed [bw_fields] to be consistent but discovered a problem 10 months later which we're now addressing by creating 
 * a separate function for [bw_field]. 
 * We will also (re)introduce logic to not display the label and separator by default for bw_field. 
 * 
 * @TODO resolve quandary - when the field names are specified are we allowed to override the #theme setting? ie. Ignore #theme => false ?
 * Answer: For "taxonomy" type fields the #theme setting may not be defined. BUT these fields have to be specified separately anyway.
 *
 * 
 * @param array $atts - shortcode parameters
 * @param string $content - not expected
 * @param string $tag - shortcode tag when invoked for shortcode expansion
 * @return string formatted metadata
 * 
 */
function bw_field( $atts=null, $content=null, $tag=null ) {
  //bw_trace2( );
  $post_id = bw_array_get_dcb( $atts, "id", null, "bw_current_post_id" );
  //bw_backtrace();
  //p( "Fields for $post_id "); 
  $name = bw_array_get_from( $atts, "name,0", NULL );
  if ( $name ) {
    unset( $atts['name'] );
  } else {
    $name = bw_array_get( $atts, "fields", null );
  }  
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
           //bw_trace2( $post_meta );
           $customfields = array( $name => $post_meta ); 
           //bw_format_meta( $customfields );
           bw_format_field( $customfields ); 
         }  
      } else {
        bw_theme_object_property( $post_id, $name, $atts );
      } 
    }
  } else {
    bw_trace2( "Invalid use of $tag. No field name to process for $post_id" );
  }
  return( bw_ret() );
}
 
/**
 * Implement help hook for [bw_field]
 */
function bw_field__help( $shortcode="bw_field" ) {
  return( "Format custom fields without labels" ); 
}

/**
 * Implement syntax hook for [bw_field]
 * 
 * Note: For [bw_fields] the parameter for listing the field names is now fields= since name= is used by get_posts()
 * We continue to support name= for backward compatibility with content created by oik-tunes.
 
 */
function bw_field__syntax( $shortcode="bw_field" ) {
  $syntax = array( "fields" => bw_skv( null, "<i>field names</i>", "CSV list of field names. Default: all registered fields" )
                 , "name,0" => bw_skv( null, "<i>field name(s)</i>", "CSV list of field names." )
                 , "id" => bw_skv( null, "<i>ID</i>", "Post ID to use to access the fields" )
                 ); 
  return( $syntax );
}

