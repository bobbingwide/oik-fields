<?php // (C) Copyright Bobbing Wide 2014

/**
 * Implement [bw_related] shortcode
 *
 * To take out the hard work listing items which are related to the current item through "noderef" type fields
 *
 * Here we have the original shortcode from post ID 401 which is an instance of an "oik_shortcode"
 *
 *  <h3>See also</h3>
 *  [bw_list post_type=shortcode_example meta_key=_sc_param_code meta_value=401]
 *
 * The lookup performed in bw_list is for posts of type "shortcode_example" with meta fields with a name of "_sc_param_code" that have a value of 401.
 * In other words it lists all the "shortcode examples" which refer to this particular post. 
 *
 * _sc_param_code is the name given to a noderef type field which references the "shortcode_example" post type.
 * We 'hijacked' this field when defining the fields for the "shortcode_example" custom post type.
 * It had previously been used for linking shortcode parameters to the shortcode. 
 * 
 * Rather than having to get the meta_value right in each usage of the shortcode we would like it to default to the current post id.
 * We're going to change bw_get_posts() to achieve this for ALL post related shortcodes.
 *
 * BUT first we want to create the [bw_related] shortcode just to see how easy it is.
 * ie. We'll code [bw_related post_type=shortcode_example  meta_key=_sc_param_code] 
 * omitting the meta_value parameter.
 *
 * This shortcode can therefore also be used to lookup the $bw_fields / $bw_mapping table to find
 * references to posts of the current post_type and therefore find the meta_key names to use
 * thus enabling the shortcode to be coded as [bw_related] 
 * 
 * Now wouldn't that be nice? 
 *
 * Note: the post_type defaults to the current post type... but that's not what we want
 * except when we want to find links to other posts of the same type. We still need to know the value for the meta_key. 
 *
 * We do need to know the current post type in order to find which fields refer to it.
 *
 * 
 */
function bw_related( $atts=null, $content=null, $tag=null ) {
  oik_require( "shortcodes/oik-list.php" );
  oik_require( "includes/bw_posts.inc" );
  $post_type = bw_array_get( $atts, "post_type", null );
  $meta_key = bw_array_get( $atts, "meta_key", null );
  if ( $post_type && $meta_key ) {
    // they've specified the post type and meta_key so we don't have to look for it
  } else {
    $meta_key = bw_query_related_fields( $atts );
  }
  $atts['meta_value'] = bw_related_meta_value( $atts, $meta_key );
  return( bw_list( $atts ) );  
}

/**
 * Query the field type given the field name
 *
 * Returns the field type of the registered field or null
 *
 * @param string $name - the field name e.g. _date
 * @param string $field_type - the field type e.g. date or null
 */
if ( !function_exists( "bw_query_field_type" ) ) {  
function bw_query_field_type( $name ) {
  global $bw_fields;
  $field = bw_array_get( $bw_fields, $name, null );
  if ( $field ) {
    $field_type = bw_array_get( $field, "#field_type", null );
  } else {
    bw_trace2( "Invalid field name" );
    $field_type = null;
  }
  return( $field_type );
} 
}

/**
 * Determine a meta_value based on the field type, the specified meta_value and possibly the "meta_compare" parameter
 *
 * @param array $atts - array of parameters which may or may not contain the meta_value parameter
 * @param string $meta_key - the field name from which we determine the field type
 * @return string $meta_value to use
 */                                         
function bw_related_meta_value( $atts, $meta_key ) {
  $meta_value = bw_array_get( $atts, "meta_value", null );
  $field_type = bw_query_field_type( $meta_key );
  bw_trace2( $field_type, "field_type" );
  $meta_value = apply_filters( "oik_default_meta_value_${field_type}", $meta_value, $atts );
  return( $meta_value ); 
}   


/**
 * Check if the post_type is mentioned in post_types
 *
 * @param array/string $post_types - an array of post types or a single post type
 * @param string $post_type - a single post type name
 * @return bool - true if the post type is mentioned
 *  
 */ 
