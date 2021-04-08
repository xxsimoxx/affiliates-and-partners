<?php
/**
 * Plugin Name: Affiliates and Partners for CP
 * Plugin URI: https://software.gieffeedizioni.it
 * Description: Affiliates and Partners directory made easy.
 * Version: 0.0.1
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Author: Gieffe edizioni srl
 * Author URI: https://www.gieffeedizioni.it
 * Text Domain: apcp
 * Domain Path: /languages
 */

namespace XXSimoXX\AffiliateAndPartners;

if (!defined('ABSPATH')) {
	die('-1');
}

require_once('classes/UpdateClient.class.php');

class AffiliateAndPartners{

	public function __construct() {

		require_once('includes/constants.php');

		// Load text domain.
		add_action('plugins_loaded', [$this, 'text_domain']);

		// Register CSS and JS.
		add_action('wp_enqueue_scripts', [$this, 'register_style']);
		add_action('wp_enqueue_scripts', [$this, 'register_script']);

		// Register custom post type to store icons.
		add_action('init', [$this, 'register_cpt']);
		add_action('save_post_'.PREFIX, [$this, 'save_meta_boxes_data']);

		// Add link to icons in plugins page
		add_filter('plugin_action_links_'.plugin_basename(__FILE__), [$this, 'settings_link']);

		// Add shortcode
		add_shortcode(PREFIX.'-list', [$this, 'process_shortcode']);

		// Uninstall.
		register_uninstall_hook(__FILE__, [__CLASS__, 'uninstall']);

	}

	public function text_domain() {
		load_plugin_textdomain('apcp', false, basename(dirname(__FILE__)).'/languages');
	}

	public function register_style() {
		wp_register_style('apcp-style', plugins_url('/css/apcp.css', __FILE__));
	}

	public function register_script() {
		wp_register_script('apcp-script', plugins_url('/js/apcp.js', __FILE__));
	}

	public function register_cpt() {
		$capabilities = [
			'edit_post'             => 'manage_options',
			'read_post'             => 'manage_options',
			'delete_post'           => 'manage_options',
			'delete_posts'          => 'manage_options',
			'edit_posts'            => 'manage_options',
			'edit_others_posts'     => 'manage_options',
			'publish_posts'         => 'manage_options',
			'read_private_posts'    => 'manage_options',
		];

		$labels = [
			'name'                => __('Item', 'apcp'),
			'singular_name'       => __('Item', 'apcp'),
			'add_new'             => __('New item', 'apcp'),
			'add_new_item'        => __('Add new item', 'apcp'),
			'edit_item'           => __('Edit item', 'apcp'),
			'new_item'            => __('New item', 'apcp'),
			'all_items'           => __('Items', 'apcp'),
			'view_item'           => __('View item', 'apcp'),
			'search_items'        => __('Search items', 'apcp'),
			'not_found'           => __('No items found', 'apcp'),
			'not_found_in_trash'  => __('No items found in trash', 'apcp'),
			'menu_name'           => __('Affiliates and Partners', 'apcp'),
		];
		$args = [
			'public'                => false,
			'show_ui'               => true,
			'rewrite'               => false,
			'supports'              => ['title', 'editor'],
			'taxonomies' 			=> [PREFIX.'-categories'],
			'labels'                => $labels,
			'exclude_from_search'   => true,
			'register_meta_box_cb'	=> [$this, 'add_meta_boxes'],
			'capabilities'       	=> $capabilities,
			'menu_icon'				=> 'dashicons-awards',
			'publicly_queryable'	=> false,
		];

		register_post_type(PREFIX, $args);

		register_taxonomy(
			PREFIX.'-categories',
			[PREFIX],
			[
				'hierarchical' 		=> true,
				'label' 			=> __('Categories', 'apcp'),
				'singular_label'	=> __('Category', 'apcp'),
			]
		);

	}

	private function list_meta_boxes() {
		$meta = [
				'province'				=> __('Province',  'apcp'),
				'phone'					=> __('Phone',     'apcp'),
				'e_mail'				=> __('E-Mail',    'apcp'),
				'www'					=> __('Website',   'apcp'),
				'whatsapp'				=> __('WhatsApp',  'apcp'),
				'telegram'				=> __('Telegram',  'apcp'),
				'facebook'				=> __('Facebook',  'apcp'),
				'linkedin'				=> __('LinkedIn',  'apcp'),
				'instagram'				=> __('Instagram', 'apcp'),
				'twitter'				=> __('Twitter',   'apcp'),
				'google'				=> __('Google',    'apcp'),
				'id_number'				=> __('ID number', 'apcp'),
		];

		return apply_filters(PREFIX.'_fields', $meta);
	}

