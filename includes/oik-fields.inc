<?php // (C) Copyright Bobbing Wide 2011-2014

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
  bw_backtrace();
  bw_trace2( $value, "numeric field $key" );
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
 * Return the value for the select field
 *
 * @param string $key - the field name
 * @param mixed $value - the field value as array index 0 OR a string 
 * @param array $field - key value pairs containing the #options for the select field within the #args element
 
 * The select field may be #optional indicating that no value is needed.
 * We need to be able to cater for this. 
(
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

)
 * 
 */
function bw_return_field_select( $key, $value, $field ) {
  bw_trace2(); 
  bw_backtrace();
  $args = bw_array_get( $field, '#args', null );
  if ( $args ) {
    $select = bw_array_get( $args, '#options', null );
    $select = bw_as_array( $select );
  } 
  // If value is an empty array this will return an empty array. 
  
  $val = bw_array_get( $value, 0, $value );
  if ( empty( $val ) ) {
    $result = null;
  } else { 
    $result = bw_array_get( $select, $val, $val );   
  }  
  //if ( null !== $val ) { 
  //} else {
  //  $result = $value;
  //}  
  return( $result );  
}

/**
 * Theme a field of type 'noderef' 
 *
 * If it's a multiple link then display the results as an unordered list, otherwise display a single item
 * @TODO Also support comma separated display format. 
 
 *
 * @param string $key - the field name 
 * @param mixed $value - the ID of the post either directly or as $value[0] or an array of posts
 * @param array $field - the information about the field
 */
function bw_theme_field_noderef( $key, $value, $field ) {
  bw_trace2();
  bw_backtrace();
  $multiple = bw_array_get( $field['#args'], "#multiple", false );
  if ( $multiple ) {
    if ( is_array( $value ) && count( $value ) ) {
    sul();
    foreach ( $value as $key => $v0 ) {
      stag( "li" );
      $title = get_the_title( $v0 );
      alink( null, get_permalink( $v0 ), $title );
      etag( "li");
    }
    eul();
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
 * @param string $key - the field name
 * @param array $value - the field value at index 0
 */ 
function bw_theme_field_URL( $key, $value ) {
  $v0 = bw_array_get( $value, 0, $value );
  if ( $v0 ) {
    $link = retlink( null, $v0, $v0 );
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
 * Theme a field of type 'email' using _bw_email()
 *
 * @param string $key - the field name
 * @param array $value - the field value at index 0  
 */
function bw_theme_field_email( $key, $value ) {
  oik_require( "shortcodes/oik-email.php" );
  $email = bw_array_get( $value, 0, $value );
  // The prefix and separator have already been set, so null them out here
  $atts = array( "prefix" => ""  
               , "sep" => ""
               , "email" => $email
               );
  e( _bw_email( $atts ) );
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
 */
function bw_theme_field_taxonomy( $key, $value ) {
  bw_trace2();
  bw_backtrace();
  gobang();

}

