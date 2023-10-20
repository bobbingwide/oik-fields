# oik-fields 
![banner](assets/oik-fields-banner-772x250.jpg)
* Contributors: bobbingwide
* Donate link: https://www.oik-plugins.com/oik/oik-donate/
* Tags: custom fields, metadata, shortcodes, [bw_field], [bw_fields], [bw_new], [bw_related]
* Requires at least: 4.9.8
* Tested up to: 6.4-RC1
* Stable tag: 1.54.1
* License: GPLv2 or later
* License URI: http://www.gnu.org/licenses/gpl-2.0.html

## Description 
Field formatting for custom post type (CPT) meta data, plus [bw_field], [bw_fields], [bw_new] and [bw_related] shortcodes

### Features:
* Advanced API for plugin developers
* Extensible architecture for additional custom field types
* Uses APIs from the oik base plugin
* Virtual fields

#### Display 
* Displays custom fields using [bw_field] or [bw_fields] shortcode
* Display related content using [bw_related]
* Displays custom fields in admin pages

#### Input 
* Displays input versions for custom fields (front-end and admin UI)
* Displays custom fields in Add New / Edit pages
* Performs field validation and sanitization
* Can be used for printing fields in emails

#### Shortcodes 
[bw_new]

Allow end users to create content for a CPT

[bw_field]
Display custom fields on the page, without labels or separators

[bw_fields]
Display custom fields on the page, with labels and separators

[bw_related]

Display related content. Uses the field definition to determine the search criteria.
Works for both noderef type fields and date fields ( using oik-dates )
Now supports the format= parameter to display results using the same logic as [bw_pages]
When format=T then it displays results using the same logic as [bw_table]

#### Actions and filter hooks 
Invokes - calls using do_action() or apply_filters()
* oik_fields_loaded

The [bw_new] shortcode invokes the following filters:

* "bw_field_validation_${field_type}"
* "bw_field_validation_${field}"
* "bw_validate_functions"
* "oik_add_new_${post_type}"
* "oik_add_new_format_${post_type}"
* "oik_add_new_fields_${post_type}"
* "oik_set_spam_fields_${post_type}"
* "bw_form_functions", $fields );

The [bw_related] shortcode invokes the following filters:

* "oik_default_meta_value_${field_type}"

Implements  - ie. responds to

* "oik_default_meta_value_noderef" - determine the default meta_value for a noderef field type
* "oik_fields_loaded" - define some standard virtual fields
* the rest tbc


## Installation 
1. Upload the contents of the oik-fields plugin to the `/wp-content/plugins/oik-fields' directory
1. Activate the oik-fields plugin through the 'Plugins' menu in WordPress

* Note: oik-fields is dependent upon the oik base plugin

# What is this plugin for? 
This plugin, working in conjunction with include files from the oik base plugin, provides the support to display meta data associated with custom post types.
It provides basic support for the following types of field:

* checkbox
* currency
* date
* email
* noderef
* numeric
* sctext - text field accepting shortcodes
* sctextarea - text area field accepting shortcodes
* select, including multi-select
* text
* textarea
* URL
* virtual
* serialized

Support is also provided for specific fields:

* post_title
* post_content
* excerpt
* bw_header_image
* post_date
* post_modified

Virtual fields provided:
* file_size  - display the file size in bytes of an attachment
* dimensions - display the width and height of an attached image
* featured   - the featured image is the full size image
* thumbnail  - the thumbnail sized version of the featured image
* author_name - the name of the post's author


# What other field types are there? 
The following field types are provided by the plugins listed below:

* mshot  - oik-mshot
* mshot2 - oik-mshot2
* rating  - oik-rating
* userref - oik-user
* date/time/timestamp - oik-dates
* media - oik-media

# What is oik-fields dependent upon? 
This plugin is dependent upon the oik base plugin. It specifically includes the following files.

```
	if ( oik_fields_check_oik_version( "3.2.1" ) ) {
		oik_require( "includes/bw_register.php" );
		oik_require( "includes/bw_metadata.php" );
		oik_require_lib( "bw_fields" );
	} else {
		oik_require( "includes/bw_register.inc" ); // Yes, we know it's deprecated
		oik_require( "bw_metadata.inc" ); // Yes, we know it's deprecated
		oik_require( "includes/bw_fields.inc" ); // Yes, we know it's deprecated
	}
```

# Fields displayed by default 
Fields are displayed by default in both forms and [bw_fields] displays.
You can control these using two values in the options array

 '#theme' => false - if you don't want the field displayed by [bw_fields]
 '#form' => false - if you don't want the form field displayed by [bw_new]

If you don't want the field's label to be displayed by [bw_fields] add:
 '#label' => false


## Frequently Asked Questions 
# Where is the FAQ? 
[oik FAQ](https://www.oik-plugins.com/oik/oik-faq)


# What are ._field_name and _node_ref?  

For the tag= and category_name= parameters, the code for the [bw_related] shortcode
will lookup the current value for the specified field and pass this as the value for the tag or category slug.

* Use ._field_name when the field is directly attached to the current post.
* Use _node_ref._field_name when the field is attached to the post referenced by the _node_ref field.

# Can I use _node_ref._field_ref with [bw_fields] 
Not yet. But supporting 'fieldref' fields ( field references aka field type 'fieldref') is a planned enhancement.

## Screenshots 
1. oik-fields displaying custom fields for a custom post type (CPT) called Premium plugins

## Upgrade Notice 
# 1.54.1 
Upgrade for support for PHP 8.1 and PHP 8.2


## Changelog 
# 1.54.1 
* Changed: Support PHP 8.1 and PHP 8.2 #38
* Tested: With WordPress 6.4-RC1 and WordPress Multisite
* Tested: With PHP 8.0, PHP 8.1 and PHP 8.2
* Tested: With PHPUnit 9.6


## Further reading 
If you want to read more about the oik plugins then please visit the

[oik base plugin](https://www.oik-plugins.com/oik)
