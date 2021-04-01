=== Icons for CP ===

Description:        Manage a directory of affiliates or partners. 
Version:            0.0.1
Requires PHP:       5.6
Requires:           1.1.0
Tested:             4.9.99
Author:             Gieffe edizioni
Author URI:         https://www.gieffeedizioni.it
Plugin URI:         https://software.gieffeedizioni.it
Download link:      https://github.com/xxsimoxx/icons-for-cp/releases/download/v1.1.0/icons-for-cp-1.1.0.zip
License:            GPLv2
License URI:        https://www.gnu.org/licenses/gpl-2.0.html
    
Manage a directory of affiliates or partners.

== Description ==
# Plugin description

This plugin allows to manage any kind of directory: affiliates, partners, local resellers, members of your association...

The main goal is to keep it easy, light.
Any customization can be done with css anf using filters.

Go to "Affiliates and Partners" menu and add members the same way you add posts or pages.
Use the shortcode in the pages where you want listing to appear.

### Supported custom fields

TODO XXXXX custom fields are implemented and rendered natively.
If the field is not set nothing is shown.
If you want to add custom fields is up to you render them at output using filters.

### Filters

`apcp-fields` lets you add or delete custom fields.
`apcp-element` lets you render custom fields or change the defaults.

This example show how to use those filters:

```
add_filter( 'apcp-fields', 'myprefix_add_fields', 10, 1 );

function myprefix_add_fields( $fields ) {

	// Really don't want to display facebook
	unset( $fields['facebook'] );

	// But showing driving license ID is important
	$fields['license_ID'] = 'License ID';
	
	return $fields;	
}

add_filter( 'apcp-element', 'myprefix_render_fields', 10, 3 );

// $content:  the generated HTML to be filtered
// $post:     the post object
// $postmeta: an array containing value of all custom fields
function myprefix_render_fields ( $content, $post, $postmeta ) {

	// Here render your new field, add at the bottom of HTML
	$content .= $post->post_title . ' driver license ID is ' . $postmeta['license_ID'];

	return $content;

}
```

### Shortcode

TODO

### Uninstall
When uninstalling all plugin data will be deleted.
Put `define('KEEP_APCP', true)` in your `wp_config.php` to keep them.

### Privacy
**To help us know the number of active installations of this plugin, we collect and store anonymized data when the plugin check in for updates. The date and unique plugin identifier are stored as plain text and the requesting URL is stored as a non-reversible hashed value. This data is stored for up to 28 days.**


== Screenshots ==
1. TODO


== Changelog ==
= 0.0.1 =
* No changelog until final