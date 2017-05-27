<?php // (C) Copyright Bobbing Wide 2015, 2017
/**
 * Return the Google Map for the given _post_code, _lat and _long
 *
 * This is similar to using [bw_show_googlemap] to display a Google Map
 * except we pass the values of _post_code, _lat and _long 
 * so it doesn't need to get them from oik options.
 *
 * @param string $post_code
 * @param string $lat
 * @param string $long
 * @param integer $ID 
 * @return string - Google Map
 */
function bw_fields_get_google_map( $post_code, $lat=null, $long=null, $ID=null ) {
	//bw_trace2();
	//bw_backtrace();
	
	if ( is_numeric( $lat )  && is_numeric( $long ) ) {
		oik_require( "shortcodes/oik-googlemap.php" );
		$google_map = bw_googlemap_v3( get_the_title( $ID )
							, $lat
							, $long
							, $post_code
							, "100%"
							, "400px"
							);
	}	else {
		$google_map = null;
	}

	return( $google_map );
}