	public function add_meta_boxes() {
		foreach ($this->list_meta_boxes() as $slug => $name) {
			add_meta_box(PREFIX.'-'.$slug, $name, [$this, 'metabox_callback'], null, 'normal', 'default', $slug);
		}
	}

	public function metabox_callback($post, $args) {
		$field = $args['args'];
		$html = '<input type="text" id="'.PREFIX.'-'.$field.'" name="'.PREFIX.'-'.$field.'" size="100" value="'.get_post_meta($post->ID, PREFIX.'-'.$field, true).'">';
		echo apply_filters(PREFIX.'_field_render', $html, $field, $post->ID);
	}

	public function save_meta_boxes_data($post_id) {
		foreach (array_keys($this->list_meta_boxes()) as $slug) {
			if (!isset($_REQUEST[PREFIX.'-'.$slug])) {
				continue;
			}
			update_post_meta($post_id, PREFIX.'-'.$slug, sanitize_text_field($_REQUEST[PREFIX.'-'.$slug]));
		}
	}

	public function settings_link($links) {
		$link = '<a href="'.admin_url('edit.php?post_type='.PREFIX).'" title="'.__('Settings', 'apcp').'"><i class="dashicon dashicons-edit"></i></a>';
		array_unshift($links, $link);
		return $links;
	}


