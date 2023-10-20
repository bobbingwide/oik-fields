<?php // (C) Copyright Bobbing Wide 2014-2019, 2023

/**
 * Implement [bw_related] shortcode
 *
 * To take out the hard work listing items which are related to the current item through "noderef" type fields
 *
 * Here we have the original shortcode from post ID 401 which is an instance of an "oik_shortcode"
 *
 * `
 *  [bw_list post_type=shortcode_example meta_key=_sc_param_code meta_value=401]
 * `
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
  //bw_trace2();
  //bw_backtrace();
  
  oik_require( "shortcodes/oik-list.php" );
  oik_require( "includes/bw_posts.php" );
  $post_type = bw_array_get( $atts, "post_type", null );
  $meta_key = bw_array_get( $atts, "meta_key", null );
  $atts['post_parent'] = bw_array_get( $atts, 'post_parent', 'no' );
  $tag = bw_array_get( $atts, "tag", null );
  $category_name = bw_array_get( $atts, "category_name", null );
  $by = bw_array_get( $atts, "by", null );
  if ( $tag ) {
    $atts['tag'] = bw_query_taxonomy_value( $tag ); 
  } elseif ( $category_name ) {
    $atts['category_name'] = bw_query_taxonomy_value( $category_name );  
  } elseif ( $by ) {
    $ids = bw_query_field_values( $by ); 
    $atts['post__in'] = bw_array_get_unkeyed( $ids );
    unset( $atts['by'] );
  } else {
    if ( $post_type && $meta_key ) {
      // they've specified the post type and meta_key so we don't have to look for it
    } else {
      $meta_key = bw_query_related_fields( $atts );
    }
    $atts['meta_value'] = bw_related_meta_value( $atts, $meta_key );
  } 
  $format = bw_array_get( $atts, "format", null );
  if ( $format === 'T') {
  	oik_require( "shortcodes/oik-table.php");
  	$result = bw_table( $atts );

  } elseif ( $format ) {
    oik_require( "shortcodes/oik-pages.php" );
    $result = bw_pages( $atts );
  } else {
    $result = bw_list( $atts ); 
  }  
  return( $result );  
}

/**
 * Determine the tag or category value to use if the given value is not a tag or category slug
 * 
 * The rules for tag and category slugs are... that they are usually all lowercase and contains only letters, numbers, and hyphens.
 * So if we find a period then we can consider it to be a field reference
 * 
 * But we need to convert the field value into a tag slug! 
 *
 * Note: If the tag is numeric or comma separated then we'll not do any lookup
 *
 * 
 */
function bw_query_taxonomy_value( $fieldorvalue ) {
  $dotpos = strpos( $fieldorvalue, "." );
  bw_trace2( $dotpos, "dotpos");
  if ( false !== $dotpos ) {
    if ( $dotpos ) {
      // lookup field from noderef - bw_query_fieldref_value
      //
      $value = bw_query_fieldref_value( $fieldorvalue );
    } else {
      // get_post_meta if a field name follows
      //
      // 
      $field_name = substr( $fieldorvalue, 1 );
      
  bw_trace2( $field_name, "field_name");
      
      if ( $field_name ) {
        $value = bw_query_field_value( $field_name );
      } else {
        // or get the current tags as in bw_get_posts() **?** @TODO
        $value = null;
      }
    }
    
  } else {
    $value = $fieldorvalue;
  }
  bw_trace2( $value, "value" );
  return( $value );
}

/**
 * Return the field value for the given fieldref
 * 
 * Lookup the noderef then access the field value for that noderef
 * Assumptions:
 * - The fields are single fields
 * - the noderef is a noderef field for the current post
 * - the field_name is a value field in the target_post
 *
 * @param string $fieldref in format _node_ref._field_ref
 * @param ID $post_id - post ID, if not the global post
 * @return string - the value or null
 */
if ( !function_exists( "bw_query_fieldref_value" ) ) { 
function bw_query_fieldref_value( $fieldref, $post_id=null ) {
  list( $noderef_name, $field_name ) = explode( ".", $fieldref ); 
  $target_post = bw_query_field_value( $noderef_name, $post_id );
  if ( $target_post ) {
    $value = bw_query_field_value( $field_name, $target_post );
  } else {
    $value = null;
  }
  return( $value );   
}
}

/**
 * Return the field value for the given post
 *
 * @TODO: Cater for object properties
 * @TODO: Cater for multiple values
 *
 * @param string $field_name e.g. _oikp_slug 
 * @param ID $post_id - post ID, if not the global post
 * @return string - the value or null
 *  
 */
