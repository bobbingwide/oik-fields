=== oik-fields ===
Contributors: bobbingwide
Donate link: http://www.oik-plugins.com/oik/oik-donate/
Tags: custom fields, metadata, shortcoes, [bw_field], [bw_fields], [bw_new]
Requires at least: 3.5
Tested up to: 3.8
Stable tag: 1.30
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==
Field formatting for custom post type (CPT) meta data, plus [bw_field], [bw_fields], [bw_new] and [bw_related] shortcodes

=== Features:===
* Advanced API for plugin developers
* Extensible architecture for additional custom field types
* Uses APIs from the oik base plugin

==== Display ====
* Displays custom fields using [bw_field] or [bw_fields] shortcode
* Display related content using [bw_related]
* Displays custom fields in admin pages

==== Input ====
* Displays input versions for custom fields (front-end and admin UI)
* Displays custom fields in Add New / Edit pages
* Performs field validation and sanitization
* Can be used for printing fields in emails

==== Shortcodes ====  
[bw_new]

Allow end users to create "pending" content for a CPT
  
[bw_field] / [bw_fields]

Display custom fields on the page

[bw_related] 

Display related content. Uses the field definition to determine the search criteria

==== Actions and filter hooks ====
Invokes - calls using do_action() or apply_filters()
* tbc

Implements  - ie. responds to 
* "oik_default_meta_value_noderef" - determine the default meta_value for a noderef field type
* the rest tbc


== Installation ==
1. Upload the contents of the oik-fields plugin to the `/wp-content/plugins/oik-fields' directory
1. Activate the oik-fields plugin through the 'Plugins' menu in WordPress

Note: oik-fields is dependent upon the oik base plugin

= What is this plugin for? =
This plugin, working in conjunction with include files from the oik base plugin, provides the support to display meta data associated with custom post types.
It provides basic support for the following types of field:

* checkbox
* currency
* date
* email
* noderef
* numeric
* select, including multi-select
* text
* textarea
* URL
 
Support is also provided for specific fields:

* post_title
* post_content
* excerpt
* bw_header_image

= What other field types are there? =
The following field types are provided by the plugins listed below:

* mshot  - oik-mshot
* rating  - oik-rating
* userref - oik-user
* time/timestamp - oik-dates

= What is oik-fields dependent upon? =
This plugin is dependent upon the oik base plugin. It specifically includes the following files:

```
  oik_require( "includes/bw_register.inc" );
  oik_require( "bw_metadata.inc" );
  oik_require2( "includes/bw_fields.inc", "oik-fields", "oik" ); // When required! 
