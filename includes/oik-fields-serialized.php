<?php // (C) Copyright Bobbing Wide 2016

/**
 * Theme a serialized field
 
     [0] => (string) "_oik_rq_parms"
    [1] => Array

        [0] => (string) "a:3:{s:9:"post_type";s:11:"oik_request";s:7:"orderby";s:4:"date";s:5:"order";s:4:"desc";}"

    [2] => Array

        [#field_type] => (string) "serialized"
        [#title] => (string) "Query parameters"
        [#args] => (NULL) 
				
 * @param string $key field name ( often then post_meta meta_key )
 * @param array $value - the field value at index 0  
 * @param array $field - the information about the field

 */
function bw_theme_field_serialized( $key, $value, $field ) {
  if ( !empty( $value ) ) {
    $serialized = bw_array_get( $value, 0, $value );
	 $unserialized = unserialize( $serialized );  
		bw_trace2( $unserialized, "unserialized" );
		bw_theme_field_unserialized_array( $unserialized );
  }
}
	
/**
 * Display the unserialized array
 *
 * We need to process this a bit like print_r but using spans for formatting
 *  
 * Format each field as span class="key level" 
 * span class=value then the values
 *
 * @param array $unserialized a unserialized array
 * @param 
 */
function bw_theme_field_unserialized_array( $unserialized, $level=0 ) {
	if ( count( $unserialized ) ) {
		foreach ( $unserialized as $key => $value ) {
			if ( is_array( $value ) ) {
				bw_theme_field_unserialized_array( $value, ++$level );
			} else {
				sdiv( "bw_unserialized" );
				span( "$key level$level" );
				e( str_repeat( "&nbsp;&", $level ) );
				e( $key );
				e( "=" );
				epan();
				span( "value" );
				e( $value );
				epan();
				ediv();
			}
		}
	}
}
				

