<?php // (C) Copyright Bobbing Wide 2013-2017, 2023

/**
 * Implement [bw_new] shortcode to allow the creation of a new post through a simple form
 *
 * The logic implemented here will be similar to the [bw_contact_form] plugin 
 * WITH
 * - authority checking - does the post_type support add_new? 
 * - spam checking - check the content against Akismet
 * - nonce free support for logged in users? - @TODO - demonstrate this is not a good idea
 * - @TODO honeypot checking for spam bots
 * 
 * filters invoked to
 * - check if it's OK to do this for the defined post_type - default false
 * - check the default values for unspecified fields - perhaps WordPress already has the appropriate filters
 * 
 * @param array $atts - array of parameters
 * @param string $content - not expected
 * @param string $tag - not expected
 * @return string the generated HTML
 */
function bw_new( $atts=null, $content=null, $tag=null ) {
  bw_display_new_post_form( $atts );
  return( bw_ret());
}

/**
 * Help hook for [bw_new] shortcode
 */
function bw_new__help( $shortcode="bw_new" ) {
  return( "Display a form to create a new post" );
}

/**
 * Syntax hook for [bw_new] shortcode
 */
function bw_new__syntax( $shortcode="bw_new" ) {
  $syntax = array( "post_type" => bw_skv( null, "<i>post type</i>", "Supporting post_type name" )
                 , "format" => bw_skv( null, "<i>format</i>", "Field format string" )
                 , "email" => bw_skv( "<i>author</i>", "n|<i>email</i>", "Email address for New pending content email" ) 
                 );
  return( $syntax );
}

/** 
 * Return a unique new post form ID 
 *
 * @param bool $set - increment the ID if true
 * @return string - the contact form ID  - format oiku_contact-$bw_contact_form_id
 */
function bw_new_post_form_id( $set=false ) {
  static $bw_new_post_form_id = 0;
  if ( $set ) {
    $bw_new_post_form_id++;
  }
  return( "oik_new_post-$bw_new_post_form_id" );
}

/**
 * Issue a message for a particular field
 * 
 * Similar to add_settings_error() but not restricted to use by admin functions
 * 
 * @param string $field - field name  e.g. _dtib_location
 * @param string $code - message code e.g. "invalid_location"
 * @param string $text - the message text e.g. "Invalid Location, please correct"
 * @param string $type The type of message it is, controls HTML class. Use 'error' or 'updated'.
 */
if ( !function_exists( "bw_issue_message" ) ) { 
function bw_issue_message( $field, $code, $text, $type='error' ) { 
  bw_trace2( null, null, true, BW_TRACE_ALWAYS );
  global $bw_messages;
  if ( !isset( $bw_messages ) ) {
    $bw_messages = array();
  }
  $bw_messages[] = array( "field" => $field
                        , "code" => $code
                        , "text" => $text
                        , "type" => $type
                        );
  return( false );                       
}
}
  
/**
 * Return true is there are messages to display
 * 
 * @return integer - number of messages, if any
 */
if ( !function_exists( "bw_query_messages" ) ) { 
function bw_query_messages() {
  global $bw_messages;
  $messages = isset( $bw_messages );
  if ( $messages ) {
    $messages = count( $bw_messages );
  }
  return( $messages );
}
}

/** 
 * Display a message 
 * 
 * The message display is similar to the message display for settings errors 
 * 
 * @param array $bw_message - a message field
 * @return bool - true 
 */
 
if ( !function_exists( "bw_display_message" ) ) { 
function bw_display_message( $bw_message ) {
  $classes = $bw_message['field'];
  $classes .= " ";
  $classes .= $bw_message['type']; 
  sdiv( $classes, $bw_message['code'] );
  p( $bw_message['text'] );
  ediv();
  return( true );
}
}  

/** 
 * Display the messages
 * 
 * Display the set of messages in @global $bw_messages
 */
if ( !function_exists( "bw_display_messages" ) ) { 
function bw_display_messages() {
  global $bw_messages;
  $displayed = false;
  sdiv( "bw_messages" );
  foreach ( $bw_messages as $key => $bw_message ) {
    $displayed = bw_display_message( $bw_message );
  }
  ediv();
  return( $displayed );
}
} 
 
