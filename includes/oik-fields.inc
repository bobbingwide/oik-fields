<?php // (C) Copyright Bobbing Wide 2011-2018

/**
 * Library: 
 * Provides:
 * Depends: 
 */

/**
 * Theme a field of type 'currency'
 * 
 * @param string $key - the field name
 * @param string $value - the field value
 *
 * Note: money_format() does not work in Windows
 * Note: This does not implement the currency prefix. It simply formats with 2 decimal places
 * 
 *
 */
function bw_theme_field_currency( $key, $value ) {
  e( sprintf( "%.2f", bw_array_get( $value, 0, $value ) ) );
}

/**
 * Theme a field of type 'numeric'
 * 
 * @param string $key - the field name
 * @param string/array $value - the field value - expected to be an array with key 0
 *
 */
function bw_theme_field_numeric( $key, $value ) {
  //bw_backtrace();
  //bw_trace2( $value, "numeric field $key" );
  if ( empty( $value ) ) {
    e( "" );
  } else {   
    e( bw_array_get( $value, 0, $value ) );
  }  
}

/**
 * Theme a field of type 'date'
 * 
 * e.g. date_i18n(get_option('date_format') ,strtotime("11/15-1976"));?>
 * 
 * @param string $key - the field name
 * @param string $value - the field value - expected to be in ccyy-mm-dd  rather than a unix timestamp
 *
 */
if ( !function_exists( "bw_theme_field_date" ) ) {
function bw_theme_field_date( $key, $value ) {
  //if ( count( $value ) )
  //  e( bw_format_date( $value[0] ) );
  $date = bw_array_get( $value, 0, $value );
  if ( $date ) {
    $format = get_option( 'date_format' );
    $date = strtotime( $date );
    e( date_i18n( $format, $date ) ); 
    //e( bw_format_date(  ) );
  }  
}
}

/**
 * Theme a field of type 'select'
 * 
 * @param string $key - the field name
 * @param mixed $value - the field value
 * @param array $field - key value pairs containing the #options for the select field within the #args element
 *
 */
function bw_theme_field_select( $key, $value, $field ) {
  e( bw_return_field_select( $key, $value, $field ) );
}

/**
 * Return the value(s) for the select field
 *
 * The select field may be #optional indicating that no value is needed.
 * The field may also be #multiple, indicating that a comma separated list should be returned.
 *
 * We need to be able to cater for this. 
 * `
    [0] => _oik_api_type
    [1] => Array
        (
        )

    [2] => Array
        (
            [#field_type] => select
            [#title] => API type
            [#args] => Array
                (
                    [#options] => Array
                        (
                            [shortcode] => shortcode
                            [filter] => filter
                            [action] => action
                            [public] => public
                            [private] => private
                            [undefined] => undefined
                            [deprecated] => deprecated
                            [hook] => hook
                        )

                    [#optional] => 1
                )
        )
 * `
 *
 * @param string $key - the field name
 * @param mixed $value - the field value as array index 0, OR a string, or multiple array items 
 * @param array $field - key value pairs containing the #options for the select field within the #args element
 * @return string the field value(s), comma separated as necessary
 */
function bw_return_field_select( $key, $value, $field ) {
	//bw_trace2(); 
	//bw_backtrace();
	$args = bw_array_get( $field, '#args', null );
	$multiple = false;
	$select = array();
	$result = null;
	if ( $args ) {
		$select = bw_array_get( $args, '#options', null );
		$select = bw_as_array( $select );
		$multiple = bw_array_get( $args, "#multiple", false );
	} 
	if ( $multiple ) {
		$results = array();
		foreach ( $value as $val ) {
			$results[] = bw_array_get( $select, $val, $val );   
		}
		$result = implode( ", ", $results );
	} else {
		// If value is an empty array this will return an empty array. 
		$val = bw_array_get( $value, 0, $value );
		if ( !empty( $val ) ) {
			$result = bw_array_get( $select, $val, $val );   
		}
	}	  
	return( $result );  
}

