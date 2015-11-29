<?php // (C) Copyright Bobbing Wide 2015
/**
 * Return the Google Map for the given _post_code, _lat and _long
 *
 * Use [bw_show_googlemap] to display a Google Map
 * passing the values of _post_code, _lat and _long 
 * so it doesn't need to get them from oik options
 *
 * @param array -
 * @return string - Google Map
 */
function bw_fields_get_google_map( $post_code, $lat=null, $long=null, $ID=null ) {

	bw_trace2();
	bw_backtrace();

	
	oik_require( "shortcodes/oik-googlemap.php" );
	

	
  $google_map = bw_googlemap_v3( get_the_title( $ID )   
            , $lat
            , $long
            , $post_code
						, "100%"
						, "400px"
            );
	
	

  return( $google_map );
}