/**
 * Obtain and trim a field 
 *
 * @param string $field_name - the name of the field to retrieve
 * @param array $validated - array of validated fields
 * @return bool - true if the field has been set, false if the field is not set
 */
function bw_obtain_field( $field_name, &$validated ) {
  $value = bw_array_get( $_REQUEST, $field_name, '' );
  bw_trace2( $value, "value", true, BW_TRACE_VERBOSE );
  //bw_backtrace();
  if ( is_array( $value ) ) {
    // @TODO - Don't bother performing any validation yet
    foreach( $value as $k => $v ) {
      $value[$k] = stripslashes( trim( $v )) ;
      
    }
  } else {
    $value = stripslashes( trim( $value ));
  }  
  $validated[ $field_name ] = $value;
  if ( $value ) { 
    $valid = true;
  } else {
    $valid = false;
  }
  return( $valid );
}

/**
 * Validate a "post_title" field
 *
 * Does WordPress invoke any functions? Something during the pre_post_insert ?
 * 
 */
function bw_validate_function_title(  $abbrev, $fields, &$validated ) { 
  $valid = bw_obtain_field( "post_title", $validated );
  if ( $valid ) {
    $validated['post_title'] = sanitize_text_field( $validated['post_title'] );
  }
  return( $valid  );
}

/**
 * Validate a "post_content" field
 * 
 * Do we want to allow HTML or not?
 * Do we need to stripslashes multiple times?
 */
function bw_validate_function_content( $abbrev, $fields, &$validated ) {
  $valid = bw_obtain_field( "post_content", $validated );
  if ( $valid ) {
		//bw_trace2( $validated['post_content'], "before", false, BW_TRACE_DEBUG );
    $validated['post_content'] = wp_kses_data( $validated['post_content'] );
		//bw_trace2( $validated['post_content'], "after", false, BW_TRACE_DEBUG );
  }
  return( $valid );
}

/**
 * Validate a #required field as having a value
 * 
 * If required then the field must be set ($valid=true). Otherwise we don't care
 * 
 * @param bool $valid - whether or not a non-blank field was obtained
 * @param string $field - the field name
 * @param array $data 
 * @return bool true if valid  
 */
function _bw_field_validation_required( $valid, $field, $data ) {   
  if ( !$valid ) {
    $required = bw_array_get( $data['#args'], "#required", false );
    if ( $required ) {
      $text = sprintf( __( "Please enter a value for %s" ),  $data['#title'] );
      bw_issue_message( $field, "bw_field_required", $text );
    } 
  } else { 
    $valid = true;
  }
  return( $valid );
}

/**
 * Perform field validation/sanitization based on #field_type and $field name
 * 
 * @param string $field - field name of the custom post type's field
 * @param array $validated - array of validated fields
 *
 * 
 * $data contains the definition of the field e.g.
 
  <code>
      [#field_type] => rating
      [#title] => Rating
      [#args] => 
  </code>
 */ 
function bw_field_validation( $field, &$validated ) {
  global $bw_fields;
  $data = $bw_fields[$field];
	bw_trace2( $data, "data", true, BW_TRACE_DEBUG );
  $field_type = $data['#field_type'];
  $valid = bw_obtain_field( $field, $validated ); 
  $valid = _bw_field_validation_required( $valid, $field, $data );
  
  $value = $validated[$field];
  $value = apply_filters( "bw_field_validation_{$field_type}", $value, $field, $data ); 
  
  $value = apply_filters( "bw_field_validation_{$field}", $value, $field, $data );
  $validated[$field] = $value;
  
  /** How do we determine if it's valid or not? 
      by comparing $validated[$field] with $value
      OR by checking for messages?
  */
  return( $valid );
}

/**
 * Validate the custom fields for the Add new form
 *
 * For each field obtain the value then call bw_field_validation() 
 *  
 * @param string $abbrev - expected to be '_'
 * @param array $fields - array of custom fields to validate
 * @param array $validated - array of (all) validated fields
 * @return bool 
 *
 */