/**
 * Theme a field of type 'noderef' 
 *
 * If it's a multiple link then display the results as an unordered list, otherwise display a single item.
 * For a multiple link we have to avoid 'None' which has a value of 0, and is not a valid post ID.
 *
 * @TODO Also support comma separated display format. 
 *
 *
 * @param string $key - the field name 
 * @param mixed $value - the ID of the post either directly or as $value[0] or an array of posts
 * @param array $field - the information about the field
 */
function bw_theme_field_noderef( $key, $value, $field ) {
		bw_trace2();
		$started = false;
		//bw_backtrace();
		$multiple = bw_array_get( $field['#args'], "#multiple", false );
		if ( $multiple ) {
			if ( is_array( $value ) && count( $value ) ) {
				foreach ( $value as $key => $v0 ) {
					if ( $v0 ) {
						if ( !$started ) { 
							sul();
							$started = true;
						}	
						stag( "li" );
						$title = get_the_title( $v0 );
						alink( null, get_permalink( $v0 ), $title );
						etag( "li");
					}
				}
				if ( $started ) {
					eul();
				}	
			}
		} else {
			$v0 = bw_array_get( $value, 0, $value );
			if ( $v0 ) {
				$title = get_the_title( $v0 );
				alink( null, get_permalink( $v0 ), $title );
			}  
		}  
}

/**
 * Theme a field of type 'URL'
 *
 * Display a link to the given URL.
 * @TODO Assume an http:// prefix a la bw_link 
 *
 * @param string $key - the field name
 * @param array $value - the field value at index 0
 */ 
function bw_theme_field_URL( $key, $value ) {
  $v0 = bw_array_get( $value, 0, $value );
  if ( $v0 ) {
    oik_require( "shortcodes/oik-link.php" );
    $url = bw_link_url( $v0, null );
		$text = trim_scheme( $v0 );
    $link = retlink( null, $url, $text );
    e( $link );
  }  
}

/**
 * Theme a field of type 'text' 
 *
 * @param string $key - the field name
 * @param array $value - the field value at index 0  
 */
function bw_theme_field_text( $key, $value ) {
  if ( !empty( $value ) ) {
    e( bw_array_get( $value, 0, $value ) );
  }  
}

/**
 * Theme a field of type 'sctext' 
 *
 * Theme a field which may contain shortcodes
 * e.g. _mbo_class - which may contain the [mbo] shortcode to book a class
 *
 * @param string $key - the field name
 * @param array $value - the field value at index 0  
 */
function bw_theme_field_sctext( $key, $value ) {
  if ( !empty( $value ) ) {
    $text = bw_array_get( $value, 0, $value );
    $text = bw_do_shortcode( $text );
    e( $text );
  }  
}

/**
 * Theme a field of type 'textarea' 
 *
 * @param string $key - the field name
 * @param array $value - the field value at index 0  
 */
function bw_theme_field_textarea( $key, $value ) {
  if ( !empty( $value ) ) {
    e( bw_array_get( $value, 0, $value ) );
  }  
}

/**
 * Theme a field of type 'sctextarea' 
 *
 * @param string $key - the field name
 * @param array $value - the field value at index 0  
 */
function bw_theme_field_sctextarea( $key, $value ) {
  bw_theme_field_sctext( $key, $value );
}

/**
 * Theme a field of type 'email' using _bw_email()
 *
 * @param string $key - the field name
 * @param array $value - the field value at index 0  
 */
function bw_theme_field_email( $key, $value ) {
	if ( !empty( $value ) ) {
		oik_require( "shortcodes/oik-email.php" );
		$email = bw_array_get( $value, 0, $value );
		// The prefix and separator have already been set, so null them out here
		$atts = array( "prefix" => ""  
								 , "sep" => ""
								 , "email" => $email
								);
		e( _bw_email( $atts ) );
	}
}