if ( !function_exists( "bw_query_field_value" ) ) { 
function bw_query_field_value( $field_name, $post_id=null ) {
  if ( null == $post_id ) {
    $post_id = bw_global_post_id();
  }  
  $value = get_post_meta( $post_id, $field_name, true );
  return( $value );
}
}

/**
 * Return the field values for the given post
 *
 * @TODO: Cater for object properties
 *
 * @param string $field_name e.g. _oikp_slug 
 * @param ID $post_id - post ID, if not the global post
 * @return string - the value or null
 *  
 */
if ( !function_exists( "bw_query_field_values" ) ) { 
function bw_query_field_values( $field_name, $post_id=null ) {
  if ( null == $post_id ) {
    $post_id = bw_global_post_id();
  }  
  $value = get_post_meta( $post_id, $field_name, false );
  return( $value );
}
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
  $meta_value = apply_filters( "oik_default_meta_value_{$field_type}", $meta_value, $atts );
  //bw_trace2( $meta_value, "meta_value" );
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
  //bw_trace2();
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
 * Note: for some post types  (e.g. shortcode_examples ) the noderef points to a 
 * so we need to determine the meta key differently... the first noderef for the post type
 * 
 *  
 * @param string $current_post_type - post type we're finding references to
 * @param string $post_type - target post type or null
 * 
 * @TODO - this logic does not use the $post_type parameter but there are instances where it's passed in as if it should have been used. 
 * Need to reconsider what we're actually trying to achieve and how to achieve it **?** 
 * 
 */
function bw_query_field( $current_post_type, $post_type=null ) {
  //bw_trace2();
  global $bw_fields;
  global $bw_mapping;
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
 * Query post type and meta key for performing a bw_related
 *
 * @TODO - complete this code! 
 * 
 */  
function bw_query_post_type_and_meta_key( $atts ) {
  $post_type = null;
  $meta_key = null;
  $gpt = bw_global_post_type();
  if ( $gpt ) {
    $meta_key = bw_query_field( $gpt );
    if ( $meta_key ) {
      bw_trace2( $meta_key, "meta_key for global post type $gpt" );
      $post_type = bw_query_post_type( $gpt, $meta_key ); 
    } else {
     //gobang(); 
    } 
  } else {
    //gobanh();
  }
  return( array( $post_type, $meta_key ) );
}  
  
/** 
 * Determine what we should be listing based on the current post
 * 
 * @TODO tbc
 * 
 * @param array $atts - array of shortcode parameters
 *
 * post_type  meta_key   action
 * ---------  --------   -----------------------------------------------
 * set        set        n/a - this function should not have been called
 * set        null       find tbc
 * null       set        tbc 
 * null       null       tbc
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
      list( $post_type, $meta_key ) = bw_query_post_type_and_meta_key( $atts ); 
      //$meta_key = ?;
    }
  }
  $atts['post_type'] = $post_type;
  $atts['meta_key'] = $meta_key;
  return( $meta_key );
}

                                         
function bw_related__help( $shortcode="bw_related" ) {
  return( __( "Display related content", "oik-fields" ) );
}

/*
 * Syntax help for [bw_related] shortcode
 *
 * Note: the <i>expected</i> parameters for this shortcode include:
 * post_type= post type(s) to be listed
 * meta_key = the name of noderef field that refers to this post's type or the date field when searching by date
 * meta_value = the value we're looking for
 * meta_compare = used with dates
 * OR
 * by = the name of a noderef field attached to this post. 
 * 
 * You may want to add other parameters to further qualify the lookup. 
 * Use the format= parameter to have output displayed using same logic as [bw_pages]
 * If not specified the output is formatted using [bw_list] logic
 */
function bw_related__syntax( $shortcode="bw_related" ) {
  $syntax = _sc_posts(); 
  $syntax = array( "post_type" => bw_skv( null, "<i>post type</i>", "Related post type" )
                 , "meta_key" => bw_skv( null, "<i>meta key</i>", "name of noderef field" )
                 , "meta_value" => bw_skv( null, "<i>meta value</i>", "the default value depends on the field type" )
                 , "format" => bw_skv( null, "<i>format string</i>", "field format string" )
                 , "by" => bw_skv( null, "<i>noderef field</i>", "name of a noderef field" )
                 );
  return( $syntax );
} 