function bw_validate_function_fields( $abbrev, $fields, &$validated ) {
  $valid = true;
  foreach ( $fields as $field ) {
    $valid &= bw_field_validation( $field, $validated );   
  }
	//bw_trace2();
  return( $valid );
}

/**
 * Return the array of functions for validating particular fields in a form
 *
 * Note: for validating "custom fields" we call the field validation routine(s) defined for the field; either by field_type or field name
 * @uses "bw_validate_functions" filter 
 */
function _bw_validate_functions() {
  static $fields;
  if ( is_null( $fields) ) {
    $fields = array();
    $fields['T'] = "bw_validate_function_title";
  //$fields['I'] = "bw_validate_function_image"; 
  //$fields['F'] = "bw_validate_function_image"; 
    $fields['C'] = "bw_validate_function_content"; 
    $fields['E'] = "bw_validate_function_excerpt"; 
  //$fields['M'] = "bw_validate_function_readmore"; 
  //$fields['R'] = "bw_validate_function_readmore"; 
  //$fields['L'] = "bw_validate_function_link"; 
  //$fields['A'] = "bw_validate_function_anchor"; 
  //$fields['/'] = "bw_validate_function_div"; 
  //$fields[' '] = "bw_validate_function_nbsp"; 
  //$fields['c'] = "bw_validate_function_categories"; 
  //$fields['o'] = "bw_validate_function_comments"; 
  //$fields['t'] = "bw_validate_function_tags"; 
  //$fields['a'] = "bw_validate_function_author"; 
  //$fields['d'] = "bw_validate_function_date"; 
  //$fields['e'] = "bw_validate_function_edit"; 
    $fields['_'] = "bw_validate_function_fields"; 
    // Apply_filters to allow other formatting functions provided by other plugins 
    $fields = apply_filters( "bw_validate_functions", $fields );
  }
  return( $fields );
} 

/**
 * Call the function to validate a field
 * 
 * Finds the function that will validate the 'field' and invokes it
 * 
 * @param string $abbrev - the single character abbreviation representing the field to validate
 * @param array $fields - array of custom post type field names expected 
 * @param array $validated - array of validated fields
 * @return bool - true if validation was successful
 */
function bw_call_validate_function( $abbrev, $fields, &$validated ) {
  $functions = _bw_validate_functions();
  $function = bw_array_get( $functions, $abbrev, "bw_validate_function_undefined" );
	bw_trace2( $function, "function", true, BW_TRACE_VERBOSE );
  $valid = call_user_func_array( $function, array( $abbrev, $fields, &$validated ) );
  //bw_trace2( $valid, "valid?", false );
  return( $valid );
}
   
/**
 * Validate the fields in the form
 *
 * This is the validation of the main fields: Title, Content, and the custom fields
 * If the form doesn't have certain fields, such as post_title, then we need to
 * ensure that the final validation sets the values that are missing.
 *
 * @param string $format - the expected format of the set of fields to be validated
 * @param array $fields - array of custom post type field names expected 
 * @param array $validated - array of validated fields
 * @return bool - true if validation was successful
 */
function bw_validate_fields( $format, $fields, &$validated ) {
  $fs = str_split( $format );
  $valid = true;
  foreach ( $fs as $f ) {
    $valid &= bw_call_validate_function( $f, $fields, $validated );
  }
	$valid = apply_filters_ref_array( "oik_add_new_validate", array( $valid, $format, $fields, &$validated ) );
  return( $valid );
}  

/** 
 * Validate the Add new form to match what's expected
 *
 * @param string $post_type 
 * @param array $validated
 * @return bool - true if validated
 */
function bw_validate_form_as_required( $post_type, &$validated ) {
  $handle = apply_filters( "oik_add_new_{$post_type}", true );
  if ( $handle ) { 
    $format = apply_filters( "oik_add_new_format_{$post_type}", bw_add_new_format_default() , $post_type );
    $fields = bw_add_new_fields_defaults( $post_type ); 
    $fields = apply_filters( "oik_add_new_fields_{$post_type}", $fields, $post_type );
    $valid = bw_validate_fields( $format, $fields, $validated );
    $valid = !bw_query_messages();
  } else {
    p( "Add new not supported for $post_type.");
    $valid = false; 
  }  
  return( $valid ); 
}

