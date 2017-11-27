<?php // (C) Copyright Bobbing Wide 2015-2017

/**
 * OIK_fields_groups class
 *
 * Implements the logic to count fields associated with a set of posts
 * where the fields can be:
 * - post properties
 * - post meta data
 * - taxonomy terms
 
 * Extend this class with your own field types. eg
 * Field | Extension class
 * ------ | ----------------
 * select | class OIK_fields_groups_select	extends OIK_fields_groups {} 
 * noderef | class OIK_fields_groups_noderef extends OIK_fields_groups {}
 * date | class OIK_fields_groups_date extends OIK_fields_groups {}
 * property | class OIK_fields_groups_property extends OIK_fields_groups {}


 *
 */								
class OIK_fields_groups {

	/**
	 * The name of the field(s) we're grouping by
	 * 
	 */
	public $field;
	
	/**
	 * The field type	- used to determine which class we're supposed to be using
	 */
	public $field_type;
	
	/**
	 * Array of field value counts - may be initialised for finite sets such as select 
	 */											
	public $fields; 
	
	/**
	 * Flag to indicate if the complete set has been built											
	 */
	public $initial_set;
	
	public $atts;
	
	/**
	 * Singular field value
	 */
	
	public $field_value;
	
	/**
	 * Multiple field values
	 */
	public $field_values; 
	
	/**
	 * The set of posts that match the query
	 */
	public $posts;
	
	private $uo;
	
	/**
	 * Construct the object
	 */
	public function __construct() {
		$this->fields = array();
	}
	
	public function process( $atts ) {
		$this->atts = $atts;
		$this->field();
		$this->field_type();
		$this->get_posts();
		$this->initialise_set();
		$this->count_posts();
		$this->sort_fields();
		$this->report_fields();
	}
	
	/**
	 * Determine the field name
	 *
	 * What shall we use as a default? published_date, first custom field?
	 * Answer: use post_status since this is what wp_count_posts does
	 * 
	 * What if the field doesn't exist?
	 *
	 */
	public function field() {
		$this->field = bw_array_get( $this->atts, "field", "post_status" );
	}
	
	/**
	 * Determine the field type
	 * 
	 * @TODO How do we know if it's a property?
	 */
	public function field_type() {
		$this->field_type = bw_query_field_type( $this->field );
	}	
	
	public function get_posts() {
		oik_require( "includes/bw_posts.php" );
		$this->posts = bw_get_posts( $this->atts );
	}
	
	public function initialise_set() {
		$fields = array();
	}
	
	/**
	 * Count the posts
	 * 
	 */
	public function count_posts() {
		foreach ( $this->posts as $post ) {
			$this->get_field_values( $post );
			$this->count_values();
		}
	}
	
	/**
	 * 
	 * @TODO This only works for the field property, not the field post_meta
	 *
	 */
	function get_field_values( $post ) {
		$field = $this->field;
		if ( property_exists( $post, $field ) ) {
			$this->field_values = array( $post->$field );
		} else {
			$this->get_post_meta( $post );  
		}
  }
	 
	/**
	 * Get a single post meta value
	 * 
	 * @TODO Actually we just need the "value" - using bw_theme_field_value()
	 */
	function get_post_meta( $post ) {
		$this->field_values = get_post_meta( $post->ID, $this->field, false );
		bw_trace2( $this, "this" );
		gob();
	}
	
	/**
	 * Count all the returned results
	 */
	function count_values() {
		if ( is_array( $this->field_values ) ) {
			foreach ( $this->field_values as $key => $this->field_value ) {
				$this->count_value();
			}
		} else {
			$this->field_value = $this->field_values;
			$this->count_value();
		}
	}
	
	/**
	 * Count this instance
	 */
	function count_value() {
		//print_r( $this->field_value );
		if ( !isset( $this->fields[ $this->field_value ] ) ) {
			$this->fields[ $this->field_value ] = 1;
		} else {
			$this->fields[ $this->field_value ]++;
		}
	}
	
	/**
	 * By default sort by count DESCENDING
	 * 
	 * @TODO take this from the $atts! 
	 *
	 * If we convert this into an object then we can sort on a variety of things
	 * Let's just start simple, sorting the array by "count" DESC
	 * 
	 * 
	 *
	 */
	function sort_fields() {
			
	}
	
	
	/**
	 * Report the values
	 * 
	 */
	function report_fields() {
		$this->start_report();
		//print_r( $this->fields );
		foreach ( $this->fields as $key => $field ) {
			$this->report_field( $key, $field );
		}
		$this->end_report();
	}
	
	function start_report() {
		oik_require( "shortcodes/oik-list.php" );
		$this->uo = bw_sl( $this->atts );
	}
	
	function end_report() {
		bw_el( $this->uo );
	
	}
	
	function report_field( $key, $field ) {
		//print_r( $field );
		if ( is_array( $field ) ) {
			
			$item = implode( " ", $field );
		} else {
			$item = $field;
		}
		li( "$key $item" );
		
	}
}

 