	private function format_fields($field_type, $value) { //phpcs:ignore

		$formatted = false;

		switch ($field_type) {
			case 'facebook':
				$formatted  = '<a href="'.$value.'" target="_blank" rel="noopener">';
				$formatted .= '<svg class="apcp-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M400 32H48A48 48 0 0 0 0 80v352a48 48 0 0 0 48 48h137.25V327.69h-63V256h63v-54.64c0-62.15 37-96.48 93.67-96.48 27.14 0 55.52 4.84 55.52 4.84v61h-31.27c-30.81 0-40.42 19.12-40.42 38.73V256h68.78l-11 71.69h-57.78V480H400a48 48 0 0 0 48-48V80a48 48 0 0 0-48-48z"/></svg>';
				$formatted .= '</a>';
				break;

			case 'twitter':
				$formatted  = '<a href="'.$value.'" target="_blank" rel="noopener">';
				$formatted .= '<svg class="apcp-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M400 32H48C21.5 32 0 53.5 0 80v352c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48V80c0-26.5-21.5-48-48-48zm-48.9 158.8c.2 2.8.2 5.7.2 8.5 0 86.7-66 186.6-186.6 186.6-37.2 0-71.7-10.8-100.7-29.4 5.3.6 10.4.8 15.8.8 30.7 0 58.9-10.4 81.4-28-28.8-.6-53-19.5-61.3-45.5 10.1 1.5 19.2 1.5 29.6-1.2-30-6.1-52.5-32.5-52.5-64.4v-.8c8.7 4.9 18.9 7.9 29.6 8.3a65.447 65.447 0 0 1-29.2-54.6c0-12.2 3.2-23.4 8.9-33.1 32.3 39.8 80.8 65.8 135.2 68.6-9.3-44.5 24-80.6 64-80.6 18.9 0 35.9 7.9 47.9 20.7 14.8-2.8 29-8.3 41.6-15.8-4.9 15.2-15.2 28-28.8 36.1 13.2-1.4 26-5.1 37.8-10.2-8.9 13.1-20.1 24.7-32.9 34z"/></svg>';
				$formatted .= '</a>';
				break;

			case 'instagram':
				$formatted  = '<a href="'.$value.'" target="_blank" rel="noopener">';
				$formatted .= '<svg class="apcp-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M224,202.66A53.34,53.34,0,1,0,277.36,256,53.38,53.38,0,0,0,224,202.66Zm124.71-41a54,54,0,0,0-30.41-30.41c-21-8.29-71-6.43-94.3-6.43s-73.25-1.93-94.31,6.43a54,54,0,0,0-30.41,30.41c-8.28,21-6.43,71.05-6.43,94.33S91,329.26,99.32,350.33a54,54,0,0,0,30.41,30.41c21,8.29,71,6.43,94.31,6.43s73.24,1.93,94.3-6.43a54,54,0,0,0,30.41-30.41c8.35-21,6.43-71.05,6.43-94.33S357.1,182.74,348.75,161.67ZM224,338a82,82,0,1,1,82-82A81.9,81.9,0,0,1,224,338Zm85.38-148.3a19.14,19.14,0,1,1,19.13-19.14A19.1,19.1,0,0,1,309.42,189.74ZM400,32H48A48,48,0,0,0,0,80V432a48,48,0,0,0,48,48H400a48,48,0,0,0,48-48V80A48,48,0,0,0,400,32ZM382.88,322c-1.29,25.63-7.14,48.34-25.85,67s-41.4,24.63-67,25.85c-26.41,1.49-105.59,1.49-132,0-25.63-1.29-48.26-7.15-67-25.85s-24.63-41.42-25.85-67c-1.49-26.42-1.49-105.61,0-132,1.29-25.63,7.07-48.34,25.85-67s41.47-24.56,67-25.78c26.41-1.49,105.59-1.49,132,0,25.63,1.29,48.33,7.15,67,25.85s24.63,41.42,25.85,67.05C384.37,216.44,384.37,295.56,382.88,322Z"/></svg>';
				$formatted .= '</a>';
				break;

			case 'linkedin':
				$formatted  = '<a href="'.$value.'" target="_blank" rel="noopener">';
				$formatted .= '<svg class="apcp-svg"  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M416 32H31.9C14.3 32 0 46.5 0 64.3v383.4C0 465.5 14.3 480 31.9 480H416c17.6 0 32-14.5 32-32.3V64.3c0-17.8-14.4-32.3-32-32.3zM135.4 416H69V202.2h66.5V416zm-33.2-243c-21.3 0-38.5-17.3-38.5-38.5S80.9 96 102.2 96c21.2 0 38.5 17.3 38.5 38.5 0 21.3-17.2 38.5-38.5 38.5zm282.1 243h-66.4V312c0-24.8-.5-56.7-34.5-56.7-34.6 0-39.9 27-39.9 54.9V416h-66.4V202.2h63.7v29.2h.9c8.9-16.8 30.6-34.5 62.9-34.5 67.2 0 79.7 44.3 79.7 101.9V416z"/></svg>';
				$formatted .= '</a>';
				break;

			case 'google':
				$formatted  = '<a href="'.$value.'" target="_blank" rel="noopener">';
				$formatted .= '<svg class="apcp-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 488 512"><path d="M488 261.8C488 403.3 391.1 504 248 504 110.8 504 0 393.2 0 256S110.8 8 248 8c66.8 0 123 24.5 166.3 64.9l-67.5 64.9C258.5 52.6 94.3 116.6 94.3 256c0 86.5 69.1 156.6 153.7 156.6 98.2 0 135-70.4 140.8-106.9H248v-85.3h236.1c2.3 12.7 3.9 24.9 3.9 41.4z"/></svg>';
				$formatted .= '</a>';
				break;

			case 'phone':
				$formatted  = '<a href="tel:'.$value.'">';
				$formatted .= '<svg class="apcp-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M400 32H48C21.49 32 0 53.49 0 80v352c0 26.51 21.49 48 48 48h352c26.51 0 48-21.49 48-48V80c0-26.51-21.49-48-48-48zM94 416c-7.033 0-13.057-4.873-14.616-11.627l-14.998-65a15 15 0 0 1 8.707-17.16l69.998-29.999a15 15 0 0 1 17.518 4.289l30.997 37.885c48.944-22.963 88.297-62.858 110.781-110.78l-37.886-30.997a15.001 15.001 0 0 1-4.289-17.518l30-69.998a15 15 0 0 1 17.16-8.707l65 14.998A14.997 14.997 0 0 1 384 126c0 160.292-129.945 290-290 290z"/></svg>';
				$formatted .= $value.'</a>';
				break;

			case 'e_mail':
				$formatted  = '<a href="mailto:'.$value.'">';
				$formatted .= '<svg class="apcp-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M400 32H48C21.49 32 0 53.49 0 80v352c0 26.51 21.49 48 48 48h352c26.51 0 48-21.49 48-48V80c0-26.51-21.49-48-48-48zM178.117 262.104C87.429 196.287 88.353 196.121 64 177.167V152c0-13.255 10.745-24 24-24h272c13.255 0 24 10.745 24 24v25.167c-24.371 18.969-23.434 19.124-114.117 84.938-10.5 7.655-31.392 26.12-45.883 25.894-14.503.218-35.367-18.227-45.883-25.895zM384 217.775V360c0 13.255-10.745 24-24 24H88c-13.255 0-24-10.745-24-24V217.775c13.958 10.794 33.329 25.236 95.303 70.214 14.162 10.341 37.975 32.145 64.694 32.01 26.887.134 51.037-22.041 64.72-32.025 61.958-44.965 81.325-59.406 95.283-70.199z"/></svg>';
				$formatted .= $value.'</a>';
				break;

			case 'www':
				$formatted  = '<a href="'.$value.'">';
				$formatted .= '<svg class="apcp-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M326.612 185.391c59.747 59.809 58.927 155.698.36 214.59-.11.12-.24.25-.36.37l-67.2 67.2c-59.27 59.27-155.699 59.262-214.96 0-59.27-59.26-59.27-155.7 0-214.96l37.106-37.106c9.84-9.84 26.786-3.3 27.294 10.606.648 17.722 3.826 35.527 9.69 52.721 1.986 5.822.567 12.262-3.783 16.612l-13.087 13.087c-28.026 28.026-28.905 73.66-1.155 101.96 28.024 28.579 74.086 28.749 102.325.51l67.2-67.19c28.191-28.191 28.073-73.757 0-101.83-3.701-3.694-7.429-6.564-10.341-8.569a16.037 16.037 0 0 1-6.947-12.606c-.396-10.567 3.348-21.456 11.698-29.806l21.054-21.055c5.521-5.521 14.182-6.199 20.584-1.731a152.482 152.482 0 0 1 20.522 17.197zM467.547 44.449c-59.261-59.262-155.69-59.27-214.96 0l-67.2 67.2c-.12.12-.25.25-.36.37-58.566 58.892-59.387 154.781.36 214.59a152.454 152.454 0 0 0 20.521 17.196c6.402 4.468 15.064 3.789 20.584-1.731l21.054-21.055c8.35-8.35 12.094-19.239 11.698-29.806a16.037 16.037 0 0 0-6.947-12.606c-2.912-2.005-6.64-4.875-10.341-8.569-28.073-28.073-28.191-73.639 0-101.83l67.2-67.19c28.239-28.239 74.3-28.069 102.325.51 27.75 28.3 26.872 73.934-1.155 101.96l-13.087 13.087c-4.35 4.35-5.769 10.79-3.783 16.612 5.864 17.194 9.042 34.999 9.69 52.721.509 13.906 17.454 20.446 27.294 10.606l37.106-37.106c59.271-59.259 59.271-155.699.001-214.959z"/></svg>';
				$formatted .= preg_replace('/^https?:\/\//', '', $value).'</a>';
				break;

			case 'whatsapp':
				$formatted  = '<a href="https://wa.me/'.preg_replace('/^\+?/', '', $value).'" target="_blank" rel="noopener">';
				$formatted .= '<svg class="apcp-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M224 122.8c-72.7 0-131.8 59.1-131.9 131.8 0 24.9 7 49.2 20.2 70.1l3.1 5-13.3 48.6 49.9-13.1 4.8 2.9c20.2 12 43.4 18.4 67.1 18.4h.1c72.6 0 133.3-59.1 133.3-131.8 0-35.2-15.2-68.3-40.1-93.2-25-25-58-38.7-93.2-38.7zm77.5 188.4c-3.3 9.3-19.1 17.7-26.7 18.8-12.6 1.9-22.4.9-47.5-9.9-39.7-17.2-65.7-57.2-67.7-59.8-2-2.6-16.2-21.5-16.2-41s10.2-29.1 13.9-33.1c3.6-4 7.9-5 10.6-5 2.6 0 5.3 0 7.6.1 2.4.1 5.7-.9 8.9 6.8 3.3 7.9 11.2 27.4 12.2 29.4s1.7 4.3.3 6.9c-7.6 15.2-15.7 14.6-11.6 21.6 15.3 26.3 30.6 35.4 53.9 47.1 4 2 6.3 1.7 8.6-1 2.3-2.6 9.9-11.6 12.5-15.5 2.6-4 5.3-3.3 8.9-2 3.6 1.3 23.1 10.9 27.1 12.9s6.6 3 7.6 4.6c.9 1.9.9 9.9-2.4 19.1zM400 32H48C21.5 32 0 53.5 0 80v352c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48V80c0-26.5-21.5-48-48-48zM223.9 413.2c-26.6 0-52.7-6.7-75.8-19.3L64 416l22.5-82.2c-13.9-24-21.2-51.3-21.2-79.3C65.4 167.1 136.5 96 223.9 96c42.4 0 82.2 16.5 112.2 46.5 29.9 30 47.9 69.8 47.9 112.2 0 87.4-72.7 158.5-160.1 158.5z"/></svg>';
				$formatted .= '</a>';
				break;

			case 'telegram':
				$formatted  = '<a href="https://t.me/'.$value.'" target="_blank" rel="noopener">';
				$formatted .= '<svg class="apcp-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 496 512"><path d="M248 8C111 8 0 119 0 256s111 248 248 248 248-111 248-248S385 8 248 8zm121.8 169.9l-40.7 191.8c-3 13.6-11.1 16.9-22.4 10.5l-62-45.7-29.9 28.8c-3.3 3.3-6.1 6.1-12.5 6.1l4.4-63.1 114.9-103.8c5-4.4-1.1-6.9-7.7-2.5l-142 89.4-61.2-19.1c-13.3-4.2-13.6-13.3 2.8-19.7l239.1-92.2c11.1-4 20.8 2.7 17.2 19.5z"/></svg>';
				$formatted .= '</a>';
				break;

			case 'id_number':
				$formatted .= '<a><svg class="apcp-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path d="M336 0H48C21.5 0 0 21.5 0 48v416c0 26.5 21.5 48 48 48h288c26.5 0 48-21.5 48-48V48c0-26.5-21.5-48-48-48zM144 32h96c8.8 0 16 7.2 16 16s-7.2 16-16 16h-96c-8.8 0-16-7.2-16-16s7.2-16 16-16zm48 128c35.3 0 64 28.7 64 64s-28.7 64-64 64-64-28.7-64-64 28.7-64 64-64zm112 236.8c0 10.6-10 19.2-22.4 19.2H102.4C90 416 80 407.4 80 396.8v-19.2c0-31.8 30.1-57.6 67.2-57.6h5c12.3 5.1 25.7 8 39.8 8s27.6-2.9 39.8-8h5c37.1 0 67.2 25.8 67.2 57.6v19.2z"/></svg>';
				$formatted .= $value.'</a>';
				break;

			case 'province':
				$formatted .= '<a><svg class="apcp-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path d="M288 0c-69.59 0-126 56.41-126 126 0 56.26 82.35 158.8 113.9 196.02 6.39 7.54 17.82 7.54 24.2 0C331.65 284.8 414 182.26 414 126 414 56.41 357.59 0 288 0zM20.12 215.95A32.006 32.006 0 0 0 0 245.66v250.32c0 11.32 11.43 19.06 21.94 14.86L160 448V214.92c-8.84-15.98-16.07-31.54-21.25-46.42L20.12 215.95zM288 359.67c-14.07 0-27.38-6.18-36.51-16.96-19.66-23.2-40.57-49.62-59.49-76.72v182l192 64V266c-18.92 27.09-39.82 53.52-59.49 76.72-9.13 10.77-22.44 16.95-36.51 16.95zm266.06-198.51L416 224v288l139.88-55.95A31.996 31.996 0 0 0 576 426.34V176.02c0-11.32-11.43-19.06-21.94-14.86z"/></svg>';
				$formatted .= $value.'</a>';
				break;

		}

		return apply_filters(PREFIX.'_element_meta', $formatted, $field_type, $value);

	}