/**
 * Determine post_status for new post
 *
 * The post status depends on the the authority of the submitting user
 * 
 * current_user_can | status
 * ---------------- | ----------------
 * publish_pages | publish
 * -              | pending
 * 
 */
function bw_determine_post_status( $post_type, &$validated ) {
	$post_status = 'pending';
	if ( current_user_can( 'publish_pages' ) ) {
		$post_status = "publish";
	}
	$validated['post_status'] = $post_status;
	return( $post_status );
}

/**
 * Insert a post of the specified post type with custom fields set from the validated fields
 * 
 * @TODO We may want to add our own additions to the post_content.
 * e.g. for "dtib_review" we want to add <!--more -->[bw_fields]
 * What's the best way of doing this?
 *
 * @TODO This assumes that we will have set the post_title, post_content and post_status in $validated.
 * What do we do if we haven't? 
 * 
 *
 * @param string $post_type 
 * @param array $validated - containing both post fields and post meta data fields
 * @return ID post ID of the created post
 */
function bw_insert_post( $post_type, $validated ) {
  $post = array( 'post_type' => $post_type
               , 'post_title' => $validated['post_title']
               , 'post_name' => $validated['post_title']
               , 'post_content' => $validated['post_content']
               );
	$post = bw_set_validated_field( $post, $validated, 'post_status' );							 
	$post = bw_set_validated_field( $post, $validated, "post_date" );							 
  /* Set metadata fields */
  $_POST = $validated;
  $post_id = wp_insert_post( $post, TRUE );
  bw_trace2( $post_id, "post_id", true, BW_TRACE_DEBUG );
  return( $post_id );
}

/**
 * Set a post field if validated
 * 
 * @param array $post current settings for the new post object
 * @param array $validated validated fields
 * @param string $field the name of the field to set
 * @return array the updated post array
 */
function bw_set_validated_field( $post, $validated, $field ) {
	if ( isset( $validated[ $field ] ) ) {
		$post[ $field ] = $validated[ $field ];
	}
	return( $post );
}

/**
 * Perform an Akismet spam check against the submitted form
 * 
 * We don't know what fields are in the form so we have to ask the post type to respond with the fields that they want to set.
 * The filter is targetted at the specific $post_type
 */
function bw_spam_check( $post_type, $validated ) {
  $fields = $validated;
  $fields['comment_type'] = $post_type; 
  $fields = apply_filters( "oik_set_spam_fields_{$post_type}", $fields );
  bw_trace2( $fields, "fields", true, BW_TRACE_DEBUG ); 
  $send = bw_akismet_check( $fields );
  return( $send );
}

/**
 * Get an edit post link for use in emails
 *
 * This function should generate the link regardless of the current user's capability
 * get_edit_post_link() won't do this.
 * 
 */
function bw_get_edit_post_link( $post_type, $post ) {
  $post_type_object = get_post_type_object( $post_type );
  $action = '&amp;action=edit';
  $epl = admin_url( sprintf( $post_type_object->_edit_link . $action, $post) );
  return( $epl );
}

/**
 * Send an email to the post author informing them of a new entry to review
 *
 * @param array $atts - shortcode parameters
 * @param array $validated - array of fields
 * @param bool $valid - true if the form was considered valid and passed the spam check (if activated)
 * @param bool $sent - post_id of the inserted post 
 */
function bw_notify_author_email( $atts, $validated, $valid, $sent ) {  
  bw_trace2( null, null, true, BW_TRACE_DEBUG );
  $email_to = bw_array_get( $atts, "email", null  );
  if ( $email_to !== "n" ) {
    if ( !$email_to ) {
      $email_to = bw_get_option_arr( "email", null, $atts );
    }  
    $subject = "New pending content: " . $atts['post_type'] ;
    $message = bw_return_fields( $validated );
    $message .= '<br />';
    $link = bw_get_edit_post_link( $atts['post_type'], $sent ); 
    $message .= retlink( null, $link, "Approve new post $sent" );
    $fields = array();
    $fields['message'] = $message;
    $fields['contact'] = $email_to;
    $fields['from'] = $email_to;
    oik_require( "includes/oik-contact-form-email.php" );
    $sent = bw_send_email( $email_to, $subject, $message, null, $fields );
  }  
  return( $sent );
}

