<?php // (C) Copyright Bobbing Wide 2015

/**
 * OIK_fields_groups_taxonomy extends OIK_fields_groups
 * to provide support for grouping by taxonomy term
 **/
 
 
// Temporary hack until autoload implemented
 
oik_require( "includes/class-oik-fields-groups.php", "oik-fields" ); 


class OIK_fields_groups_taxonomy extends OIK_fields_groups {

	public $terms;
	
	function initialise_set() {
	
		$this->terms = get_terms( $this->field );
		//print_r( $this->terms );
		foreach ( $this->terms as $term ) {
			$this->fields[ $term->term_id ] = 0;
		}
	
	}
	
	function get_field_values( $post ) {
		$terms = wp_get_post_terms( $post->ID, $this->field );
		$this->field_values = $terms;
	}
	
	function count_values() {
		foreach ( $this->field_values as $key => $term ) {
			$this->field_value = $term->term_id;
			$this->count_value();
		}
	
	}
	
	function get_term( $key ) {
		$term = get_term( $key, $this->field );
		$term_name = $term->name;
		return( $term_name );
	}
	
	function report_field( $key, $field ) {
		$term = $this->get_term( $key );
		li( "$term $field" );
	}
	
}

/*


function bw_count_query( $atts=null, $content=null, $tag=null ) {

	
	

	
	$posts = bw_get_posts( $atts );
	foreach ( $posts as $post ) {
		
		
		$post_terms = wp_get_post_terms( $post->ID, $taxonomy );
		
}

*/