	public function process_shortcode($atts) { //phpcs:ignore
		extract(shortcode_atts([
			'category'		=> '',
			'css'			=> 'yes',
			'allow_reorder' => '',
			'full_search'   => 'no',
		], $atts));

		$retval  = '';

		$query = [
			'post_type'   		=> PREFIX,
			'post_status' 		=> 'publish',
			'numberposts' 		=> -1,
			'apcp-categories'	=> $category,
			'orderby' 			=> 'title',
			'order' 			=> 'ASC',
		];

		$allposts = get_posts($query);

		$metas = $this->list_meta_boxes();

		if ($css !== 'no') {
			wp_enqueue_style('apcp-style');
		}

		if ($full_search === 'yes' || $allow_reorder !== '') {
			wp_enqueue_script('apcp-script');
		}

		if ($allow_reorder !== '') {

			$retval .= '<div class="'.PREFIX.'-sort">'.__('Sort by', 'apcp');
			$order_elements = explode(',', $allow_reorder);

			foreach ($order_elements as $order_element) {

				$order_element = trim($order_element);

				if (!isset($metas[$order_element])) {
					continue;
				}

				$target = lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', PREFIX.'-'.$order_element))));
				$retval .= ' <a href="#" onClick=\'return sortChildren("'.$target.'")\'>'.$metas[$order_element].'</a>';
			}

			$retval .= '</div>';

		}

		if ($full_search === 'yes') {

			$retval .= '<div class="'.PREFIX.'-search">';
			$retval .= '<input oninput="showOnly();" type="text" id="'.PREFIX.'-search-field" placeholder="'.__('Search for...', 'apcp').'">';
			$retval .= '</div>';

		}

		$retval  .= '<div class="'.PREFIX.'-container" id="'.PREFIX.'-container">';

		foreach ($allposts as $post) {

			$postmeta = [];
			$data = 'data-'.PREFIX.'-titel="'.$post->post_title.'"';
			foreach (array_keys($metas) as $slug) {
				$value = get_post_meta($post->ID, PREFIX.'-'.$slug, true);
				if ($value === '' || $value === false) {
					continue;
				}
				$postmeta[$slug] = $value;
				$data .= 'data-'.PREFIX.'-'.$slug.'="'.$value.'" ';

			}

			$retval         .= '<div class="'.PREFIX.'-element" '.$data.' data-'.PREFIX.'-ID="'.$post->ID.'">';

			$current_retval  = '<h2 class="'.PREFIX.'-title">';
			$current_retval .= $post->post_title;
			$current_retval .= '</h2>';

			$current_retval .= '<span class="'.PREFIX.'-content">';
			$current_retval .= wpautop($post->post_content);
			$current_retval .= '</span>';

			foreach ($postmeta as $meta => $content) {

				if ($content === '') {
					continue;
				}

				$formatted = $this->format_fields($meta, $content);

				if ($formatted === false) {
					continue;
				}

				$current_retval .= '<span class="'.PREFIX.'-field '.PREFIX.'-field-'.$meta.'" >';
				$current_retval .= $formatted;
				$current_retval .= '</span>';

			}

			$retval         .= apply_filters(PREFIX.'_element', $current_retval, $post, $postmeta);

			$retval         .= '</div>';

		}

		$retval .= '</div>';

		return $retval;
	}

	private function warn($message, $line = false, $file = false) {

		if (!defined('WP_DEBUG') || WP_DEBUG !== true) {
			return;
		}

		$caller = debug_backtrace();
		if ($line === false) {
			$line = $caller[0]['line'];
		}
		if ($file === false) {
			$file = $caller[0]['file'];
		}

		if (function_exists('codepotent_php_error_log_viewer_log')) {
			return codepotent_php_error_log_viewer_log($message, 'notice', $file, $line);
		}

		$codepotent_file = plugin_dir_path(__DIR__).'codepotent-php-error-log-viewer/includes/functions.php';
		if (file_exists($codepotent_file)) {
			require_once($codepotent_file);
			return codepotent_php_error_log_viewer_log($message, 'notice', $file, $line);
		}

		trigger_error(print_r($x, true), E_USER_WARNING);

	}


	public static function uninstall() {
		if (defined('\KEEP_APCP') && \KEEP_APCP === true) {
			return;
		}
		$allposts = get_posts([
			'post_type'   => PREFIX,
			'post_status' => 'any',
			'numberposts' => -1,
		]);
		foreach ($allposts as $eachpost) {
			wp_delete_post($eachpost->ID, true);
		}
	}

}

new AffiliateAndPartners;