/**
 * Return the fields to be printed in the email body
 * 
 * Note: Some fields may not be correctly "printed" 
 * **?** e.g. Rating fields which are displayed using JavaScript 2013/09/05
 * 
 */ 
function bw_return_fields( $validated ) {
  bw_push();
  bw_format_meta( $validated );
  $ret = bw_ret();
  bw_pop();
  return( $ret );
}

/**
 * Process a new post form submission
 *
 * Handle the new post form submission
 * 1. Check fields
 * 2. Perform spam checking
 * 3. Insert post_type
 * 4. Display "thank you" message
 * 5. Optionally, send an email to the post's author
 * 
 * In order to have reached here the following checks have been performed
 * 1. the Add new submit button with the matching "oik_new_post-nnn" form ID has been pressed
 * 2. the nonce field has been checked
 * 
 * So this means we can perform our field validation and create the new post 
 * by examining the $atts for this shortcode
 
 * @return bool - true if new post added, false if form need to be redisplayed. 
 */
function _bw_process_new_post_form_oik( $atts) {
  $post_type = bw_array_get( $atts, "post_type", null );
  $validated = array();
  $valid = bw_validate_form_as_required( $post_type, $validated );
	bw_trace2( $validated, "validated", true, BW_TRACE_DEBUG );
  if ( $valid ) {
    $valid = bw_spam_check( $post_type, $validated );
    if ( $valid ) {
		$post_status = bw_determine_post_status( $post_type, $validated );
        $sent = bw_maybe_insert_post( $post_type, $validated, $atts );
		bw_set_post_terms( $post_type, $validated, $sent );
		if ( $post_status != "publish" ) {
			bw_notify_author_email( $atts, $validated, $valid, $sent );
		}
    } else {
      $sent = true; 
    }    
    bw_thankyou_message( $validated, $valid, $sent );
  } else {
    $sent = false; 
    $displayed = bw_display_messages();
    if ( !$displayed ) {
      p( "Invalid input. Please corrrect and retry." );
    }  
  }
  return( $sent );
}

/**
 * Return a required field marker
 *
 * Provide a visual indicator that the field is required. 
 *
 * @return string - span of class required with an asterisk representing required
 * 
 */
function bw_default_required_marker() {
  return( '<span class="required">*</span>' ) ;
}

/**
 * Set the field to required if that's the case.
 * 
 * Sets #extras to "required" for HTML5 validation of the field
 * AND appends a required field marker to the field title
 * 
 * @param array $data - field data
 * @return array $data - modified field data
 */ 
function _bw_form_required_field( $data ) {
  $required = bw_array_get( $data['#args'], "#required", false );
  if ( $required ) { 
    $data['#args']['#extras'] = "required"; 
    $data['#title'] .= bw_default_required_marker(); 
  }
  return( $data );
}
 
/**
 * Display the required fields in the form
 *
 * Note: We ignore any fields that have the additional attribute of #form => false
 *
 * @param array $fields - array of field definitions to be completed for this post type
 *
 */
function _bw_show_new_post_fields( $fields ) {
  global $bw_fields;
  //bw_backtrace();
  if ( count( $fields )) {
    bw_trace2( $fields, "fields", false, BW_TRACE_VERBOSE ) ;
    foreach ( $fields as $field ) {
      $data = $bw_fields[$field];
      $value = "";
      $form = bw_array_get( $data['#args'], "#form", true );
      if ( $form ) {
        $data = _bw_form_required_field( $data );
        bw_form_field( $field, $data['#field_type'], $data['#title'], $value, $data['#args'] );
      }  
    }
  }
}

