<?php
/**
 * Plugin Name: Watso – Basic Help Chat Button
 * Plugin URI: https://www.hosteva.com/plugins/watso-basic-chat/?utm_campaign=watso_basic_chat
 * Description: A simple and elegant WhatsApp chat button to support your visitors with multi-number support, UTM tracking, full customization, and scheduling.
 * Version: 1.0.5
 * Author: Hosteva Hosting
 * Author URI: https://www.hosteva.com/?utm_campaign=watso_basic_chat
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: watso-basic-chat
 * Domain Path: /languages
 * Requires at least: 4.9
 * Tested up to: 6.7
 * Requires PHP: 5.6
 */

// Prevent direct access
if (!defined('ABSPATH')) {
	exit;
}

// Define plugin constants
define('WATSO_VERSION', '1.0.5');
define('WATSO_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WATSO_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('WATSO_PLUGIN_FILE', __FILE__);

class WatsoWhatsAppChat {

	private static $instance = null;

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action('init', array($this, 'init'));
		register_activation_hook(__FILE__, array($this, 'activate'));
		register_deactivation_hook(__FILE__, array($this, 'deactivate'));
		register_uninstall_hook(__FILE__, array('WatsoWhatsAppChat', 'uninstall'));
	}

	// Safe debug logging function
	public function debug_log($message, $data = null) {
		$settings = $this->get_settings();

		// Only log if plugin debug mode is enabled
		if (!isset($settings['debug_mode']) || !$settings['debug_mode']) {
			return;
		}

		// Only log if WordPress debug logging is enabled
		if (!defined('WP_DEBUG_LOG') || !WP_DEBUG_LOG) {
			return;
		}

		$timestamp = current_time('Y-m-d H:i:s');
		$log_message = "[Watso Debug {$timestamp}] {$message}";

		if ($data !== null) {
			$log_message .= ' | Data: ' . wp_json_encode($data, true);
		}

		// Write to WordPress standard debug.log only
		if (function_exists('wp_trigger_error')) {
			wp_trigger_error('', $log_message, E_USER_NOTICE);
		} else {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log($log_message);
		}
	}

	private function get_sanitized_settings_from_post() {
		// Nonce verification - admin sayfasında kullanım için
		if (!isset($_POST['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'watso_settings_group-options')) {
			return array();
		}

		if (!isset($_POST['settings']) || !is_array($_POST['settings'])) {
			return array();
		}

		return map_deep(wp_unslash($_POST['settings']), 'sanitize_text_field');
	}

	/**
	 * Verify AJAX nonce for frontend requests
	 *
	 * @return bool
	 */
	private function verify_frontend_nonce() {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- This function IS the nonce verification
		if (!isset($_POST['nonce'])) {
			wp_send_json_error(array('message' => 'Nonce field missing'));
			return false;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- This function IS the nonce verification
		$nonce = sanitize_text_field(wp_unslash($_POST['nonce']));
		if (!wp_verify_nonce($nonce, 'watso_frontend_nonce')) {
			wp_send_json_error(array('message' => 'Nonce verification failed'));
			return false;
		}

		return true;
	}

	/**
	 * Verify AJAX nonce for admin requests
	 *
	 * @return bool
	 */
	private function verify_admin_nonce() {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- This function IS the nonce verification
		if (!isset($_POST['nonce'])) {
			wp_send_json_error(array('message' => 'Nonce field missing'));
			return false;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- This function IS the nonce verification
		$nonce = sanitize_text_field(wp_unslash($_POST['nonce']));
		if (!wp_verify_nonce($nonce, 'watso_nonce')) {
			wp_send_json_error(array('message' => 'Nonce verification failed'));
			return false;
		}

		if (!current_user_can('manage_options')) {
			wp_send_json_error(array('message' => 'Insufficient permissions'));
			return false;
		}

		return true;
	}

	public function init() {
		$this->debug_log('Plugin initialization started');

		// Load text domain
		//load_plugin_textdomain('watso-basic-chat', false, dirname(plugin_basename(__FILE__)) . '/languages');

		// Load includes
		$this->load_includes();

		// Initialize admin
		if (is_admin()) {
			$this->debug_log('Admin area detected, initializing admin features');

			add_action('admin_menu', array($this, 'add_admin_menu'));
			add_action('admin_init', array($this, 'admin_init'));
			add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));

			// AJAX HANDLER
			add_action('wp_ajax_watso_render_preview', array($this, 'ajax_render_preview'));

			// Data cleaned notice
			add_action('admin_notices', array($this, 'show_data_cleaned_notice'));
		}

		// Frontend functionality
		if (!is_admin()) {
			$this->debug_log('Frontend detected, initializing frontend features');

			add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
			add_action('wp_footer', array($this, 'render_chat_button'));
		}

		// AJAX handlers
		add_action('wp_ajax_nopriv_watso_track_click', array($this, 'ajax_track_click'));
		add_action('wp_ajax_watso_track_click', array($this, 'ajax_track_click'));
		add_action('wp_ajax_nopriv_watso_get_current_settings', array($this, 'ajax_get_current_settings'));
		add_action('wp_ajax_watso_get_current_settings', array($this, 'ajax_get_current_settings'));

		$this->debug_log('Plugin initialization completed');
	}

	private function load_includes() {
		$this->debug_log('Loading includes');

		// Load function files
		$includes = array(
			'includes/functions/general.php',
			'includes/functions/appearance.php',
			'includes/functions/numbers.php',
			'includes/functions/tracking.php',
			'includes/functions/schedule.php',
			'includes/functions/advanced.php',
			'includes/class-button-renderer.php'
		);

		foreach ($includes as $file) {
			$file_path = WATSO_PLUGIN_PATH . $file;
			if (file_exists($file_path)) {
				require_once $file_path;
				$this->debug_log('Include loaded successfully', array('file' => $file));
			} else {
				$this->debug_log('Include file not found', array('file' => $file, 'path' => $file_path));
			}
		}
	}

	public function activate() {
		$this->debug_log('Plugin activation started');

		// Get existing settings
		$existing_settings = get_option('watso_settings', array());

		// Get default options
		$default_options = $this->get_default_settings();

		// If no settings exist (first installation), use defaults
		if (empty($existing_settings)) {
			add_option('watso_settings', $default_options);
			$this->debug_log('First installation - default settings added');
		} else {
			// If existing settings exist, only add missing ones
			$updated_settings = wp_parse_args($existing_settings, $default_options);

			// Update UTM settings (force update)
			$updated_settings['utm_enabled'] = true;
			$updated_settings['utm_source'] = 'website';
			$updated_settings['utm_medium'] = 'whatsapp';
			$updated_settings['utm_campaign'] = 'support';

			update_option('watso_settings', $updated_settings);
			$this->debug_log('Existing installation - settings updated');
		}

		// Create uploads directory for icons
		$upload_dir = wp_upload_dir();
		$watso_dir = $upload_dir['basedir'] . '/watso-icons';
		if (!file_exists($watso_dir)) {
			wp_mkdir_p($watso_dir);
			$this->debug_log('Created uploads directory', array('path' => $watso_dir));
		}

		$this->debug_log('Plugin activation completed');
	}

	public function deactivate() {
		$this->debug_log('Plugin deactivation started');

		// Database cleanup check
		$settings = get_option('watso_settings', array());

		if (isset($settings['clean_on_uninstall']) && $settings['clean_on_uninstall']) {
			$this->debug_log('Database cleanup is enabled, removing data');

			// Delete settings
			delete_option('watso_settings');

			// Delete uploaded icons
			$upload_dir = wp_upload_dir();
			$watso_dir = $upload_dir['basedir'] . '/watso-icons';
			if (file_exists($watso_dir)) {
				$files = glob($watso_dir . '/*');
				foreach ($files as $file) {
					if (is_file($file)) {
						wp_delete_file($file);
					}
				}
				// Leave directory empty (to avoid problems on reactivation)
			}

			// Add temporary option for admin notice
			add_option('watso_data_cleaned', true);
			$this->debug_log('Data cleanup completed');
		} else {
			$this->debug_log('Database cleanup not enabled, data preserved');
		}

		$this->debug_log('Plugin deactivation completed');
	}

	public static function uninstall() {
		$settings = get_option('watso_settings', array());
		if (isset($settings['clean_on_uninstall']) && $settings['clean_on_uninstall']) {
			delete_option('watso_settings');

			// Remove uploaded icons
			$upload_dir = wp_upload_dir();
			$watso_dir = $upload_dir['basedir'] . '/watso-icons';
			if (file_exists($watso_dir)) {
				$files = glob($watso_dir . '/*');
				foreach ($files as $file) {
					if (is_file($file)) {
						wp_delete_file($file);
					}
				}
				global $wp_filesystem;
				if (empty($wp_filesystem)) {
					require_once ABSPATH . '/wp-admin/includes/file.php';
					WP_Filesystem();
				}
				$wp_filesystem->rmdir($watso_dir);
			}
		}
	}

	public function add_admin_menu() {
		$this->debug_log('Adding admin menu');

		add_menu_page(
			__('Watso - Basic Chat Settings', 'watso-basic-chat'),
			__('Watso Chat', 'watso-basic-chat'),
			'manage_options',
			'watso-settings',
			array($this, 'admin_page'),
			'dashicons-format-chat',
			30
		);
	}

	public function admin_init() {
		$this->debug_log('Admin init started');

		if (isset($_POST['submit']) && isset($_POST['option_page']) && $_POST['option_page'] === 'watso_settings_group') {
			// Nonce verification için additional check
			if (!isset($_POST['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'watso_settings_group-options')) {
				wp_die(esc_html__('Security check failed. Please try again.', 'watso-basic-chat'));
			}
		}

		// Register settings for each section
		register_setting('watso_settings_group', 'watso_settings', array($this, 'sanitize_settings'));

		// Initialize all sections
		watso_init_general_settings();
		watso_init_appearance_settings();
		watso_init_numbers_settings();
		watso_init_tracking_settings();
		watso_init_schedule_settings();
		watso_init_advanced_settings();

		$this->debug_log('Admin init completed');
	}

	public function admin_enqueue_scripts($hook) {
		if ('toplevel_page_watso-settings' !== $hook) {
			return;
		}

		$this->debug_log('Enqueuing admin scripts');

		wp_enqueue_script('jquery');
		wp_enqueue_script('wp-color-picker');
		wp_enqueue_style('wp-color-picker');
		wp_enqueue_media();

		wp_enqueue_script(
			'watso-admin',
			WATSO_PLUGIN_URL . 'assets/admin.js',
			array('jquery', 'wp-color-picker'),
			WATSO_VERSION,
			true
		);

		wp_enqueue_style(
			'watso-admin',
			WATSO_PLUGIN_URL . 'assets/admin.css',
			array('wp-color-picker'),
			WATSO_VERSION
		);

		wp_localize_script('watso-admin', 'watso_ajax', array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('watso_nonce'),
			'plugin_url' => WATSO_PLUGIN_URL,
			'strings' => array(
				'loading' => __('Loading...', 'watso-basic-chat'),
				'error' => __('An error occurred while loading content.', 'watso-basic-chat')
			)
		));

		$this->debug_log('Admin scripts enqueued successfully');
	}

	public function enqueue_scripts() {
		$settings = $this->get_settings();

		if (!$settings['active']) {
			$this->debug_log('Plugin not active, scripts not enqueued');
			return;
		}

		$this->debug_log('Enqueuing frontend scripts');

		wp_enqueue_script(
			'watso-frontend',
			WATSO_PLUGIN_URL . 'assets/frontend.js',
			array('jquery'),
			WATSO_VERSION,
			true
		);

		wp_enqueue_style(
			'watso-frontend',
			WATSO_PLUGIN_URL . 'assets/frontend.css',
			array(),
			WATSO_VERSION
		);

		wp_localize_script('watso-frontend', 'watso_data', array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('watso_frontend_nonce'),
			'settings' => $settings,
			'current_url' => get_permalink(),
			'debug' => isset($settings['debug_mode']) ? $settings['debug_mode'] : false
		));

		$this->debug_log('Frontend scripts enqueued successfully', array(
			'debug_mode' => $settings['debug_mode'],
			'active_numbers' => count($settings['numbers'])
		));
	}

	public function render_chat_button() {
		$settings = $this->get_settings();

		if (!$settings['active']) {
			$this->debug_log('Plugin not active, chat button not rendered');
			return;
		}

		$this->debug_log('Starting chat button render process');

		// Check if should show on mobile
		if (wp_is_mobile() && !$settings['show_mobile']) {
			$this->debug_log('Mobile device detected but mobile display disabled');
			return;
		}

		$this->debug_log('All checks passed, rendering chat button');

		// USE UNIFIED RENDERER
		WatsoButtonRenderer::render_button($settings, false);

		$this->debug_log('Chat button rendered successfully');
	}

	private function is_within_schedule($settings) {
		$this->debug_log('Checking schedule');

		$current_time = current_time('H:i');
		$current_day = strtolower(gmdate('l'));

		$this->debug_log('Schedule check details', array(
			'current_time' => $current_time,
			'current_day' => $current_day
		));

		// Check holidays
		if (isset($settings['holidays']) && is_array($settings['holidays'])) {
			$today = gmdate('Y-m-d');
			foreach ($settings['holidays'] as $holiday) {
				if (isset($holiday['date']) && $holiday['date'] === $today) {
					$this->debug_log('Today is a holiday', array('holiday' => $holiday));
					return false;
				}
			}
		}

		// Check schedule hours
		if (isset($settings['schedule_hours'][$current_day])) {
			$day_schedule = $settings['schedule_hours'][$current_day];

			$this->debug_log('Day schedule found', array(
				'day' => $current_day,
				'schedule' => $day_schedule
			));

			if (!$day_schedule['enabled']) {
				$this->debug_log('Day not enabled in schedule');
				return false;
			}

			$start_time = $day_schedule['start'];
			$end_time = $day_schedule['end'];

			$within_hours = ($current_time >= $start_time && $current_time <= $end_time);

			$this->debug_log('Time range check', array(
				'start_time' => $start_time,
				'end_time' => $end_time,
				'within_hours' => $within_hours
			));

			return $within_hours;
		}

		$this->debug_log('No specific schedule for today, defaulting to true');
		return true;
	}

	public function admin_page() {
		$this->debug_log('Loading admin page');
		include WATSO_PLUGIN_PATH . 'admin/admin-page.php';
	}

	// IMPROVED DEFAULT SETTINGS - WITH NEW FIELDS
	private function get_default_settings() {
		return array(
			'active' => true,
			'position' => 'bottom-right',
			'show_mobile' => true,
			'button_title' => __('WhatsApp Support', 'watso-basic-chat'),
			'dropdown_header_text' => __('Select a contact', 'watso-basic-chat'),
			'source_message_text' => __('Hello! I am visiting this page:', 'watso-basic-chat'),
			'button_radius' => 15,
			'button_color' => '#119849',
			'short_description_color' => '#888888', // NEW
			'status_text_color' => '#4CAF50', // NEW
			'department_color' => '#666666', // NEW
			'custom_icon' => '',
			'numbers' => array(
				array(
					'number' => '+905551234567',
					'title' => __('John Smith', 'watso-basic-chat'), // Changed to name
					'department' => __('Technical Support', 'watso-basic-chat'), // NEW
					'status_text' => __('Online', 'watso-basic-chat'), // NEW
					'short_description' => '', // NEW - empty default
					'active' => true
				)
			),
			'utm_enabled' => true,
			'utm_source' => 'website',
			'utm_medium' => 'whatsapp',
			'utm_campaign' => 'support',
			'meta_tracking' => true,
			'show_source_url' => true,
			'schedule_enabled' => false,
			'schedule_hours' => array(
				'monday' => array('start' => '09:00', 'end' => '18:00', 'enabled' => true),
				'tuesday' => array('start' => '09:00', 'end' => '18:00', 'enabled' => true),
				'wednesday' => array('start' => '09:00', 'end' => '18:00', 'enabled' => true),
				'thursday' => array('start' => '09:00', 'end' => '18:00', 'enabled' => true),
				'friday' => array('start' => '09:00', 'end' => '18:00', 'enabled' => true),
				'saturday' => array('start' => '09:00', 'end' => '18:00', 'enabled' => false),
				'sunday' => array('start' => '09:00', 'end' => '18:00', 'enabled' => false)
			),
			'holidays' => array(),
			'debug_mode' => false,
			'clean_on_uninstall' => false
		);
	}

	public function get_settings() {
		$defaults = $this->get_default_settings();
		$saved = get_option('watso_settings', array());
		return wp_parse_args($saved, $defaults);
	}

	// IMPROVED SANITIZE FUNCTION - WITH NEW FIELDS
	public function sanitize_settings($input) {
		$this->debug_log('Sanitizing settings', array('input_keys' => array_keys($input)));

		$sanitized = array();

		// Boolean fields - "online_status_text" removed
		$bool_fields = array('active', 'show_mobile', 'utm_enabled', 'meta_tracking', 'show_source_url', 'schedule_enabled', 'debug_mode', 'clean_on_uninstall');
		foreach ($bool_fields as $field) {
			$sanitized[$field] = isset($input[$field]) ? (bool) $input[$field] : false;
		}

		// Text fields
		$sanitized['position'] = sanitize_text_field($input['position'] ?? 'bottom-right');
		$sanitized['button_title'] = sanitize_text_field($input['button_title'] ?? '');
		$sanitized['dropdown_header_text'] = sanitize_text_field($input['dropdown_header_text'] ?? __('Select a contact', 'watso-basic-chat'));
		$sanitized['source_message_text'] = sanitize_text_field($input['source_message_text'] ?? __('Hello! I am visiting this page:', 'watso-basic-chat'));

		// Button radius - support 0 value
		$sanitized['button_radius'] = isset($input['button_radius']) && is_numeric($input['button_radius'])
			? max(0, min(30, (int)$input['button_radius']))
			: 15;

		// Colors
		$sanitized['button_color'] = sanitize_hex_color($input['button_color'] ?? '#119849');
		$sanitized['short_description_color'] = sanitize_hex_color($input['short_description_color'] ?? '#888888'); // NEW
		$sanitized['status_text_color'] = sanitize_hex_color($input['status_text_color'] ?? '#4CAF50'); // NEW
		$sanitized['department_color'] = sanitize_hex_color($input['department_color'] ?? '#666666'); // NEW

		// UTM fields
		$sanitized['utm_source'] = sanitize_text_field($input['utm_source'] ?? '');
		$sanitized['utm_medium'] = sanitize_text_field($input['utm_medium'] ?? '');
		$sanitized['utm_campaign'] = sanitize_text_field($input['utm_campaign'] ?? '');

		// Numbers array - WITH NEW FIELDS
		if (isset($input['numbers']) && is_array($input['numbers'])) {
			$sanitized['numbers'] = array();
			foreach ($input['numbers'] as $number) {
				$sanitized['numbers'][] = array(
					'number' => sanitize_text_field($number['number'] ?? ''),
					'title' => sanitize_text_field($number['title'] ?? ''),
					'department' => sanitize_text_field($number['department'] ?? ''), // NEW
					'status_text' => sanitize_text_field($number['status_text'] ?? ''), // NEW
					'short_description' => sanitize_text_field($number['short_description'] ?? ''), // NEW
					'active' => isset($number['active']) ? (bool) $number['active'] : true
				);
			}
		}

		// Schedule hours
		if (isset($input['schedule_hours']) && is_array($input['schedule_hours'])) {
			$sanitized['schedule_hours'] = array();
			foreach ($input['schedule_hours'] as $day => $schedule) {
				$sanitized['schedule_hours'][$day] = array(
					'start' => sanitize_text_field($schedule['start'] ?? '09:00'),
					'end' => sanitize_text_field($schedule['end'] ?? '18:00'),
					'enabled' => isset($schedule['enabled']) ? (bool) $schedule['enabled'] : false
				);
			}
		}

		// Holidays
		if (isset($input['holidays']) && is_array($input['holidays'])) {
			$sanitized['holidays'] = array();
			foreach ($input['holidays'] as $holiday) {
				$sanitized['holidays'][] = array(
					'date' => sanitize_text_field($holiday['date'] ?? ''),
					'title' => sanitize_text_field($holiday['title'] ?? '')
				);
			}
		}

		// File uploads
		if (isset($input['custom_icon'])) {
			$sanitized['custom_icon'] = esc_url_raw($input['custom_icon']);
		}

		$this->debug_log('Settings sanitized successfully', array(
			'numbers_count' => count($sanitized['numbers'] ?? array()),
			'debug_mode' => $sanitized['debug_mode']
		));

		return $sanitized;
	}

	public function ajax_track_click() {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified in verify_frontend_nonce()
		$this->debug_log('AJAX track click started');

		if (!$this->verify_frontend_nonce()) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified above
		$number = isset($_POST['number']) ? sanitize_text_field(wp_unslash($_POST['number'])) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified above
		$source_url = isset($_POST['source_url']) ? esc_url_raw(wp_unslash($_POST['source_url'])) : '';

		$this->debug_log('Click tracked', array(
			'number' => $number,
			'source_url' => $source_url
		));

		wp_send_json_success(array(
			                     'message' => 'Click tracked successfully'
		                     ));
	}

	public function ajax_get_current_settings() {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified in verify_frontend_nonce()
		if (!$this->verify_frontend_nonce()) {
			return;
		}

		$current_settings = $this->get_settings();

		wp_send_json_success($current_settings);
	}

	// PREVIEW AJAX HANDLER
	public function ajax_render_preview() {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified in verify_admin_nonce()
		$this->debug_log('AJAX render preview started');

		if (!$this->verify_admin_nonce()) {
			return;
		}

		// Get settings using helper method
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified in verify_admin_nonce()
		$raw_settings = isset($_POST['settings']) && is_array($_POST['settings'])
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified in verify_admin_nonce()
			? map_deep(wp_unslash($_POST['settings']), 'sanitize_text_field')
			: array();

		if (empty($raw_settings)) {
			$this->debug_log('No valid settings received');
		}

		$this->debug_log('Raw settings received', array('keys' => array_keys($raw_settings)));

		// Merge with default settings
		$default_settings = $this->get_default_settings();

		// Clean and secure settings
		$clean_settings = array();

		// Boolean fields
		$bool_fields = array('active', 'show_mobile', 'utm_enabled', 'meta_tracking', 'show_source_url', 'schedule_enabled', 'debug_mode', 'clean_on_uninstall');
		foreach ($bool_fields as $field) {
			$clean_settings[$field] = isset($raw_settings[$field]) ? (bool) $raw_settings[$field] : false;
		}

		// String fields
		$clean_settings['position'] = sanitize_text_field($raw_settings['position'] ?? 'bottom-right');
		$clean_settings['button_title'] = sanitize_text_field($raw_settings['button_title'] ?? '');
		$clean_settings['dropdown_header_text'] = sanitize_text_field($raw_settings['dropdown_header_text'] ?? __('Select a contact', 'watso-basic-chat'));
		$clean_settings['source_message_text'] = sanitize_text_field($raw_settings['source_message_text'] ?? __('Hello! I am visiting this page:', 'watso-basic-chat'));

		// Button radius - support 0 value
		$clean_settings['button_radius'] = isset($raw_settings['button_radius']) && is_numeric($raw_settings['button_radius'])
			? max(0, min(30, (int)$raw_settings['button_radius']))
			: 15;

		$clean_settings['button_color'] = sanitize_hex_color($raw_settings['button_color'] ?? '#119849');
		$clean_settings['short_description_color'] = sanitize_hex_color($raw_settings['short_description_color'] ?? '#888888');
		$clean_settings['status_text_color'] = sanitize_hex_color($raw_settings['status_text_color'] ?? '#4CAF50');
		$clean_settings['department_color'] = sanitize_hex_color($raw_settings['department_color'] ?? '#666666');
		$clean_settings['custom_icon'] = esc_url_raw($raw_settings['custom_icon'] ?? '');

		// UTM fields
		$clean_settings['utm_source'] = sanitize_text_field($raw_settings['utm_source'] ?? 'website');
		$clean_settings['utm_medium'] = sanitize_text_field($raw_settings['utm_medium'] ?? 'whatsapp');
		$clean_settings['utm_campaign'] = sanitize_text_field($raw_settings['utm_campaign'] ?? 'support');

		// Fix numbers array - WITH NEW FIELDS
		if (isset($raw_settings['numbers']) && is_array($raw_settings['numbers'])) {
			$clean_settings['numbers'] = array();
			foreach ($raw_settings['numbers'] as $number) {
				if (is_array($number) && !empty($number['number'])) {
					$clean_settings['numbers'][] = array(
						'number' => sanitize_text_field($number['number']),
						'title' => sanitize_text_field($number['title'] ?? 'Support'),
						'department' => sanitize_text_field($number['department'] ?? ''),
						'status_text' => sanitize_text_field($number['status_text'] ?? ''),
						'short_description' => sanitize_text_field($number['short_description'] ?? ''),
						'active' => isset($number['active']) ? (bool) $number['active'] : true
					);
				}
			}
		}

		// If numbers is empty, add default
		if (empty($clean_settings['numbers'])) {
			$clean_settings['numbers'] = array(
				array(
					'number' => '+905551234567',
					'title' => 'John Smith',
					'department' => 'Technical Support',
					'status_text' => 'Online',
					'short_description' => '',
					'active' => true
				)
			);
		}

		// Merge with default settings
		$final_settings = wp_parse_args($clean_settings, $default_settings);

		$this->debug_log('Final settings prepared', array(
			'numbers_count' => count($final_settings['numbers']),
			'debug_mode' => $final_settings['debug_mode']
		));

		// Call renderer and capture HTML
		ob_start();
		try {
			// Check if WatsoButtonRenderer class is loaded
			if (!class_exists('WatsoButtonRenderer')) {
				throw new Exception('WatsoButtonRenderer class not found');
			}

			WatsoButtonRenderer::render_button($final_settings, true);
			$html = ob_get_clean();

			if (empty($html)) {
				throw new Exception('Renderer returned empty HTML');
			}

			$this->debug_log('Preview rendered successfully', array('html_length' => strlen($html)));

			wp_send_json_success(array(
				                     'html' => $html,
				                     'debug' => array(
					                     'settings_count' => count($final_settings),
					                     'numbers_count' => count($final_settings['numbers']),
					                     'html_length' => strlen($html),
					                     'button_radius' => $final_settings['button_radius']
				                     )
			                     ));

		} catch (Exception $e) {
			ob_end_clean();
			$this->debug_log('Preview render error', array(
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine()
			));

			wp_send_json_error(array(
				                   'message' => 'Render error: ' . $e->getMessage(),
				                   'debug' => array(
					                   'file' => $e->getFile(),
					                   'line' => $e->getLine(),
					                   'settings' => $final_settings
				                   )
			                   ));
		}
	}

	public function generate_whatsapp_url($number, $settings, $number_index = 0) {
		$this->debug_log('Generating WhatsApp URL', array(
			'number' => $number,
			'number_index' => $number_index,
			'utm_enabled' => $settings['utm_enabled'] ?? false
		));

		$base_url = 'https://wa.me/';
		$clean_number = preg_replace('/[^0-9+]/', '', $number);

		$message_parts = array();

		// Add source URL if enabled
		if ($settings['show_source_url']) {
			$current_url = get_permalink();
			if (!$current_url) {
				global $wp;
				$current_url = home_url(add_query_arg(array(), $wp->request));
			}

			// Use custom message text, or default if empty
			$source_message = isset($settings['source_message_text']) && !empty(trim($settings['source_message_text']))
				? trim($settings['source_message_text'])
				: __('Hello! I am visiting this page:', 'watso-basic-chat');

			$message_parts[] = $source_message . ' ' . $current_url;

			$this->debug_log('Source URL added to message', array(
				'source_message' => $source_message,
				'current_url' => $current_url
			));
		}

		$message = implode("\n", $message_parts);

		// Add UTM parameters to URL
		$utm_params = array();

		// Add UTM parameters if enabled
		if (isset($settings['utm_enabled']) && $settings['utm_enabled']) {
			if (!empty($settings['utm_source'])) {
				$utm_params[] = 'utm_source=' . urlencode($settings['utm_source']);
			}
			if (!empty($settings['utm_medium'])) {
				$utm_params[] = 'utm_medium=' . urlencode($settings['utm_medium']);
			}
			if (!empty($settings['utm_campaign'])) {
				$campaign = $settings['utm_campaign'];
				// If multiple numbers, add number suffix to campaign
				if ($number_index > 0) {
					$campaign = $campaign . '-' . ($number_index + 1);
				}
				$utm_params[] = 'utm_campaign=' . urlencode($campaign);
			}

			$this->debug_log('UTM parameters added', array(
				'utm_params' => $utm_params,
				'campaign_suffix' => $number_index > 0 ? '-' . ($number_index + 1) : 'none'
			));
		}

		// Build URL
		$url = $base_url . $clean_number;

		$query_params = array();

		// Add message if exists
		if (!empty($message)) {
			$query_params[] = 'text=' . urlencode($message);
		}

		// Add UTM parameters
		if (!empty($utm_params)) {
			$query_params = array_merge($query_params, $utm_params);
		}

		// Add query parameters to URL
		if (!empty($query_params)) {
			$url .= '?' . implode('&', $query_params);
		}

		$this->debug_log('WhatsApp URL generated', array(
			'final_url' => $url,
			'url_length' => strlen($url)
		));

		return $url;
	}

	// Show data cleanup notification
	public function show_data_cleaned_notice() {
		if (get_option('watso_data_cleaned')) {
			?>
			<div class="notice notice-success is-dismissible">
				<p>
					<strong><?php esc_html_e('Watso Chat:', 'watso-basic-chat'); ?></strong>
					<?php esc_html_e('Since database cleanup is active, all plugin data has been deleted. You can make your new settings.', 'watso-basic-chat'); ?>
				</p>
			</div>
			<?php
			// Show this notification only once
			delete_option('watso_data_cleaned');

			$this->debug_log('Data cleaned notice displayed');
		}
	}

}

// Initialize the plugin
WatsoWhatsAppChat::get_instance();