function bw_check_noderef_types( $post_types, $post_type ) {
  if ( is_array( $post_types ) ) { 
    $found = bw_array_get( $post_types, $post_type, false );
  } else {
    $found = ( $post_types == $post_type );
  }
  bw_trace2( $found, "post type found?" );
  return( $found );
}
  
/**
 * Query the field name for a noderef pointing to this post type
 * 
 * There may be more than one, but we're only going to take the first
 * Entry from $bw_fields

 
     [_sc_param_code] => Array
        (
            [#field_type] => noderef
            [#title] => Shortcode
            [#args] => Array
                (
                    [#type] => oik_shortcodes
                )

        )
 * 
 * #field_type = noderef
 * #args #type = oik_shortcodes == $current_post_type
 * 
 * 
 *  
 * @param string $current_post_type - post type we're finding references to
 * @param string $post_type - target post type or null
 */
function bw_query_field( $current_post_type, $post_type=null ) {
  global $bw_fields;
  $meta_key = null;
  //bw_trace2( $bw_fields, "fields" );
  //bw_trace2( $bw_mapping, "mapping" );
  foreach ( $bw_fields as $key => $data ) {
    if ( $data['#field_type'] == "noderef" ) {
      $post_types = $data['#args']['#type'];
      $cpt_found = bw_check_noderef_types( $post_types, $current_post_type );
      if ( $cpt_found ) {
        $meta_key = $key;
        bw_trace2( $meta_key, "meta_key" );
        break;
      }  
    }   
  } 
  return( $meta_key );
}   
  
/**
 * Query the source post type which has a noderef field $meta_key referencing the current_post_type
 *
 * @TOOD - write the code 2014/01/10... in the mean time we're just going to return 'any'
 *
 * @param string $current_post_type 
 * @param string $meta_key - the noderef field name
 * @return string - the post_type to look for 
 * 
 */
function bw_query_post_type( $current_post_type, $meta_key ) {
  $post_type = 'any';
  bw_trace2( $post_type, "post_type" );
  return( $post_type );
}   
  
/** 
 * Determine what we should be listing based on the current post
 * 
 * @param array $atts - array of shortcode parameters
 *
 * post_type  meta_key   action
 * set        set        n/a - this function should not have been called
 * set        null       find 
 * @TODO tbc
 */  
function bw_query_related_fields( &$atts) {
  $post_type = bw_array_get( $atts, "post_type", null );
  if ( $post_type ) {
    // The meta_key is not set - we need to find a meta_key value for a noderef field from the specified post type to the current post type
    $meta_key = bw_query_field( bw_global_post_type(), $post_type );  
  } else {
    $meta_key = bw_array_get( $atts, "meta_key", null );
    if ( $meta_key ) {
      $post_type = bw_query_post_type( bw_global_post_type(), $meta_key ); 
    } else {
      // Neither set 
      $meta_key = bw_query_field( bw_global_post_type() );
      $post_type = bw_query_post_type( bw_global_post_type(), $meta_key ); 
    }
  }
  $atts['post_type'] = $post_type;
  $atts['meta_key'] = $meta_key;
  return( $meta_key );
}

                                         
function bw_related__help( $shortcode="bw_related" ) {
  return( __( "Display links to related content", "oik-bob-bing-wide" ) );
}

/*
 * Syntax help for [bw_related] shortcode
 *
 * Note: the expected parameters for this shortcode are:
 * post_type= post type(s) to be listed
 * meta_key = the name of noderef field that refers to this post's type
 * 
 * You may want to add other parameters to further qualify the lookup. 
 */
function bw_related__syntax( $shortcode="bw_related" ) {
  $syntax = _sc_posts(); 
  $syntax = array( "post_type" => bw_skv( null, "<i>post type</i>", "Related post type" )
                 , "meta_key" => bw_skv( null, "<i>meta key</i>", "name of noderef field" )
                 , "meta_value" => bw_skv( null, "<i>meta value</i>", "the default value depends on the field type" )
                 );
  return( $syntax );
} 