/**
 * Notes about implementing filters for [bw_new] shortcode, using $post_type = "post" as an example
 * 
 * You only need to implement the "oik_add_new_{$post_type}" filter if you don't want the default processing which is "true"
 * e.g.
 * add_filter( "oik_add_new_post", "__return_false" );
 *
 * The default format for the Add new form is provided by bw_add_new_format_default()
 * 
 * add_filter( "oik_add_new_format_post", "bw_add_new_format_default" );
 *
 * The default fields for the Add new form are provided by bw_add_new_fields_defaults( $post_type )
 * The $post_type is passed as the second parameter
 * This code is WRONG, as the function only expects one parameter 
 * 
 * add_filter( "oik_add_new_fields_post", "bw_add_new_fields_defaults", 10, 2 );
 * 
add_filter( "oik_set_spam_fields_post", "bw_set_spam_fields" );   
  
*/


/**
 * Return the default format for the "Add new" form
 * @return string "TC_" - where T=Title,C=Content,_=Fields
 *
 */
function bw_add_new_format_default() {
  return( "TC_" );
}  

/**
 * Return all the fields to be used in an "Add new" form
 * 
 * Similar to bw_get_field_names() this gets the names of the fields to be displayed on the "Add new" form
 *
 * @param string $post_type - the post_type to find fields for
 * @return array $names - the array of registered fields for the post_type
 *  
 */
function bw_add_new_fields_defaults( $post_type ) {
  global $bw_mapping;
  $names= array(); 
  //bw_trace2( $bw_mapping );
  //bw_backtrace();
  if ( isset( $bw_mapping['field'][$post_type] )) {
    foreach ( $bw_mapping['field'][$post_type] as $field ) {
      $names[] = $field;
    }
  }
  return( $names );
}

/**
 * Set default values for fields for spam checking
 * 
 * The $fields array is expected to contain the following keys:
 *  'comment_type' - the custom post type
 *  'comment_author' - author name
 *  'comment_author_email' - author's email address
 *  'comment_author_url' - author's web address
 *  'comment_content' - the content of the CPT
 *  
 * @param array $fields - array of field names and values
 * @return array $fields
 */
function bw_set_spam_fields( $fields ) {
  $user = wp_get_current_user();
  if ( $user ) {
    $fields['comment_author'] = $user->user_firstname . " " . $user->user_lastname;
    $fields['comment_author_email'] = $user->user_email;
    $fields['comment_author_url'] = $user->user_url;
  } else {
    $fields['comment_author'] = $fields['post_title'];
    $fields['comment_author_email'] = null;
    $fields['comment_author_url'] = null;
  }    
  $fields['comment_content'] = $fields['post_content']; 
  bw_trace2( null, null, true, BW_TRACE_VERBOSE );
  return( $fields ); 
}

/**
 * Invoke the function to display the field in a form
 * 
 * @param string $abbrev - the single character abbreviation for the field
 * @param array $fields - array of fields 
 */
function bw_call_form_function( $abbrev, $fields ) {
  //bw_backtrace();
  $functions = _bw_form_functions();
  $function = bw_array_get( $functions, $abbrev, "bw_form_function_undefined" );
  bw_trace2( $function, "function", true, BW_TRACE_VERBOSE );
  call_user_func( $function, $abbrev, $fields );
}

/**
 * Display the post title field
 */
function bw_form_function_title() {
  bw_form_field( "post_title", "text", "Title", null, null );
}
  
/**
 * Display the post content field
 */
function bw_form_function_content() {
  bw_form_field( "post_content", "textarea", "Content", null, null );
}

/**
 * Display the post excerpt field
 */
function bw_form_function_excerpt() {
  bw_form_field( "post_excerpt", "textarea", "Excerpt", null, null );
} 

/**
 * Display form input versions for the selected fields
 *
 * Note: if a field is complex then this should be managed by the field's form and field functions
 */
function bw_form_function_fields( $f, $fields ) {
  _bw_show_new_post_fields( $fields );
}

/**
 * Display an undefined field format as a text field
 */
function bw_form_function_undefined( $f, $fields ) {
  bw_form_field( "undefined_$f", "text", "Undefined: $f", null, null );
} 

