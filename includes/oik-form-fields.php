<?php
/* 

    Copyright 2014 Bobbing Wide (email : herb@bobbingwide.com )

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2,
    as published by the Free Software Foundation.

    You may NOT assume that you can use any other version of the GPL.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    The license for this software can likely be found here:
    http://www.gnu.org/licenses/gpl-2.0.html
*/


/**
 * Implement bw_form_field_ hook for 'virtual' field
 *
 * @param string $name - field name
 * @param string $type - field type
 * @param string $title - field title
 * @param string $value - not expected
 * @param string $args - the '#args' part of the field definition
 *
 */
function bw_form_field_virtual( $name, $type, $title, $value, $args ) {
  bw_trace2();
  $lab = label( $name, $title );
  stag( "tr" );
  bw_td( $lab );
  stag( "td" );
  $theme_args = array( "#args" => $args );
  bw_theme_field_virtual( $name, $value, $theme_args );
  //bw_tablerow( array( $lab, $field ));
  etag( "td" );
  etag( "tr" );
}
