<?php

function bw_fields_get_author_name( $parms ) {
    //print_r( $parms);
    //gob();
    $author_name = get_the_author();
    return $author_name;
}