/**
 * Return the array of functions for displaying particular fields in a form
 *
 */
function _bw_form_functions() {
  static $fields;
  if ( is_null( $fields) ) {
    $fields = array();
    $fields['T'] = "bw_form_function_title";
  //$fields['I'] = "bw_form_function_image"; 
  //$fields['F'] = "bw_form_function_image"; 
    $fields['C'] = "bw_form_function_content"; 
    $fields['E'] = "bw_form_function_excerpt"; 
  //$fields['M'] = "bw_form_function_readmore"; 
  //$fields['R'] = "bw_form_function_readmore"; 
  //$fields['L'] = "bw_form_function_link"; 
  //$fields['A'] = "bw_form_function_anchor"; 
  //$fields['/'] = "bw_form_function_div"; 
  //$fields[' '] = "bw_form_function_nbsp"; 
  //$fields['c'] = "bw_form_function_categories"; 
  //$fields['o'] = "bw_form_function_comments"; 
  //$fields['t'] = "bw_form_function_tags"; 
  //$fields['a'] = "bw_form_function_author"; 
  //$fields['d'] = "bw_form_function_date"; 
  //$fields['e'] = "bw_form_function_edit"; 
    $fields['_'] = "bw_form_function_fields"; 
    // Apply_filters to allow other formatting functions provided by other plugins 
    $fields = apply_filters( "bw_form_functions", $fields );
  }
  return( $fields );
} 

/**
 * Format the "Add new" form as specified by the parameters
 * 
 * The format= parameter is used to specify the fields to be displayed.
 * Each field or metadata has a single digit code.
 * The output is written to the internal buffer used by all shortcodes.
 *
 * @param string $format - the form's format e.g. TC_ - for Title Content Fields
 * @param array $atts - the attributes
 */
function bw_form_as_required( $format, $fields ) {
  bw_trace2( null, null, true, BW_TRACE_DEBUG );
  $fs = str_split( $format );
  foreach ( $fs as $f ) {
    bw_call_form_function( $f, $fields );
  }
}

/**
 * Show the "oik" new post form
 * 
 * This form contains the fields specified in "format" for the required "post_type"
 * with the "format" being determined by the post type in response to "oik_add_new_format_$post_type" filter
 * and the "fields" being determined by the post type in response to "oik_add_new__fields" filter
 */
function _bw_show_new_post_form_oik( $atts ) {
  $post_type = bw_array_get( $atts, "post_type", null );
  bw_context( "post_type", $post_type );
  $handle = apply_filters( "oik_add_new_{$post_type}", true );
  if ( $handle ) { 
    $format = apply_filters( "oik_add_new_format_{$post_type}", bw_add_new_format_default() , $post_type );
    $fields = bw_add_new_fields_defaults( $post_type ); 
    $fields = apply_filters( "oik_add_new_fields_{$post_type}", $fields, $post_type );
    oik_require( "bobbforms.inc" );
    $class = bw_array_get( $atts, "class", "bw_new_post" );
    sdiv( $class );
    bw_form_tag( $format );
    stag( "table" ); 
    bw_form_as_required( $format, $fields );
    etag( "table" );
    e( wp_nonce_field( "_oik_new_post_form", "_oik_new_post_nonce", false, false ) );
    //e( ihidden( "oiku_email_to", $email_to ) );
    
    $text = bw_get_add_new_button_text( $post_type ); 
    e( isubmit( bw_new_post_form_id(), $text, null ) );
    etag( "form" );
    ediv();
  }
}

/**
 * Display the "Add new" button for the post type.
 * 
 * @param string $post_type - expected to be a valid post type 
 * @return string - more often than not the add_new_item label.
 */
function bw_get_add_new_button_text( $post_type ) { 
  $post_type_object = get_post_type_object( $post_type );
  if ( $post_type_object ) {
    $add_new_item = $post_type_object->labels->add_new_item;
  } else {  
    $add_new_item = sprintf( __( "Add new %s" ), $post_type );
  }  
  return( $add_new_item ); 
} 