```  
  
= Fields displayed by default =
Fields are displayed by default in both forms and [bw_fields] displays.
You can control these using two values in the options array

 '#theme' => false - if you don't want the field displayed by [bw_fields]
 '#form' => false - if you don't want the form field displayed by [bw_new]

  
== Frequently Asked Questions ==
= Where is the FAQ? =
[oik FAQ](http://www.oik-plugins.com/oik/oik-faq)

= Is there a support forum? =
Yes - please use the standard WordPress forum - http://wordpress.org/tags/oik?forum_id=10

= Can I get support? = 
Yes - see above 


== Screenshots ==
1. oik-fields displaying custom fields for a custom post type (CPT) called Premium plugins

== Upgrade Notice ==
= 1.30 =
Required if you need the new [bw_related] shortcode

= 1.20 =
If you use oik-fields then please upgrade to this version prior to upgrading the oik base plugins to v2.1-beta.0102 or higher.

= 1.19.1107 = 
Required for PHPHants November 2013

= 1.19.1028                   
Required for Royal Navy Golfing Society www.rngs.org.uk

= 1.19.0905 =
Requires oik v2.1-alpha.0905 for improved logic in [bw_new] shortcode

= 1.19.0803 =
Needed for oik-plugins v1.2

= 1.19.0723 =
Required for dtib_reviews plugin

= 1.19.0718 =
Now works with oik v2.1-alpha.0718. You should be able to upgrade the plugins ( oik-fields and oik ) in any order. 

= 1.18.0325 =
Required for oik-plugins v0.1.0325 and oik-user v0.1.0325, depends on oik v2.0-alpha-0325

= 1.18.0315 =
Required for oik-tunes v0.1.0314 

= 1.18.0302 =
For oik base plugin v2.0-alpha and oik-tunes v0.1.0302

= 1.18.0101 =
This version is dependent upon oik v1.17 or higher

= 1.17 =
This version matches the version in oik v1.17

== Changelog ==
= 1.30 =
* Added: [bw_related] shortcode to list related content
* Added: oik_fields_default_meta_value_noderef filter to return the default value for a a noderef type field 
* Changed: Copyright dates
* Changed: Added readme.md - built from readme.txt. See also [GitHub oik-fields](https://github.com/bobbingwide/oik-fields)

= 1.20 =
* Changed: oik base plugin now implements message issuing APIs, previously developed in shortcodes/oik-new.php. 

= 1.19.1107 = 
* Fixed: Theming for a select field with no value. 

= 1.19.1028 =
* Added: theming for textarea and checkbox fields
* Added: "bw_metadata" action for use in themes or other plugins to display output of "bw_fields"
* Added: Implements "oik_query_field_types" filter to lists the field types it can handle
* Changed: Improved _bw_theme_field_default()
* Changed: bw_funcname() will trace a message when the default field formatting will be used 
* Changed: for [bw_new] shortcode - altered passing by reference to remove messages about deprecation

= 1.19.0905 = 
* Changed: "oik_add_new_fields_${post_type}" filter is now passed the default fields for the post_type, determined using bw_add_new_fields_defaults()
* Added: _bw_show_new_post_form_oik() calls bw_context() for the post_type; used by dtib-review to set the label for the post_title.
* Added: bw_get_edit_post_link() for use in emails 
* Changed: bw_notify_author_email() replaces bw_notify_admin_email() 
* Changed: bw_notify_author_email() sends a "New pending content:" message to the author of the post containing the [bw_new] shortcode
* Added: Some comments about implementing filters used in [bw_new]

= 1.19.0802 =
* Added: Messages for [bw_new] shortcode
* Added: Field validation can be implemented using filters "bw_field_validation_${field_type}" and " bw_field_validation_${field}"
* Added: Default implementation to set spam checking fields to pass to Akismet
* Added: Field validation functions for: text, textarea, email. 
* Changed: Improved validation/sanitization for "title", "post_content"

= 1.19.0723 = 
* Added: Started adding field validation functions ( new file includes/oik-fields-validation.php )
* Added: bw_issue_message() and related functions for creating and displaying messages
* Added: _bw_field_validation_required() to validate required fields
* Added: Visual indicator for required fields
* Added: Support for HTML5 "required"
* Added: Support for HTML5 type="email"
* Added: "oik_set_spam_fields" filter to allow the CPT to determine field values to pass to the spam checker. 
* Added: "bw_validate_functions" filter logic 
* Changed: Renamed bw_validate_field() to bw_obtain_field() to reflect what it's actually doing

= 1.19.0718 =
* Changed: Some functions moved back to the oik base plugin as they were needed by [bw_table]. The file /includes/bw_fields.inc is a copy of the file delivered in oik v2.1
* Added: [bw_new] shortcode to allow end-user creation of certain custom post types

= 1.18.0325 =
* Changed: Improved support for formatting of fields displayed by [bw_fields] and [bw_user] shortcodes
* Added: bw_get_field_names() to return array of registered field names for a post_type
* Added: Separate label and field formatting, 
* Added: Default separator functions: bw_format_sep() and  bw_default_sep()

= 1.18.0315 =
* Added: Support for multiple selection noderef fields 

= 1.18.0302 =
* Changed: noderef fields now formatted as a link in [bw_fields] and custom columns

= 1.18.0101 =
* Added: Dependency logic on the oik base plugin
* Added: phpdoc comments
* Changed: Improved logic for bw_theme_field_url()
* Fixed: Handle value of 0 in bw_return_field_select() 
 
= 1.17 =
* Added: bw_return_field_select() API
* Changed: Ability for some formatting APIs to receive values directly or as array index 0

= earlier =
* Refer to the oik plugin for previous versions of this optional plugin


== Further reading ==
If you want to read more about the oik plugins then please visit the
[oik plugin](http://www.oik-plugins.com/oik) 
**"the oik plugin - for often included key-information"**

