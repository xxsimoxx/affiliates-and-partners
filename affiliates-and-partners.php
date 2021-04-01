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

//Add auto updater https://codepotent.com/classicpress/plugins/update-manager/
require_once('classes/UpdateClient.class.php');

class AgendaForCP{

	private $metabox_nonce = false;

	public function __construct() {

		require_once('includes/constants.php');

		// Load text domain.
		add_action('plugins_loaded', [$this, 'text_domain']);

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
		load_plugin_textdomain('agenda', false, basename(dirname(__FILE__)).'/languages');
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
			'name'                => __('Item', 'icons-for-cp'),
			'singular_name'       => __('Item', 'icons-for-cp'),
			'add_new'             => __('New item', 'icons-for-cp'),
			'add_new_item'        => __('Add new item', 'icons-for-cp'),
			'edit_item'           => __('Edit item', 'icons-for-cp'),
			'new_item'            => __('New item', 'icons-for-cp'),
			'all_items'           => __('Items', 'icons-for-cp'),
			'view_item'           => __('View item', 'icons-for-cp'),
			'search_items'        => __('Search items', 'icons-for-cp'),
			'not_found'           => __('No items found', 'icons-for-cp'),
			'not_found_in_trash'  => __('No items found in trash', 'icons-for-cp'),
			'menu_name'           => __('Affiliates and Partners', 'icons-for-cp'),
		];
		$args = [
			'public'                => false,
			'show_ui'               => true,
			'rewrite'               => false,
			'supports'              => ['title', 'editor'],
			'labels'                => $labels,
			'exclude_from_search'   => false,
			'register_meta_box_cb'	=> [$this, 'add_meta_boxes'],
			'capabilities'       	=> $capabilities,
			'menu_icon'				=> 'dashicons-awards',
		];
		register_post_type(PREFIX, $args);
	}

	private function list_meta_boxes() {
		$meta = [
				'phone' => __('Phone', 'agenda'),
				'pippo' => __('Pippo', 'agenda'),
				'facebook' => __('Facebook', 'agenda'),
		];
		// apcp-fields filter used to add or remove custom fields
		return apply_filters(PREFIX.'-fields', $meta);
	}

	public function add_meta_boxes() {
		foreach ($this->list_meta_boxes() as $slug => $name) {
			add_meta_box(PREFIX.'-'.$slug, $name, [$this, 'metabox_callback'], null, 'normal', 'default', $slug);
		}
	}

	public function metabox_callback($post, $args) {

		if (!$this->metabox_nonce) {
			wp_nonce_field(basename(__FILE__), PREFIX.'_nonce');
			$this->metabox_nonce = true;
		}

		$field = $args['args'];
		echo '<input type="text" id="'.PREFIX.'-'.$field.'" name="'.PREFIX.'-'.$field.'" size="100" value="'.get_post_meta($post->ID, PREFIX.'-'.$field, true).'">';

	}

	public function save_meta_boxes_data($post_id) {

		if (!isset($_POST[PREFIX.'_nonce']) || !wp_verify_nonce($_POST[PREFIX.'_nonce'], basename(__FILE__))) {
			return;
		}

		if (!current_user_can('manage_options')) {
			return;
		}

		foreach ($this->list_meta_boxes() as $slug => $name) {
			if (!isset($_REQUEST[PREFIX.'-'.$slug])) {
				continue;
			}
			update_post_meta($post_id, PREFIX.'-'.$slug, sanitize_text_field($_REQUEST[PREFIX.'-'.$slug]));
		}

	}

	public function settings_link($links) {
		$link = '<a href="'.admin_url('edit.php?post_type='.PREFIX).'" title="'.__('Settings').'"><i class="dashicon dashicons-edit"></i></a>';
		array_unshift($links, $link);
		return $links;
	}

	public function process_shortcode($atts, $content = null) {
		extract(shortcode_atts([
			'filter' => '',
		], $atts));

		// TODO: filters

		$retval  = '<div class="'.PREFIX.'-container">';

		$allposts = get_posts([
			'post_type'   => PREFIX,
			'post_status' => 'publish',
			'numberposts' => -1,
		]);

		$metas = $this->list_meta_boxes();

		foreach ($allposts as $post) {

			$postmeta = [];
			$data = '';
			foreach ($metas as $slug => $name) {
				$value = get_post_meta($post->ID, PREFIX.'-'.$slug, true);
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

			$current_retval .= '<pre>';
			$current_retval .= print_r($postmeta, true);
			$current_retval .= '</pre>';

			// apcp-element filter used to change how a single element is shown
			$retval         .= apply_filters(PREFIX.'-element', $current_retval, $post, $postmeta);
			
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

new AgendaForCP;