/**
 * Show/process a new post form using oik
 *
 * 
 *
 * @param array $atts shortcode parameters
 * @param ? $user 
 */
function bw_display_new_post_form( $atts, $user=null ) {
  oik_require( "shortcodes/oik-contact-form.php" );
  $new_post_form_id = bw_new_post_form_id( true );
  $new_post = bw_array_get( $_REQUEST, $new_post_form_id, null );
  if ( $new_post ) {
    oik_require( "bobbforms.inc" );
    $new_post = bw_verify_nonce( "_oik_new_post_form", "_oik_new_post_nonce" );
    if ( $new_post ) {
      $new_post = _bw_process_new_post_form_oik( $atts );
    }
  }
  if ( !$new_post || is_user_logged_in() ) { 
    _bw_show_new_post_form_oik( $atts, $user );
  }
}

/**
 * Create the form tag
 *
 */
function bw_form_tag( $format ) {
	$extras = null;
	if ( false !== strpos( $format, "I" ) ) {
		$extras = kv( "enctype", "multipart/form-data" );
	}
	if ( false !== strpos( $format, "F" ) ) {
		$extras = kv( "enctype", "multipart/form-data" );
	}
	bw_form( "", "post", null, $extras );
}

/**
 * Insert any post terms
 * 
 * @param string $post_type
 * @param array $validated array of validated fields
 * @param ID post ID
 */
function bw_set_post_terms( $post_type, $validated, $post_ID ) {
	//bw_trace2( );
	$fields = bw_get_field_names( $post_ID );
	foreach ( $fields as $field_name ) { 
		$field_type = bw_query_field_type( $field_name ); 
		if ( $field_type === "taxonomy" ) {
			$term_value = bw_array_get( $validated, $field_name, null );
			if ( $term_value ) { 
				wp_set_post_terms( $post_ID, $term_value, $field_name );
			}
		}	
	}
}

/**
 * Inserts a new post or updates an existing post.
 *
 * - Only allows update of an existing post for logged in users who can publish posts immediately.
 * - Only performs an update if a post with the same post title already exists.
 * - See comments for get_page_by_title() for which post that might be.
 * - Depends on a filter function for the 'bw_new_pre_update_post' filter to apply the fields in $validated to the $post.
 * - Updates other fields via $_POST. Note: these aren't filtered.
 * - If the filter function returns null then the existing post is not updated.
 *
 * @param $post_type
 * @param $validated
 * @return ID
 */
function bw_maybe_insert_post( $post_type, $validated, $atts ) {
   $allow_update = ( 'publish' === $validated['post_status'] );
   if ( $allow_update) {
       $post = bw_get_page_by_title($validated['post_title'], ARRAY_A, $post_type);
       //echo $validated['post_title'];
       if ( $post ) {
           $post = apply_filters( 'bw_new_pre_update_post', $post, $validated );
           //echo "Updating" ;
           if ( $post ) {
               //print_r(  $post );
               $_POST = $validated;
               $result = wp_update_post($post, true);
               bw_trace2($result, "result", true, BW_TRACE_DEBUG);
               //print_r( $result );
               return $post['ID'];
           }
       }
   }
   $post_id = bw_insert_post( $post_type, $validated );
   return $post_id;
}

/**
 * Gets the first page by title.
 *
 * Replaces the get_page_by_title() function which was deprecated in WordPress 6.2 by the fix for TRAC #57041.
 *
 * @param $post_title
 * @param $output
 * @param $post_type
 * @return WP_Post|null
 */
function bw_get_page_by_title( $post_title, $output, $post_type ) {
    $post = null;
    $args = [
        'post_type' => $post_type,
        'title'     => $post_title,
        'orderby' => 'post_date',
        'order' => 'asc',
        'suppress_filters' => true
        ];
    $query = new WP_Query( $args );
    $posts = $query->get_posts();
    bw_trace2( $posts, "posts", true, BW_TRACE_VERBOSE );
    if ( count( $posts )) {
        // Return the first post found in the output format chosen.
        $post = get_post( $posts[0]->ID, $output );
    }
    return $post;
}