![Logo](images/logo-for-readme.jpg)

# Affiliates and Partners for CP

This plugin allows to manage any kind of directory: affiliates, partners, local resellers, members of your association...

The main goal is to keep it easy, light.
Any customization can be done with css and using filters.

Go to "Affiliates and Partners" menu and add members the same way you add posts or pages.
Use the shortcode in the pages where you want listing to appear.

This plugin has no configuration options. You can adapt it to your needs by using filters.
CSS is very small too, so hopefully colors will be inherited from your theme, or you can configure it to look as you want.

### Shortcode

`[apcp-list category="33, 34" css="no" allow_reorder="province, id_number" full_search="yes"]`

Supported parameters:
- *category*: a category ID or a comma separated list of IDs. Default: all.
- *css*: css for this plugin is very short. But if you want you cas skip loading it by setting `css="no"`
- *allow_reorder*: display an "order by". Fields are comma separated.
- *full_search*: display an input field that filters elements with full element search. Default: no.

### Supported custom fields

Native custom fields are implemented and rendered natively.

**Facebook, LinkedIn, Instagram, Twitter, Google.**
You should insert the link pointing to your profile/page. An icon linked to your profile will appear.

**Phone**
You should insert the international phone number. The phone number is displayed with an icon.

**E-Mail**
Insert e-mail in standard form. The mail address is displayed with an icon.

**Website**
Insert website url with protocol (https://my.web.site). The web site is displayed with an icon.

**WhatsApp, Telegram**
For WhatsApp insert your phone number in international format.
For Telegram insert your username (using phone number won't work).
An icon to message you will appear.

**ID number**
Display the ID along a badge icon

**Province**
This field is not shown and can be used for search purposes.


If the field is not set nothing is shown.
If you want to add custom fields is up to you render them at output using filters.

### Filters

`apcp-fields` lets you add, delete or reorder custom fields.
`apcp-element` lets you render custom fields or change the defaults (at element level).
`apcp-element-meta` lets you render custom fields or change the defaults (at meta level).
`apcp-field-render` lets you change the way a field input is rendered.

This example show how to use those filters:

```php
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

// Here we just want to thide facebook field

add_filter( 'apcp-element-meta', 'myprefix_render_fields', 10, 3 );

// $formatted:  the generated HTML to be filtered
// $field_type: field type
// $postmeta:   field value
function myprefix_element_meta ( $formatted, $field_type, $value ) {
	if ( $field_type === 'facebook' ) {
		return false;
	}
	return $formatted;
}

// Here we want to render the "province" field al a select with all italian provinces

add_filter( 'apcp-field-render', 'myprefix_render_fields', 10, 3 );

// $html:      HTML we need to generate metabox
// $field:     field name
// $$post_ID:  ID of current post
function myprefix_capoluoghi ($html, $field, $post_ID) {
	if ($field !== 'province') {
		// Not the right field, don't touch!
		return $html;
	}

	$html = '<select id="apcp-'.$field.'" name="apcp-'.$field.'">';
	
	$current_province = get_post_meta( $post_ID, 'apcp-'.$field, true );
	
	$provincie = ['Agrigento', 'Alessandria', 'Ancona', 'Aosta', 'Arezzo', 'Ascoli Piceno', 'Asti', 'Avellino', 'Bari', 'Barletta-Andria-Trani', 'Belluno', 'Benevento', 'Bergamo', 'Biella', 'Bologna', 'Bolzano', 'Brescia', 'Brindisi', 'Cagliari', 'Caltanissetta', 'Campobasso', 'Caserta', 'Catania', 'Catanzaro', 'Chieti', 'Como', 'Cosenza', 'Cremona', 'Crotone', 'Cuneo', 'Enna', 'Fermo', 'Ferrara', 'Firenze', 'Foggia', 'Forlì-Cesena', 'Frosinone', 'Genova', 'Gorizia', 'Grosseto', 'Imperia', 'Isernia', 'La Spezia', 'L\'Aquila', 'Latina', 'Lecce', 'Lecco', 'Livorno', 'Lodi', 'Lucca', 'Macerata', 'Mantova', 'Massa-Carrara', 'Matera', 'Messina', 'Milano', 'Modena', 'Monza e della Brianza', 'Napoli', 'Novara', 'Nuoro', 'Oristano', 'Padova', 'Palermo', 'Parma', 'Pavia', 'Perugia', 'Pesaro e Urbino', 'Pescara', 'Piacenza', 'Pisa', 'Pistoia', 'Pordenone', 'Potenza', 'Prato', 'Ragusa', 'Ravenna', 'Reggio Calabria', 'Reggio Emilia', 'Rieti', 'Rimini', 'Roma', 'Rovigo', 'Salerno', 'Sassari', 'Savona', 'Siena', 'Siracusa', 'Sondrio', 'Sud Sardegna', 'Taranto', 'Teramo', 'Terni', 'Torino', 'Trapani', 'Trento', 'Treviso', 'Trieste', 'Udine', 'Varese', 'Venezia', 'Verbano-Cusio-Ossola', 'Vercelli', 'Verona', 'Vibo Valentia', 'Vicenza', 'Viterbo',];

	foreach ( $provincie as $provincia ) {
		$html .= '<option value="' . $provincia . '" ' . selected( $provincia, $current_province, false ) . '>' . $provincia . '</option>';
	}
	
	$html .= '</select>';
	
	return $html;
}
```

### Uninstall
When uninstalling all plugin data will be deleted.
Put `define('KEEP_APCP', true)` in your `wp_config.php` to keep them.

### Privacy
**To help us know the number of active installations of this plugin, we collect and store anonymized data when the plugin check in for updates. The date and unique plugin identifier are stored as plain text and the requesting URL is stored as a non-reversible hashed value. This data is stored for up to 28 days.**