/**
 * Theme a field of type checkbox 
 * 
 * @param string $key - the field name
 * @param string $value - the field value
 *
 */
function bw_theme_field_checkbox( $key, $value ) {
  //bw_trace2();
  //bw_backtrace();
  $value = bw_array_get( $value, 0, $value );
  if ( $value && $value != "0" ) {
    e( __( "Yes" ));
  } else { 
    e( __( "No" ));
  } 
  //e( $value[0] );
}

/**
 * Theme a field of type "taxonomy"
 * 
 * We should never get here since 'taxonomy' fields are handled differently from other fields.
 * @See bw_format_taxonomy(), called from bw_metadata()
 * 
 * @TODO: It seems we do when the bw_new form is being processed. 
 */
function bw_theme_field_taxonomy( $key, $value ) {
  bw_trace2();
  bw_backtrace();
  //gobang();
}

/**
 * Obtain the parm values for the callback function
 * 
 * The parameters we pass to the callback function must be obtained from post_meta for the current post.
 * For example for the callback function used to display the value of the virtual field _oik_sc_the_title_cb
 * we need to obtain the value of the _oik_sc_code field to pass as the first parameter.
 *
 * @TODO Extend to support 'fieldref' fields as well, by using bw_query_fieldref_value()
 *
 * Note: The parms must be unique values... not multiple
 * 
 * @param string $field the virtual field being displayed
 * @return array  parm values
 */
function bw_virtual_field_parm_values( $field ) { 
  $args = $field['#args'];
  $parms = bw_assoc( bw_as_array( bw_array_get( $args, "#parms", null ) ) );
  $post_id = bw_current_post_id();
  $parm_values = array();
  if ( count( $parms ) ) {
    foreach ( $parms as $parm ) {
      $parm_values[] = get_post_meta( $post_id, $parm, true );
    }
  }
	$parm_values[] = $post_id;
  //bw_trace2( $post_id, "post id" );
  //bw_trace2( $parm_values, "parm_values" );
  return( $parm_values );
}

/**
 * Load the callback function for a virtual field
 *
 * Example, the _oik_sc_the_title_cb virtual field is themed by the 
 * #callback function bw_get_shortcode_expands_in_titles() 
 * which is implemented in "#file" => "shortcodes/oik-codes.php"
 * from "#plugin" => "oik"
 *
 * @param array $field - the definition of the field
 * @return string - the callback name if successfully loaded
 *
 */
function bw_load_callback( $field ) {
  $args = $field['#args'];
  $callback = bw_array_get( $args, "#callback", null );
  if ( !is_callable( $callback ) ) {
    $file = bw_array_get( $args, "#file", null );
    $plugin = bw_array_get( $args, "#plugin", null );
    $path = oik_path( $file, $plugin );
    if ( file_exists( $path ) ) {
      oik_require( $file, $plugin );
    }  
    if ( !is_callable( $callback ) ) {
      $callback = "Missing callback for field"; 
    }
  }
  return( $callback );  
}  

/**
 * Theme a field of type 'virtual'
 * 
 * @param string $key
 * @param mixed $value
 * @param array $field
 
   [2] => Array
        (
            [#field_type] => virtual
            [#title] => Expands during 'the_title' processing?
            [#args] => Array
                (
                    [#callback] => bw_get_shortcode_expands_in_titles
                    [#parms] => _oik_sc_code
                    [#plugin] => oik
                    [#file] => shortcodes/oik-codes.php
                )

        )

 */
function bw_theme_field_virtual( $key, $value, $field ) {
  //bw_trace2();
  //bw_backtrace();
  $parm_values = bw_virtual_field_parm_values( $field );
	bw_trace2( $parm_values, "parm_values", true );
  $funcname = bw_load_callback( $field );
  if ( is_callable( $funcname ) ) {
    e( call_user_func_array( $funcname, $parm_values ) );
  } else {
    e( $funcname . $key );
  }
}

