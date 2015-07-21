<?php // (C) Copyright Bobbing Wide 2014
/**
 * Return the file size of an attached file
 *
 * The logic to create the full file name from the attached file name is from get_attached__file()
 *
 * @param string - attached file name - from _wp_attached_file metadata
 * @return string - file size ( in bytes )
 */
function bw_fields_get_file_size( $file ) {
  //bw_trace2();
  if ( $file && 0 !== strpos($file, '/') && !preg_match('|^.:\\\|', $file) && ( ($uploads = wp_upload_dir()) && false === $uploads['error'] ) ) {
    $file = $uploads['basedir'] . "/$file";
    $file_size = filesize( $file ); 
  } else {
    $file_size = null;
  } 
  return( $file_size );
}

/**
 * Return the size of an attached image
 *
 * When it's an image we return the dimensions of the original image.
 * ie. the width and height from the deserialized array from _wp_attachment_metadata
 *
 * @param array $wp_attachment_metadata - attachment metadata
 * @return string Either width x height or null if width and height are not defined
 * 
 */
function bw_fields_get_dimensions( $wp_attachment_metadata ) {
  //bw_trace2();
  $width = bw_array_get( $wp_attachment_metadata, "width", null );
  $height = bw_array_get( $wp_attachment_metadata, "height", null );
  if ( $width && $height ) {
    $dimensions = "$width x $height";
  } else {
    $dimensions = null;
  } 
  return( $dimensions );
}

/**
 * Return a featured image
 * 
 * @param ID $thumbnail_id - the ID of the post's thumbnail image
 * @return full size featured image
 */
function bw_fields_get_featured_image( $thumbnail_id ) {
  $thumbnail =  wp_get_attachment_image_src( $thumbnail_id, "full" ) ;
  if ( $thumbnail ) {
    $thumbnail = retimage( "featured", $thumbnail[0] );
  }
  bw_trace2( $thumbnail );
  return( $thumbnail);
}



/**
 * Return the thumbnail for the featured image
 * 
 * @param ID $thumbnail_id - the ID of the post's thumbnail image
 * @return thumbnail sized featured image
 */
function bw_fields_get_thumbnail( $thumbnail_id ) {
  $thumbnail =  wp_get_attachment_image_src( $thumbnail_id, "thumbnail" ) ;
  if ( $thumbnail ) {
    $thumbnail = retimage( "thumbnail", $thumbnail[0] );
  }
  bw_trace2( $thumbnail );
  return( $thumbnail);
}
 
