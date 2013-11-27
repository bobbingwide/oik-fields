<?php // (C) Copyright Bobbing Wide 2013
 
/**
 * Implement 'bw_field_validation_text' filter for oik-fields 
 * 
 * @param string $value - the value of the field from the $validated array
 * @param string $field - the field name
 * @param array $data - the field's registration data 
 * @return $string - The validated / sanitized value
 */
function oik_fields_validation_text( $value, $field, $data ) {
  $value = sanitize_text_field( $value );
  return( $value );
} 

/**
 * Implement 'bw_field_validation_textarea' filter for oik-fields 
 * 
 * Validate in the same way as we validate post_content using wp_kses_data() 
 * 
 * @param string $value - the value of the field from the $validated array
 * @param string $field - the field name
 * @param array $data - the field's registration data 
 * @return $string - The validated / sanitized value
 */
function oik_fields_validation_textarea( $value, $field, $data ) {
  $value = wp_kses_data( $value );
  //bw_backtrace();
  return( $value );
}

/**
 * Implement 'bw_field_validation_email' filter for oik-fields 
 * 
 * Validate to be in email format. 
 * @uses is_email() rather than sanitize_email()
 * 
 * @param string $value - the value of the field from the $validated array (it may be null)
 * @param string $field - the field name
 * @param array $data - the field's registration data 
 * @return $string - The validated / sanitized value
 */
function oik_fields_validation_email( $value, $field, $data ) {
  if ( $value ) { 
    $email = is_email( $value );
    if ( false === $email ) {
      bw_issue_message( $field, "bw_invalid_email", __( "Invalid email address" ), "error" );
    } else { 
      bw_trace2( "!$email!", "email?" );
    }
  } else { 
    $email = null;
  }     
  return( $email ); 
}

/**
 * Implement 'bw_field_validation_checkbox' filter for oik-fields 
 * 
 * Validate to be set or not set. 
 * 
 * @param string $value - the value of the field from the $validated array
 * @param string $field - the field name
 * @param array $data - the field's registration data 
 * @return $string - The validated / sanitized value
 */
function oik_fields_validation_checkbox( $value, $field, $data ) {
  $valid = array( 0, 1 );
  $checkbox = bw_array_get( $valid, $value, null );
  if ( null === $checkbox ) {  
    bw_issue_message( $field, "bw_invalid_checkbox", __( "Invalid checkbox" ), "error" );
  } else { 
    bw_trace2( "!$checkbox!", "checkbox?" );
  }
  return( $checkbox ); 
}   
 
/**
 * Function to run when oik-fields-validation is loaded 
 *  
 * Add filters for each of the field validation routines supported by oik-fields
 *   text         - sanitize_text_field()
 *   textarea     - wp_kses_data()
 *   email        - is_email()
 * @TODO **?** the following have not yet been tested or even developed 2013/07/22
 *   checkbox     - must be 0 or 1
 *   currency
 *   numeric
 *   date
 *   select 
 *   noderef
 *   URL
 
 *   etc
 * 
 *  These are implemented as filters which either return the filtered value OR an error
 *
 */
function oik_fields_validation_loaded() {
  $fields = array( "text", "textarea", "email" ); //, "select", "checkbox" );
  foreach ( $fields as $field ) { 
    bw_trace2( $field );
    add_filter( "bw_field_validation_$field", "oik_fields_validation_$field", 10, 3 );
  }
}

oik_fields_validation_loaded();
 
   
   
